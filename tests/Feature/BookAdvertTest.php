<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BookAdvertTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Use a private in-memory database rather than the MySQL server the other tests expect.
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        $this->artisan('migrate');

        Sanctum::actingAs(User::factory()->create(['password' => 'password']));
    }

    public function test_booking_from_a_form_post_with_a_string_duration(): void
    {
        // Form posts send the duration as a string; Carbon 3's addDays() only accepts int|float.
        $this->post('/api/v1/bookAdvert', [
            'start_date' => '2026-11-01',
            'currency_code' => 'NGN',
            'duration' => '10',
            'price' => '5000',
            'type' => 'banner',
        ], ['Accept' => 'application/json'])->assertOk();

        $this->assertDatabaseHas('bookeds', ['duration' => 10, 'end_date' => '2026-11-11 00:00:00']);
    }
}
