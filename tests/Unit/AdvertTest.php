<?php

namespace Tests\Unit;

// use PHPUnit\Framework\TestCase;

use App\Models\Advert;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdvertTest extends TestCase
{
    use RefreshDatabase, WithFaker;
    /**
     * A basic unit test example.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Your additional setup code goes here
    }


    public function test_example(): void
    {
        $this->assertTrue(true);
    }

    public function testStoreMethod(){
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        // Create a fake file for testing
        $media = base64_encode(file_get_contents(__DIR__.'/test_image.jpg'));
        $media = 'data:image/jpeg;base64,'.$media;

        // Set up the request payload
        $stuffs =[
            'name' => 'Test Ad',
            'mediaType' => 'picture',
            'duration' => 10,
            'width' => 300,
            'height' => 250,
            'page' => 'home',
            'country' => 'us',
            'platform' => 'desktop',
            'section' => 'header',
            'redirect_url' => 'http://example.com',
        ];

        $payload = [
            'media' => $media,
        ] + $stuffs;

        // Make a POST request to the store method
        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ])
        ->json('POST', '/api/v1/createAds', $payload);

        // Assert that the response has the correct status code
        $response->assertStatus(201);

        // Assert that the response contains the expected data
        $responseData = $response->json();
        $response->assertJson([
            'message' => 'success',
            'data' => [
                'user_id' => $user->id,
                'media_url' => $responseData['data']['media_url'],
            ] + $stuffs,
        ]);

        // Assert that the advert was created in the database
        $this->assertDatabaseHas('adverts', [
            'id' => $responseData['data']['id'],
            'user_id' => $user->id,
            'media_url' => $responseData['data']['media_url'],
        ] + $stuffs);

    }


     public function testUpdateMethod(){
        // Create a user and generate a token
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        // Create a test ad
        $ad = Advert::factory()->create(['user_id' => $user->id]);

        // Set up the updated ad data
        $updatedData = [
            'name' => 'Updated Ad',
            'duration' => 15,
            'width' => 400,
            'height' => 300,
            'section' => 'footer',
            'redirect_url' => 'http://example.com/updated',
        ];

        // Make a PUT request to the update method
        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ])->json('POST', '/api/v1/updateAds/' . $ad->id, $updatedData);

        // Assert that the response has the correct status code
        $response->assertStatus(200);

        // Assert that the response contains the expected data
        $responseData = $response->json();
        $response->assertJson([
            'message' => 'success',
            'data' => $updatedData,
        ]);

        // Assert that the database has been updated with the new data
        $this->assertDatabaseHas('adverts', [
            'id' => $ad->id,
            'name' => 'Updated Ad',
            'duration' => 15,
            'width' => 400,
            'height' => 300,
            'section' => 'footer',
            'redirect_url' => 'http://example.com/updated',
        ]);
    }


    public function testDeleteAdvert(){
        // Create a user and generate a token
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        // Create a test ad
        $ad = Advert::factory()->create(['user_id' => $user->id]);

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ])->json('DELETE', '/api/v1/deleteAds/'.$ad->id);

        // Assert that the response has the correct status code
        $response->assertStatus(200);
    }

}
