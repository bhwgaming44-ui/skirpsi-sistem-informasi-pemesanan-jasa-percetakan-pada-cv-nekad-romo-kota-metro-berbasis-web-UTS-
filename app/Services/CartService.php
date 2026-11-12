<?php

namespace App\Services;

use App\Models\CustomerCart;
use App\Models\Material;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Str;

class CartService
{
    private const SESSION_KEY = 'cart';

    /**
     * @var array<string, array<string, mixed>>|null
     */
    private ?array $cachedItems = null;

    private bool $loaded = false;

    public function __construct(private readonly PricingService $pricing) {}

    /**
     * @param  array{product_id: int, material_id: int, panjang?: float, lebar?: float, jumlah: int, finishing_ids?: array<int, int>, tipe_desain: string, catatan_desain?: ?string, file_desain?: ?string, nama_file_desain?: ?string}  $data
     */
    public function add(array $data): string
    {
        $items = $this->raw();
        $id = (string) Str::uuid();

        $items[$id] = [
            'product_id' => (int) $data['product_id'],
            'material_id' => (int) $data['material_id'],
            'panjang' => (float) ($data['panjang'] ?? 1),
            'lebar' => (float) ($data['lebar'] ?? 1),
            'jumlah' => (int) $data['jumlah'],
            'finishing_ids' => array_values(array_map('intval', $data['finishing_ids'] ?? [])),
            'tipe_desain' => $data['tipe_desain'],
            'catatan_desain' => $data['catatan_desain'] ?? null,
            'file_desain' => $data['file_desain'] ?? null,
            'nama_file_desain' => $data['nama_file_desain'] ?? null,
            'ditambahkan_pada' => now()->toDateTimeString(),
        ];

        $this->persist($items);

        return $id;
    }

    public function remove(string $id): void
    {
        $items = $this->raw();
        unset($items[$id]);
        $this->persist($items);
    }

    public function clear(): void
    {
        $this->refreshCache([]);

        $user = $this->user();

        if ($user !== null) {
            CustomerCart::query()->where('id_user', $user->id_user)->delete();
        }

        session()->forget(self::SESSION_KEY);
    }

    public function count(): int
    {
        return count($this->raw());
    }

