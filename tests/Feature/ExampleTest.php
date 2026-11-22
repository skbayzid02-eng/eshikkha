<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $this->seed(DatabaseSeeder::class);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('ই-শিক্ষা');
    }

    public function test_super_admin_dashboard_loads(): void
    {
        $this->seed(DatabaseSeeder::class);

        $response = $this->get(route('super-admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('বাংলাদেশ শিক্ষা প্ল্যাটফর্ম ড্যাশবোর্ড');
    }

    public function test_institution_dashboard_loads(): void
    {
        $this->seed(DatabaseSeeder::class);

        $response = $this->get(route('institution.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('ক্যাম্পাস অ্যাডমিন ড্যাশবোর্ড');
    }
}
