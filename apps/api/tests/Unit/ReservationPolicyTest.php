<?php

use App\Models\Reservation;
use App\Models\User;
use App\Policies\ReservationPolicy;

it('allows a regular user to read and cancel only their own reservation', function (): void {
    $policy = new ReservationPolicy;
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $reservation = Reservation::factory()->for($owner)->create();

    expect($policy->view($owner, $reservation)->allowed())->toBeTrue()
        ->and($policy->delete($owner, $reservation)->allowed())->toBeTrue()
        ->and($policy->view($other, $reservation)->status())->toBe(404)
        ->and($policy->delete($other, $reservation)->status())->toBe(404);
});

it('does not allow admins to use personal reservation endpoints', function (): void {
    $policy = new ReservationPolicy;
    $admin = User::factory()->admin()->create();
    $reservation = Reservation::factory()->create();

    expect($policy->viewAny($admin))->toBeFalse()
        ->and($policy->create($admin))->toBeFalse()
        ->and($policy->view($admin, $reservation)->status())->toBe(404);
});
