<?php

use App\Models\User;

test('completing onboarding saves the profile and redirects to home', function () {
    $user = User::factory()->pendingOnboarding()->create();
    $this->actingAs($user);

    $response = $this->patch(route('onboarding.update'), [
        'name' => 'Tolulope Olaleru',
        'whatsapp_number' => '+2348000000000',
        'goals' => ['Know the Bible better', 'Get closer to God'],
        'birthday_day' => 20,
        'birthday_month' => 1,
    ]);

    $response->assertRedirect(route('home'));

    expect($user->refresh())
        ->name->toBe('Tolulope Olaleru')
        ->whatsapp_number->toBe('+2348000000000')
        ->goals->toBe(['Know the Bible better', 'Get closer to God'])
        ->birthday_day->toBe(20)
        ->birthday_month->toBe(1)
        ->onboarding_completed_at->not->toBeNull();
});

test('birthday is optional when completing onboarding', function () {
    $user = User::factory()->pendingOnboarding()->create();
    $this->actingAs($user);

    $response = $this->patch(route('onboarding.update'), [
        'name' => 'Tolulope Olaleru',
        'whatsapp_number' => '+2348000000000',
        'goals' => ['Know the Bible better'],
    ]);

    $response->assertRedirect(route('home'));

    expect($user->refresh())
        ->birthday_day->toBeNull()
        ->birthday_month->toBeNull()
        ->onboarding_completed_at->not->toBeNull();
});

test('at least one goal is required', function () {
    $user = User::factory()->pendingOnboarding()->create();
    $this->actingAs($user);

    $response = $this->patch(route('onboarding.update'), [
        'name' => 'Tolulope Olaleru',
        'whatsapp_number' => '+2348000000000',
        'goals' => [],
    ]);

    $response->assertSessionHasErrors('goals');
    expect($user->refresh()->onboarding_completed_at)->toBeNull();
});
