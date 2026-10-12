<?php

use App\Models\User;

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('profile.edit'));

    $response->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    $user->refresh();

    expect($user->name)->toBe('Test User');
    expect($user->email)->toBe('test@example.com');
    expect($user->email_verified_at)->toBeNull();
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => 'Test User',
            'email' => $user->email,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->delete(route('profile.destroy'), [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('root'));

    $this->assertGuest();
    expect($user->fresh())->toBeNull();
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from(route('profile.edit'))
        ->delete(route('profile.destroy'), [
            'password' => 'wrong-password',
        ]);

    $response
        ->assertSessionHasErrors('password')
        ->assertRedirect(route('profile.edit'));

    expect($user->fresh())->not->toBeNull();
});

test('the details collected at onboarding can be updated from settings', function () {
    $user = User::factory()->create([
        'whatsapp_number' => '+2348000000000',
        'goals' => ['Know the Bible better'],
        'birthday_day' => 20,
        'birthday_month' => 1,
    ]);

    $this->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'whatsapp_number' => '+2348111111111',
            'goals' => ['Get closer to God', 'Build a daily habit'],
            'birthday_day' => 5,
            'birthday_month' => 11,
        ])
        ->assertSessionHasNoErrors();

    expect($user->refresh())
        ->whatsapp_number->toBe('+2348111111111')
        ->goals->toBe(['Get closer to God', 'Build a daily habit'])
        ->birthday_day->toBe(5)
        ->birthday_month->toBe(11);
});

test('the birthday can be cleared', function () {
    $user = User::factory()->create(['birthday_day' => 20, 'birthday_month' => 1]);

    $this->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'birthday_day' => '',
            'birthday_month' => '',
        ])
        ->assertSessionHasNoErrors();

    expect($user->refresh())->birthday_day->toBeNull()->birthday_month->toBeNull();
});

test('onboarding details are left alone when the form does not send them', function () {
    $user = User::factory()->create([
        'whatsapp_number' => '+2348000000000',
        'goals' => ['Know the Bible better'],
        'birthday_day' => 20,
        'birthday_month' => 1,
    ]);

    $this->actingAs($user)
        ->patch(route('profile.update'), ['name' => 'New Name', 'email' => $user->email])
        ->assertSessionHasNoErrors();

    expect($user->refresh())
        ->whatsapp_number->toBe('+2348000000000')
        ->goals->toBe(['Know the Bible better'])
        ->birthday_day->toBe(20);
});

test('an existing WhatsApp number cannot be blanked out', function () {
    $user = User::factory()->create(['whatsapp_number' => '+2348000000000']);

    $this->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'whatsapp_number' => '',
        ])
        ->assertSessionHasErrors('whatsapp_number');

    expect($user->refresh()->whatsapp_number)->toBe('+2348000000000');
});

test('profile details are validated', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'goals' => [],
            'birthday_day' => 32,
            'birthday_month' => 13,
        ])
        ->assertSessionHasErrors(['birthday_day', 'birthday_month']);
});
