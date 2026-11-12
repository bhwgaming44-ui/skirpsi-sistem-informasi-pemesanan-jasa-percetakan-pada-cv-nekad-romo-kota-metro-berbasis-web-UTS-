<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Services\SavedProductService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class CatalogController extends Controller
{
    private const PER_PAGE = 9;

    public function __construct(private readonly SavedProductService $savedProducts) {}

    public function index(Request $request): View
    {
        $filters = $this->filters($request);
        $products = $this->products($filters);
        $categories = $this->categories();

        return view('catalog.index', [
            'products' => $products,
            'categories' => $categories,
            'activeCategory' => $filters['activeCategory'],
            'categoryName' => $categories->firstWhere('id_kategori', $filters['activeCategory'])?->nama_kategori,
            'search' => $filters['search'],
            'sort' => $filters['sort'],
            'savedProductIds' => $this->savedProducts->ids(),
            'allProductIds' => $this->matchingIds($filters),
        ]);
    }

    public function filter(Request $request): JsonResponse
    {
        $filters = $this->filters($request);
        $products = $this->products($filters);

        return response()->json([
            'html' => view('catalog.partials.results', [
                'products' => $products,
                'search' => $filters['search'],
                'sort' => $filters['sort'],
                'activeCategory' => $filters['activeCategory'],
                'categoryName' => $filters['activeCategory'] > 0
                    ? Category::query()->whereKey($filters['activeCategory'])->value('nama_kategori')
                    : null,
                'savedProductIds' => $this->savedProducts->ids(),
            ])->render(),
            'total' => $products->total(),
            'from' => $products->firstItem(),
            'to' => $products->lastItem(),
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
            'search' => $filters['search'],
            'sort' => $filters['sort'],
            'category' => $filters['activeCategory'],
            'ids' => $this->matchingIds($filters),
            'has_filters' => $filters['search'] !== ''
                || $filters['sort'] !== 'terbaru'
                || $filters['activeCategory'] > 0,
            'url' => route('catalog.index', $this->queryParams($filters, $products), false),
        ]);
    }

    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);

        $product->load(['kategori', 'bahanAktif', 'finishing', 'galeri']);

        $related = Product::query()
            ->aktif()
            ->where('id_kategori', $product->id_kategori)
            ->whereKeyNot($product->getKey())
            ->with(['bahanAktif'])
            ->limit(3)
            ->get();

        return view('catalog.show', [
            'product' => $product,
            'related' => $related,
            'savedProductIds' => $this->savedProducts->ids(),
        ]);
    }

    /**
     * @return array{search: string, activeCategory: int, sort: string}
     */
    private function filters(Request $request): array
    {
        $sort = $request->string('sort', 'terbaru')->toString();

        if (! in_array($sort, ['terbaru', 'nama', 'termurah', 'termahal'], true)) {
            $sort = 'terbaru';
        }

        return [
            'search' => mb_substr(trim($request->string('q')->toString()), 0, 80),
            'activeCategory' => max($request->integer('kategori'), 0),
            'sort' => $sort,
        ];
    }

    /**
     * @param  array{search: string, activeCategory: int, sort: string}  $filters
     */
    private function products(array $filters): LengthAwarePaginator
    {
        $products = $this->productQuery($filters)
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        if ($filters['search'] !== '' && $products->total() === 0) {
            $products = $this->productQuery($filters, fuzzy: true)
                ->paginate(self::PER_PAGE, ['*'], 'page', 1)
                ->withQueryString();
        }

        return $products;
    }

    /**
     * @param  array{search: string, activeCategory: int, sort: string}  $filters
     * @return array<int, int>
     */
    private function matchingIds(array $filters): array
    {
        $ids = $this->productQuery($filters)
            ->pluck('id_produk')
            ->map(fn (int|string $id): int => (int) $id)
            ->all();

        if ($filters['search'] !== '' && $ids === []) {
            $ids = $this->productQuery($filters, fuzzy: true)
                ->pluck('id_produk')
                ->map(fn (int|string $id): int => (int) $id)
                ->all();
        }

        return $ids;
    }

    /**
     * @param  array{search: string, activeCategory: int, sort: string}  $filters
     */
    private function productQuery(array $filters, bool $fuzzy = false): Builder
    {
        $query = Product::query()
            ->aktif()
            ->with(['kategori', 'bahanAktif'])
            ->withCount('bahanAktif');

        if ($filters['search'] !== '') {
            $this->applySearch($query, $filters['search'], $fuzzy);
        }

        if ($filters['activeCategory'] > 0) {
            $query->where('id_kategori', $filters['activeCategory']);
        }

        match ($filters['sort']) {
            'nama' => $query->orderBy('nama_produk'),
            'termurah' => $query->withMin('bahanAktif as harga_min', 'harga_satuan')->orderBy('harga_min'),
            'termahal' => $query->withMin('bahanAktif as harga_min', 'harga_satuan')->orderByDesc('harga_min'),
            default => $query->orderByDesc('is_featured')->orderBy('id_produk'),
        };

        return $query;
    }

    private function applySearch(Builder $query, string $search, bool $fuzzy): void
    {
        $tokens = $this->searchTokens($search);

        if ($tokens === []) {
            return;
        }

        $query->where(function (Builder $builder) use ($tokens, $fuzzy): void {
            foreach ($tokens as $index => $token) {
                $pattern = $fuzzy ? $this->fuzzyPattern($token) : $this->likePattern($token);

                if ($fuzzy) {
                    $method = $index === 0 ? 'where' : 'orWhere';

                    $builder->{$method}('nama_produk', 'like', $pattern);

                    continue;
                }

                $builder->where(function (Builder $inner) use ($pattern): void {
                    $inner->where('nama_produk', 'like', $pattern)
                        ->orWhere('deskripsi', 'like', $pattern);
                });
            }
        });
    }

    /**
     * @return array<int, string>
     */
    private function searchTokens(string $search): array
    {
        $tokens = preg_split('/[^\p{L}\p{N}]+/u', trim($search)) ?: [];

        return array_values(array_unique(array_filter(
            $tokens,
            fn (string $token): bool => $token !== ''
        )));
    }

    private function likePattern(string $value): string
    {
        return '%'.addcslashes($value, '\\%_').'%';
    }

    private function fuzzyPattern(string $token): string
    {
        $characters = mb_str_split(addcslashes($token, '\\%_'));

        if (count($characters) <= 1) {
            return $this->likePattern($token);
        }

        return '%'.implode('%', $characters).'%';
    }

    /**
     * @return Collection<int, Category>
     */
    private function categories(): Collection
    {
        return Category::query()
            ->withCount(['produk' => fn ($query) => $query->where('is_active', true)])
            ->get();
    }

    /**
     * @param  array{search: string, activeCategory: int, sort: string}  $filters
     * @return array<string, int|string>
     */
    private function queryParams(array $filters, LengthAwarePaginator $products): array
    {
        return array_filter([
            'q' => $filters['search'] !== '' ? $filters['search'] : null,
            'kategori' => $filters['activeCategory'] > 0 ? $filters['activeCategory'] : null,
            'sort' => $filters['sort'] !== 'terbaru' ? $filters['sort'] : null,
            'page' => $products->currentPage() > 1 ? $products->currentPage() : null,
        ]);
    }
}
