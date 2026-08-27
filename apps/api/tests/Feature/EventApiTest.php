<?php

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('filters sorts and paginates public events', function (): void {
    $admin = User::factory()->admin()->create();
    Event::factory()->for($admin, 'creator')->published()->create([
        'title' => 'Laravel Conference',
        'starts_at' => '2030-01-02 10:00:00',
        'ends_at' => '2030-01-02 12:00:00',
    ]);
    Event::factory()->for($admin, 'creator')->create(['title' => 'Private Laravel Draft']);
    Event::factory()->for($admin, 'creator')->published()->create(['title' => 'Other Conference']);

    $this->getJson('/api/events?keyword=laravel&status=PUBLISHED&sort=-starts_at&per_page=1')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'Laravel Conference')
        ->assertJsonPath('meta.total', 1)
        ->assertHeader('X-Request-ID');
});

it('returns 422 for a sort expression outside the allow list', function (): void {
    $this->getJson('/api/events?sort=reserved_count;drop')
        ->assertUnprocessable()
        ->assertJsonPath('code', 'VALIDATION_ERROR');
});

it('hides unpublished events from guests but exposes them to an authenticated admin', function (): void {
    $admin = User::factory()->admin()->create();
    $draft = Event::factory()->for($admin, 'creator')->create(['title' => 'Draft Event']);

    $this->getJson('/api/events')->assertOk()->assertJsonCount(0, 'data');
    $this->getJson("/api/events/{$draft->id}")
        ->assertNotFound()->assertJsonPath('code', 'EVENT_NOT_FOUND');

    $token = $admin->createToken('admin')->plainTextToken;
    $this->withToken($token)->getJson('/api/events?status=DRAFT')
        ->assertOk()->assertJsonPath('data.0.id', $draft->id);
    $this->withToken($token)->getJson("/api/events/{$draft->id}")
        ->assertOk()->assertJsonPath('data.id', $draft->id);
});

it('allows an admin to create patch and delete an event', function (): void {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    $createResponse = $this->postJson('/api/events', [
        'title' => 'New Event',
        'description' => 'Description',
        'venue' => 'Tokyo',
        'starts_at' => '2030-05-01T10:00:00+09:00',
        'ends_at' => '2030-05-01T12:00:00+09:00',
        'capacity' => 50,
    ])->assertCreated()->assertJsonPath('data.status', 'DRAFT');

    $eventId = $createResponse->json('data.id');
    $this->patchJson("/api/events/{$eventId}", ['status' => EventStatus::Published->value])
        ->assertOk()
        ->assertJsonPath('data.status', 'PUBLISHED');
    $this->assertDatabaseHas('events', ['id' => $eventId, 'status' => EventStatus::Published->value]);

    $this->deleteJson("/api/events/{$eventId}")->assertNoContent();
    $this->assertDatabaseMissing('events', ['id' => $eventId]);
});

it('returns 403 when a regular user writes an event', function (): void {
    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/events', [
        'title' => 'Forbidden Event',
        'description' => 'Description',
        'venue' => 'Tokyo',
        'starts_at' => '2030-05-01T10:00:00+09:00',
        'ends_at' => '2030-05-01T12:00:00+09:00',
        'capacity' => 50,
    ])->assertForbidden()->assertJsonPath('code', 'FORBIDDEN');
});

it('returns 422 when capacity is reduced below the reserved count', function (): void {
    $admin = User::factory()->admin()->create();
    $event = Event::factory()->for($admin, 'creator')->published()->create([
        'capacity' => 10,
        'reserved_count' => 3,
    ]);
    Sanctum::actingAs($admin);

    $this->patchJson("/api/events/{$event->id}", ['capacity' => 2])
        ->assertUnprocessable()
        ->assertJsonPath('code', 'VALIDATION_ERROR');
    expect($event->fresh()->capacity)->toBe(10);
});

it('returns the event not found error shape', function (): void {
    $this->getJson('/api/events/00000000-0000-0000-0000-000000000000')
        ->assertNotFound()
        ->assertJsonPath('code', 'EVENT_NOT_FOUND');
});
