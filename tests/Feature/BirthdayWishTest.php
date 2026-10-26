<?php

use App\Models\BirthdayWish;
use App\Models\User;
use App\Notifications\BirthdayWishReceived;
use Illuminate\Support\Facades\Notification;

function birthdayUserToday(): User
{
    return User::factory()->create(['birthday_day' => today()->day, 'birthday_month' => today()->month]);
}

test('a member can wish a birthday person and they are notified', function () {
    Notification::fake();
    $sender = User::factory()->create();
    $celebrant = birthdayUserToday();

    $this->actingAs($sender)->post(route('birthday-wishes.store', $celebrant))->assertRedirect();

    Notification::assertSentTo($celebrant, BirthdayWishReceived::class);
    $this->assertDatabaseHas('birthday_wishes', ['sender_id' => $sender->id, 'recipient_id' => $celebrant->id]);
});

test('wishing the same person twice only notifies once', function () {
    Notification::fake();
    $sender = User::factory()->create();
    $celebrant = birthdayUserToday();

    $this->actingAs($sender)->post(route('birthday-wishes.store', $celebrant));
    $this->actingAs($sender)->post(route('birthday-wishes.store', $celebrant));

    Notification::assertSentToTimes($celebrant, BirthdayWishReceived::class, 1);
});

test('wishes cannot be sent to yourself, on a non-birthday, or as a guest', function () {
    Notification::fake();
    $celebrant = birthdayUserToday();
    $other = User::factory()->create(['birthday_day' => today()->addDay()->day, 'birthday_month' => today()->addDay()->month]);

    $this->actingAs($celebrant)->post(route('birthday-wishes.store', $celebrant))->assertForbidden();
    $this->actingAs(User::factory()->create())->post(route('birthday-wishes.store', $other))->assertNotFound();
    auth()->logout();
    $this->post(route('birthday-wishes.store', $celebrant))->assertRedirect(route('login'));

    Notification::assertNothingSent();
});

test('home flags birthday people the member has already wished', function () {
    $sender = User::factory()->create();
    $celebrant = birthdayUserToday();
    BirthdayWish::factory()->create(['sender_id' => $sender->id, 'recipient_id' => $celebrant->id]);

    $this->actingAs($sender)->get(route('home'))
        ->assertInertia(fn ($page) => $page->where('birthdays.0.wished', true));
});
