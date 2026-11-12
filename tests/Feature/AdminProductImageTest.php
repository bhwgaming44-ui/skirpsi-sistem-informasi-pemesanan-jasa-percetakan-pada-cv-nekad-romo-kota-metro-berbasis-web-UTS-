<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\FinishingSeeder;
use Database\Seeders\ProductSeeder;
use Database\Seeders\SettingSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminProductImageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var array<int, string>
     */
    private array $createdFiles = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->seed([
            SettingSeeder::class,
            UserSeeder::class,
            CategorySeeder::class,
            FinishingSeeder::class,
            ProductSeeder::class,
        ]);
    }

    protected function tearDown(): void
    {
        foreach ($this->createdFiles as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }

        parent::tearDown();
    }

    public function test_admin_can_create_a_product_with_primary_and_gallery_images(): void
    {
        $admin = User::query()->where('username', 'admin')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.products.store'), $this->productPayload([
                'gambar' => UploadedFile::fake()->image('gambar-utama.jpg', 900, 700),
                'galeri' => [
                    UploadedFile::fake()->image('galeri-satu.jpg', 900, 700),
                    UploadedFile::fake()->image('galeri-dua.jpg', 900, 700),
                ],
            ]))
            ->assertRedirect();

        $product = Product::query()->where('nama_produk', 'Produk Uji Gambar')->firstOrFail();

        $this->assertNotNull($product->gambar);
        $this->assertCount(2, $product->galeri);

        $primaryPath = public_path('images/products/'.$product->gambar);
        $this->trackFile($primaryPath);
        $this->assertFileExists($primaryPath);

        foreach ($product->galeri as $image) {
            $galleryPath = public_path('images/products/galeri/'.$image->file);
            $this->trackFile($galleryPath);
            $this->assertFileExists($galleryPath);
        }

        $this->get(route('catalog.show', $product))
            ->assertOk()
            ->assertSee($product->galeri->first()->url());
    }

    public function test_admin_can_replace_the_primary_image_and_manage_the_gallery(): void
    {
        $admin = User::query()->where('username', 'admin')->firstOrFail();
        [$product, $oldGallery, $oldPrimaryFile, $oldGalleryFile] = $this->productWithMedia();

        $this->actingAs($admin)
            ->put(route('admin.products.update', $product), $this->productPayload([
                'gambar' => UploadedFile::fake()->image('gambar-baru.jpg', 800, 600),
                'galeri' => [UploadedFile::fake()->image('galeri-baru.jpg', 800, 600)],
                'hapus_galeri' => [$oldGallery->id_gambar],
            ]))
            ->assertRedirect(route('admin.products.edit', $product));

        $product->refresh();

        $this->assertNotSame($oldPrimaryFile, $product->gambar);
        $this->assertFileDoesNotExist(public_path('images/products/'.$oldPrimaryFile));
        $this->assertFileExists(public_path('images/products/'.$product->gambar));
        $this->trackFile(public_path('images/products/'.$product->gambar));

        $this->assertDatabaseMissing('gambar_produk', ['id_gambar' => $oldGallery->id_gambar]);
        $this->assertFileDoesNotExist(public_path('images/products/galeri/'.$oldGalleryFile));

        $newGallery = $product->galeri()->firstOrFail();
        $this->assertFileExists(public_path('images/products/galeri/'.$newGallery->file));
        $this->trackFile(public_path('images/products/galeri/'.$newGallery->file));
    }

    public function test_deleting_a_product_removes_its_primary_and_gallery_files(): void
    {
        $admin = User::query()->where('username', 'admin')->firstOrFail();
        [$product, $gallery, $primaryFile, $galleryFile] = $this->productWithMedia();

        $this->actingAs($admin)
            ->delete(route('admin.products.destroy', $product))
            ->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseMissing('produk', ['id_produk' => $product->id_produk]);
        $this->assertDatabaseMissing('gambar_produk', ['id_gambar' => $gallery->id_gambar]);
        $this->assertFileDoesNotExist(public_path('images/products/'.$primaryFile));
        $this->assertFileDoesNotExist(public_path('images/products/galeri/'.$galleryFile));
    }

    public function test_documentation_page_is_only_available_for_admins(): void
    {
        $admin = User::query()->where('username', 'admin')->firstOrFail();
        $customer = User::query()->where('username', 'raihan')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.documentation'))
            ->assertOk()
            ->assertSee('Use Case')
            ->assertSee('Flowchart Tambah Produk');

        $this->actingAs($customer)
            ->get(route('admin.documentation'))
            ->assertForbidden();

        auth()->logout();

        $this->get(route('admin.documentation'))
            ->assertRedirect(route('login'));
    }

    /**
     * @return array{0: Product, 1: ProductImage, 2: string, 3: string}
     */
    private function productWithMedia(): array
    {
        $category = Category::query()->firstOrFail();
        $primaryFile = 'uji-utama-'.Str::lower(Str::random(8)).'.jpg';
        $galleryFile = 'uji-galeri-'.Str::lower(Str::random(8)).'.jpg';

        File::ensureDirectoryExists(public_path('images/products/galeri'));
        file_put_contents(public_path('images/products/'.$primaryFile), 'primary');
        file_put_contents(public_path('images/products/galeri/'.$galleryFile), 'gallery');

        $this->trackFile(public_path('images/products/'.$primaryFile));
        $this->trackFile(public_path('images/products/galeri/'.$galleryFile));

        $product = Product::query()->create([
            'id_kategori' => $category->id_kategori,
            'nama_produk' => 'Produk Uji Gambar',
            'satuan' => 'meter',
            'deskripsi' => 'Produk untuk pengujian gambar.',
            'gambar' => $primaryFile,
            'min_order' => 1,
            'estimasi_hari' => 2,
            'is_featured' => false,
            'is_active' => true,
        ]);

        $gallery = $product->galeri()->create([
            'file' => $galleryFile,
            'urutan' => 1,
        ]);

        return [$product, $gallery, $primaryFile, $galleryFile];
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function productPayload(array $overrides = []): array
    {
        $category = Category::query()->firstOrFail();

        return array_merge([
            'id_kategori' => $category->id_kategori,
            'nama_produk' => 'Produk Uji Gambar',
            'satuan' => 'meter',
            'deskripsi' => 'Produk untuk pengujian unggah gambar.',
            'min_order' => 1,
            'estimasi_hari' => 2,
            'is_active' => '1',
            'bahan' => [
                ['nama_bahan' => 'Flexi Uji 280gr', 'harga_satuan' => 25000],
            ],
        ], $overrides);
    }

    private function trackFile(string $path): void
    {
        $this->createdFiles[] = $path;
    }
}