    public function isEmpty(): bool
    {
        return $this->raw() === [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function items(): array
    {
        $items = [];

        foreach ($this->raw() as $id => $item) {
            $product = Product::query()->with(['bahan', 'finishing', 'kategori'])->find($item['product_id']);
            $material = Material::query()->find($item['material_id']);

            if (! $product || ! $material || ! $product->is_active || ! $material->is_active) {
                continue;
            }

            $quote = $this->pricing->quote($product, $material, $item);

            $items[$id] = [
                'id' => $id,
                'product' => $product,
                'material' => $material,
                'quote' => $quote,
                'tipe_desain' => $item['tipe_desain'],
                'catatan_desain' => $item['catatan_desain'],
                'file_desain' => $item['file_desain'],
                'nama_file_desain' => $item['nama_file_desain'],
            ];
        }

        return $items;
    }

    public function subtotal(): int
    {
        return (int) collect($this->items())->sum(fn (array $item) => $item['quote']['subtotal']);
    }

    /**
     * Menggabungkan keranjang tamu (session) ke keranjang akun yang tersimpan di database.
     */
    public function mergeGuestCartIntoUser(User $user): void
    {
        $guestItems = session()->get(self::SESSION_KEY, []);
        $guestItems = is_array($guestItems) ? $guestItems : [];

        $stored = CustomerCart::query()->where('id_user', $user->id_user)->first();
        $accountItems = $stored !== null && is_array($stored->items) ? $stored->items : [];

        if ($guestItems === []) {
            $this->refreshCache($accountItems);

            return;
        }

        $merged = $this->mergeItems($accountItems, $guestItems);

        CustomerCart::query()->updateOrCreate(
            ['id_user' => $user->id_user],
            ['items' => $merged],
        );

        session()->forget(self::SESSION_KEY);

        $this->refreshCache($merged);
    }

    /**
     * Menyimpan draft checkout agar dapat dilanjutkan setelah keluar dan masuk kembali.
     *
     * @param  array{metode_pengambilan?: ?string, alamat_pengiriman?: ?string, catatan?: ?string}  $draft
     */
    public function saveCheckoutDraft(array $draft): void
    {
        $user = $this->user();

        if ($user === null) {
            return;
        }

        $cart = CustomerCart::query()->firstOrCreate(
            ['id_user' => $user->id_user],
            ['items' => []],
        );

        $cart->checkout_draft = $draft;
        $cart->save();
    }

    /**
     * @return array{metode_pengambilan?: ?string, alamat_pengiriman?: ?string, catatan?: ?string}
     */
    public function checkoutDraft(): array
    {
        $user = $this->user();

        if ($user === null) {
            return [];
        }

        $draft = CustomerCart::query()
            ->where('id_user', $user->id_user)
            ->value('checkout_draft');

        return is_array($draft) ? $draft : [];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function raw(): array
    {
        if ($this->loaded) {
            return $this->cachedItems ?? [];
        }

        $user = $this->user();
        $items = [];

        if ($user !== null) {
            $cart = CustomerCart::query()->where('id_user', $user->id_user)->first();
            $items = $cart !== null && is_array($cart->items) ? $cart->items : [];

            $guestItems = session()->get(self::SESSION_KEY, []);

            if (is_array($guestItems) && $guestItems !== []) {
                $items = $this->mergeItems($items, $guestItems);

                CustomerCart::query()->updateOrCreate(
                    ['id_user' => $user->id_user],
                    ['items' => $items],
                );

                session()->forget(self::SESSION_KEY);
            }
        } else {
            $items = session()->get(self::SESSION_KEY, []);
        }

        $this->refreshCache(is_array($items) ? $items : []);

        return $this->cachedItems ?? [];
    }

    /**
     * @param  array<string, array<string, mixed>>  $items
     */
    private function persist(array $items): void
    {
        $this->refreshCache($items);

        $user = $this->user();

        if ($user !== null) {
            CustomerCart::query()->updateOrCreate(
                ['id_user' => $user->id_user],
                ['items' => $items],
            );

            return;
        }

        session()->put(self::SESSION_KEY, $items);
    }

    /**
     * @param  array<string, array<string, mixed>>  $accountItems
     * @param  array<string, array<string, mixed>>  $guestItems
     * @return array<string, array<string, mixed>>
     */
    private function mergeItems(array $accountItems, array $guestItems): array
    {
        $merged = $accountItems;
        $signatures = [];

        foreach ($merged as $id => $item) {
            if (is_array($item)) {
                $signatures[$this->itemSignature($item)] = $id;
            }
        }

        foreach ($guestItems as $id => $item) {
            if (! is_array($item)) {
                continue;
            }

            $signature = $this->itemSignature($item);

            if (isset($signatures[$signature])) {
                $existingId = $signatures[$signature];
                $merged[$existingId]['jumlah'] = (int) ($merged[$existingId]['jumlah'] ?? 0)
                    + (int) ($item['jumlah'] ?? 0);

                continue;
            }

            $merged[$id] = $item;
            $signatures[$signature] = $id;
        }

        return $merged;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function itemSignature(array $item): string
    {
        $finishingIds = array_map('intval', $item['finishing_ids'] ?? []);
        sort($finishingIds);

        return (string) json_encode([
            'product_id' => (int) ($item['product_id'] ?? 0),
            'material_id' => (int) ($item['material_id'] ?? 0),
            'panjang' => (float) ($item['panjang'] ?? 1),
            'lebar' => (float) ($item['lebar'] ?? 1),
            'finishing_ids' => $finishingIds,
            'tipe_desain' => (string) ($item['tipe_desain'] ?? ''),
            'catatan_desain' => (string) ($item['catatan_desain'] ?? ''),
            'file_desain' => (string) ($item['file_desain'] ?? ''),
        ]);
    }

    /**
     * @param  array<string, array<string, mixed>>  $items
     */
    private function refreshCache(array $items): void
    {
        $this->cachedItems = $items;
        $this->loaded = true;
    }

    private function user(): ?User
    {
        $user = auth()->user();

        return $user instanceof User ? $user : null;
    }
}
