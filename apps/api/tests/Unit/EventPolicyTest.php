<?php

use App\Models\Event;
use App\Models\User;
use App\Policies\EventPolicy;

it('allows only admins to manage events', function (): void {
    $policy = new EventPolicy;
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    $event = Event::factory()->for($admin, 'creator')->create();

    expect($policy->create($admin))->toBeTrue()
        ->and($policy->update($admin, $event))->toBeTrue()
        ->and($policy->delete($admin, $event))->toBeTrue()
        ->and($policy->create($user))->toBeFalse()
        ->and($policy->update($user, $event))->toBeFalse()
        ->and($policy->delete($user, $event))->toBeFalse();
});

it('allows only regular users to reserve events', function (): void {
    $policy = new EventPolicy;
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    $event = Event::factory()->for($admin, 'creator')->published()->create();

    expect($policy->reserve($user, $event))->toBeTrue()
        ->and($policy->reserve($admin, $event))->toBeFalse();
});
