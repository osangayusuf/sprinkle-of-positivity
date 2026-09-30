<?php

use App\Models\Comment;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('a user can upload a profile picture', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('profile.avatar.update'), [
            'avatar' => UploadedFile::fake()->image('me.jpg'),
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    $user->refresh();
    expect($user->avatar_path)->not->toBeNull();
    Storage::disk('public')->assertExists($user->avatar_path);
});

test('replacing a profile picture deletes the previous file', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $user->avatar_path = UploadedFile::fake()->image('old.jpg')->store('avatars', 'public');
    $user->save();
    $previousPath = $user->avatar_path;

    $this->actingAs($user)->post(route('profile.avatar.update'), [
        'avatar' => UploadedFile::fake()->image('new.jpg'),
    ]);

    Storage::disk('public')->assertMissing($previousPath);
    Storage::disk('public')->assertExists($user->refresh()->avatar_path);
});

test('a user can remove their profile picture', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $user->avatar_path = UploadedFile::fake()->image('me.jpg')->store('avatars', 'public');
    $user->save();
    $path = $user->avatar_path;

    $this->actingAs($user)
        ->delete(route('profile.avatar.destroy'))
        ->assertRedirect(route('profile.edit'));

    expect($user->refresh()->avatar_path)->toBeNull();
    Storage::disk('public')->assertMissing($path);
});

test('a profile picture must be an image', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('profile.avatar.update'), [
            'avatar' => UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf'),
        ])
        ->assertSessionHasErrors(['avatar' => 'The avatar field must be an image.']);

    expect($user->refresh()->avatar_path)->toBeNull();
});

test('a profile picture cannot be larger than 2MB', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('profile.avatar.update'), [
            'avatar' => UploadedFile::fake()->image('huge.jpg')->size(2049),
        ])
        ->assertSessionHasErrors(['avatar' => 'The avatar field must not be greater than 2048 kilobytes.']);
});

test('guests cannot upload a profile picture', function () {
    $this->post(route('profile.avatar.update'))->assertRedirect(route('login'));
});

test('comment authors are shown with their profile picture', function () {
    Storage::fake('public');
    $group = Group::factory()->create();
    $insight = insightFor($group);
    $member = createApprovedMember($group);
    $member->avatar_path = 'avatars/member.jpg';
    $member->save();
    Comment::factory()->for($insight, 'commentable')->for($member)->create();

    $this->actingAs($member)
        ->get(route('groups.insights.show', [$group, $insight]))
        ->assertInertia(fn ($page) => $page
            ->where('insight.user.avatar', null)
            ->where('comments.0.user.avatar', Storage::disk('public')->url('avatars/member.jpg'))
        );
});
