<?php

namespace Tests\Feature;

use App\Models\Position;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PositionStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_accepts_status_alias_and_exposes_status_accessor(): void
    {
        $response = $this->withoutMiddleware()->postJson('/api/positions', [
            'title' => 'QA Engineer',
            'location' => 'Remote',
            'status' => 'active',
        ]);

        $response->assertCreated()
            ->assertJsonPath('is_active', true)
            ->assertJsonPath('status', 'active');

        $this->assertDatabaseHas('positions', [
            'title' => 'QA Engineer',
            'location' => 'Remote',
            'is_active' => true,
        ]);
    }

    public function test_update_accepts_status_alias(): void
    {
        $position = Position::factory()->create([
            'is_active' => true,
        ]);

        $response = $this->withoutMiddleware()->patchJson('/api/positions/' . $position->id, [
            'status' => 'inactive',
        ]);

        $response->assertOk()
            ->assertJsonPath('is_active', false)
            ->assertJsonPath('status', 'inactive');

        $this->assertDatabaseHas('positions', [
            'id' => $position->id,
            'is_active' => false,
        ]);
    }
}
