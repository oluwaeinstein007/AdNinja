<?php

namespace Tests\Feature;

use App\Models\Advert;
use App\Models\User;
use Cloudinary\Api\ApiResponse;
use Cloudinary\Api\Upload\UploadApi;
use Cloudinary\Cloudinary;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * Covers the Cloudinary upload call sites in AdvertController against a mocked SDK,
 * so no request ever reaches the real Cloudinary API.
 */
class AdvertMediaUploadTest extends TestCase
{
    private const MEDIA = 'data:image/png;base64,iVBORw0KGgo=';

    private const SECURE_URL = 'https://res.cloudinary.com/demo/image/upload/v1/advert.png';

    private MockInterface $uploadApi;

    protected function setUp(): void
    {
        parent::setUp();

        // Use a private in-memory database rather than the MySQL server the other tests expect.
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        $this->artisan('migrate');

        $this->uploadApi = Mockery::mock(UploadApi::class);
        $cloudinary = Mockery::mock(Cloudinary::class);
        $cloudinary->shouldReceive('uploadApi')->andReturn($this->uploadApi);
        $this->app->instance(Cloudinary::class, $cloudinary);

        // A plain password: the factory's fixed cost-10 hash fails the "hashed" cast under BCRYPT_ROUNDS=4.
        Sanctum::actingAs(User::factory()->create(['user_role_id' => 1, 'password' => 'password']));
    }

    public function test_store_uploads_a_picture_and_saves_the_secure_url(): void
    {
        $this->expectUpload([
            'transformation' => [
                ['width' => 300, 'height' => 250, 'crop' => 'fit'],
            ],
        ]);

        $this->postJson('/api/v1/createAds', $this->payload(['mediaType' => 'Picture']))
            ->assertCreated()
            ->assertJsonPath('data.media_url', self::SECURE_URL);

        $this->assertDatabaseHas('adverts', ['media_url' => self::SECURE_URL, 'mediaType' => 'picture']);
    }

    public function test_store_uploads_a_gif_and_saves_the_secure_url(): void
    {
        $this->expectUpload([
            'resource_type' => 'auto',
            'format' => 'gif',
            'transformation' => ['width' => 300, 'height' => 250, 'crop' => 'scale'],
        ]);

        $this->postJson('/api/v1/createAds', $this->payload(['mediaType' => 'gif']))
            ->assertCreated()
            ->assertJsonPath('data.media_url', self::SECURE_URL);

        $this->assertDatabaseHas('adverts', ['media_url' => self::SECURE_URL, 'mediaType' => 'gif']);
    }

    public function test_update_uploads_new_picture_media(): void
    {
        $advert = Advert::factory()->create(['user_id' => auth()->id()]);

        $this->expectUpload([
            'transformation' => [
                ['width' => 300, 'height' => 250, 'crop' => 'fit'],
            ],
        ]);

        $this->postJson('/api/v1/updateAds/'.$advert->id, $this->payload(['mediaType' => 'picture']))
            ->assertOk();
    }

    public function test_update_uploads_new_gif_media(): void
    {
        $advert = Advert::factory()->create(['user_id' => auth()->id()]);

        $this->expectUpload(['resource_type' => 'auto', 'format' => 'gif']);

        $this->postJson('/api/v1/updateAds/'.$advert->id, $this->payload(['mediaType' => 'gif']))
            ->assertOk();
    }

    public function test_the_sdk_is_built_from_the_cloudinary_url_config(): void
    {
        $this->app->forgetInstance(Cloudinary::class);
        config(['services.cloudinary.url' => 'cloudinary://key:secret@demo-cloud']);

        $cloudinary = $this->app->make(Cloudinary::class);

        $this->assertSame('demo-cloud', $cloudinary->configuration->cloud->cloudName);
        $this->assertSame($cloudinary, $this->app->make(Cloudinary::class));
    }

    private function expectUpload(array $options): void
    {
        $this->uploadApi->shouldReceive('upload')
            ->once()
            ->with(self::MEDIA, $options)
            ->andReturn(new ApiResponse(['secure_url' => self::SECURE_URL], []));
    }

    private function payload(array $overrides): array
    {
        return array_merge([
            'name' => 'Test Ad',
            'page' => 'home',
            'media' => self::MEDIA,
            'duration' => 10,
            'width' => 300,
            'height' => 250,
            'country' => 'ng',
            'platform' => 'web',
            'section' => 'header',
            'redirect_url' => 'https://example.com',
        ], $overrides);
    }
}
