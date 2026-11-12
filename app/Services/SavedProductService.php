<?php

namespace App\Services;

use App\Models\Product;
use App\Models\SavedProduct;
use App\Models\User;

class SavedProductService
{
    private const SESSION_KEY = 'saved_products';

    /**
     * @return array<int, int>
     */
    public function ids(): array
    {
        $user = $this->user();

        if ($user === null) {
            return $this->sessionIds();
        }

        return SavedProduct::query()
            ->where('id_user', $user->id_user)
            ->orderBy('created_at')
            ->pluck('id_produk')
            ->map(fn (int|string $id): int => (int) $id)
            ->all();
    }

    public function has(int $productId): bool
    {
        return in_array($productId, $this->ids(), true);
    }

    public function count(): int
    {
        $user = $this->user();

        if ($user === null) {
            return count($this->sessionIds());
        }

        return SavedProduct::query()
            ->where('id_user', $user->id_user)
            ->count();
    }

    public function toggle(int $productId): bool
    {
        $user = $this->user();

        if ($user !== null) {
            return $this->toggleForUser($user, $productId);
        }

        return $this->toggleForSession($productId);
    }

    public function mergeSessionIntoUser(User $user): void
    {
        $ids = $this->sessionIds();

        if ($ids === []) {
            return;
        }

        $validIds = Product::query()
            ->aktif()
            ->whereIn('id_produk', $ids)
            ->pluck('id_produk')
            ->map(fn (int|string $id): int => (int) $id)
            ->all();

        if ($validIds !== []) {
            $now = now();

            SavedProduct::query()->insertOrIgnore(
                array_map(fn (int $id): array => [
                    'id_user' => $user->id_user,
                    'id_produk' => $id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ], $validIds)
            );
        }

        session()->forget(self::SESSION_KEY);
    }

    private function toggleForUser(User $user, int $productId): bool
    {
        $saved = SavedProduct::query()
            ->where('id_user', $user->id_user)
            ->where('id_produk', $productId)
            ->first();

        if ($saved !== null) {
            $saved->delete();

            return false;
        }

        SavedProduct::query()->create([
            'id_user' => $user->id_user,
            'id_produk' => $productId,
        ]);

        return true;
    }

    private function toggleForSession(int $productId): bool
    {
        $ids = $this->sessionIds();
        $index = array_search($productId, $ids, true);

        if ($index !== false) {
            unset($ids[$index]);
            session()->put(self::SESSION_KEY, array_values($ids));

            return false;
        }

        $ids[] = $productId;
        session()->put(self::SESSION_KEY, $ids);

        return true;
    }

    /**
     * @return array<int, int>
     */
    private function sessionIds(): array
    {
        $ids = session()->get(self::SESSION_KEY, []);

        if (! is_array($ids)) {
            return [];
        }

        return array_values(array_unique(array_map('intval', $ids)));
    }

    private function user(): ?User
    {
        $user = auth()->user();

        return $user instanceof User ? $user : null;
    }
}
