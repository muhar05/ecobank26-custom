<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'phone' => $user->phone,
                'current_password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
                'phone' => $user->phone,
                'current_password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_authenticated_user_can_change_own_phone_with_correct_password(): void
    {
        $user = User::factory()->create([
            'phone' => '081111111111',
            'password' => bcrypt('secret123'),
        ]);

        $newPhone = '082222222222';

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $newPhone,
                'current_password' => 'secret123',
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/login');
        $this->assertGuest();

        $user->refresh();
        $this->assertSame($newPhone, $user->phone);
    }

    public function test_phone_number_is_normalized_consistently(): void
    {
        $user = User::factory()->create([
            'phone' => '081111111111',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => '+62 822-3344-5566',
                'current_password' => 'secret123',
            ]);

        $response->assertSessionHasNoErrors();
        $user->refresh();
        $this->assertSame('082233445566', $user->phone);
    }

    public function test_phone_number_is_normalized_with_620_and_62_prefixes(): void
    {
        $user = User::factory()->create([
            'phone' => '081111111111',
            'password' => bcrypt('secret123'),
        ]);

        // Test 620 prefix
        $this->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => '620844444444',
                'current_password' => 'secret123',
            ])
            ->assertSessionHasNoErrors();

        $user->refresh();
        $this->assertSame('0844444444', $user->phone);

        // Test 62 prefix
        $this->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => '628555555555',
                'current_password' => 'secret123',
            ])
            ->assertSessionHasNoErrors();

        $user->refresh();
        $this->assertSame('08555555555', $user->phone);
    }

    public function test_invalid_phone_format_is_rejected(): void
    {
        $user = User::factory()->create([
            'phone' => '081111111111',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => '0211234567',
                'current_password' => 'secret123',
            ]);

        $response->assertSessionHasErrors('phone');
        $user->refresh();
        $this->assertSame('081111111111', $user->phone);
    }

    public function test_phone_with_alphabet_is_rejected(): void
    {
        $user = User::factory()->create([
            'phone' => '081111111111',
            'password' => bcrypt('secret123'),
        ]);

        // Prefix alphabet
        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => 'abc081234567890',
                'current_password' => 'secret123',
            ]);

        $response->assertSessionHasErrors('phone');
        $user->refresh();
        $this->assertSame('081111111111', $user->phone);

        // Suffix alphabet
        $response2 = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => '081234567890abc',
                'current_password' => 'secret123',
            ]);

        $response2->assertSessionHasErrors('phone');
        $user->refresh();
        $this->assertSame('081111111111', $user->phone);
    }

    public function test_phone_with_email_format_is_rejected(): void
    {
        $user = User::factory()->create([
            'phone' => '081111111111',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => '081234567890@email.com',
                'current_password' => 'secret123',
            ]);

        $response->assertSessionHasErrors('phone');
        $user->refresh();
        $this->assertSame('081111111111', $user->phone);
    }

    public function test_phone_too_short_is_rejected(): void
    {
        $user = User::factory()->create([
            'phone' => '081111111111',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => '0812345',
                'current_password' => 'secret123',
            ]);

        $response->assertSessionHasErrors('phone');
        $user->refresh();
        $this->assertSame('081111111111', $user->phone);
    }

    public function test_phone_too_long_is_rejected(): void
    {
        $user = User::factory()->create([
            'phone' => '081111111111',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => '08123456789012345678', // 20 digits, regex allows max 16
                'current_password' => 'secret123',
            ]);

        $response->assertSessionHasErrors('phone');
        $user->refresh();
        $this->assertSame('081111111111', $user->phone);
    }

    public function test_phone_with_parentheses_and_spaces_and_dashes_is_accepted(): void
    {
        $user = User::factory()->create([
            'phone' => '081111111111',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => '(0812) 3456-7890',
                'current_password' => 'secret123',
            ]);

        $response->assertSessionHasNoErrors();
        $user->refresh();
        $this->assertSame('081234567890', $user->phone);
    }

    public function test_profile_update_is_rate_limited(): void
    {
        $user = User::factory()->create([
            'phone' => '081111111111',
            'password' => bcrypt('secret123'),
        ]);

        // Attempt 6 requests
        for ($i = 0; $i < 6; $i++) {
            $this->actingAs($user)
                ->patch('/profile', [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => '081111111111',
                    'current_password' => 'wrong-pass',
                ]);
        }

        // 7th request should be throttled
        $response = $this->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => '081111111111',
                'current_password' => 'secret123',
            ]);

        $response->assertStatus(429);
    }

    public function test_race_condition_unique_violation_returns_validation_error_not_500(): void
    {
        $user = User::factory()->create([
            'phone' => '081111111111',
            'password' => bcrypt('secret123'),
        ]);

        $newPhone = '082222222222';

        // Simulate race condition: another concurrent process inserts $newPhone before save
        User::saving(function ($model) use ($user, $newPhone) {
            static $triggered = false;
            if (! $triggered && $model->id === $user->id && $model->phone === $newPhone) {
                $triggered = true;
                \Illuminate\Support\Facades\DB::table('users')->insert([
                    'name' => 'Concurrent User',
                    'email' => 'concurrent@example.com',
                    'phone' => $newPhone,
                    'password' => bcrypt('password'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $newPhone,
                'current_password' => 'secret123',
            ]);

        $response->assertSessionHasErrors('phone');
        $response->assertStatus(302);
    }

    public function test_wrong_password_causes_update_to_be_rejected(): void
    {
        $user = User::factory()->create([
            'phone' => '081111111111',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'New Name',
                'email' => $user->email,
                'phone' => '082222222222',
                'current_password' => 'wrong-password',
            ]);

        $response->assertSessionHasErrors('current_password');
        $user->refresh();
        $this->assertSame('081111111111', $user->phone);
        $this->assertNotSame('New Name', $user->name);
    }

    public function test_phone_already_used_by_another_user_is_rejected(): void
    {
        $otherUser = User::factory()->create(['phone' => '089999999999']);
        $user = User::factory()->create([
            'phone' => '081111111111',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => '089999999999',
                'current_password' => 'secret123',
            ]);

        $response->assertSessionHasErrors('phone');
        $user->refresh();
        $this->assertSame('081111111111', $user->phone);
    }

    public function test_user_retains_same_account_data_after_phone_change(): void
    {
        $user = User::factory()->create([
            'name' => 'Budi Sudarsono',
            'email' => 'budi@example.com',
            'phone' => '081111111111',
            'password' => bcrypt('secret123'),
        ]);

        $originalId = $user->id;

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Budi Sudarsono',
                'email' => 'budi@example.com',
                'phone' => '082222222222',
                'current_password' => 'secret123',
            ]);

        $response->assertSessionHasNoErrors();
        $user->refresh();

        $this->assertSame($originalId, $user->id);
        $this->assertSame('Budi Sudarsono', $user->name);
        $this->assertSame('budi@example.com', $user->email);
        $this->assertSame('082222222222', $user->phone);
    }

    public function test_user_is_redirected_to_login_and_session_invalidated_after_successful_change(): void
    {
        $user = User::factory()->create([
            'phone' => '081111111111',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => '082222222222',
                'current_password' => 'secret123',
            ]);

        $response->assertRedirect('/login');
        $response->assertSessionHas('status');
        $this->assertGuest();
    }

    public function test_user_can_login_using_new_phone_number(): void
    {
        $user = User::factory()->create([
            'phone' => '081111111111',
            'password' => bcrypt('secret123'),
        ]);

        $this->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => '082222222222',
                'current_password' => 'secret123',
            ]);

        $loginResponse = $this->post('/login', [
            'login' => '082222222222',
            'password' => 'secret123',
        ]);

        $loginResponse->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticatedAs($user);
    }

    public function test_old_phone_number_cannot_be_used_to_login_after_change(): void
    {
        $user = User::factory()->create([
            'phone' => '081111111111',
            'password' => bcrypt('secret123'),
        ]);

        $this->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => '082222222222',
                'current_password' => 'secret123',
            ]);

        $loginResponse = $this->post('/login', [
            'login' => '081111111111',
            'password' => 'secret123',
        ]);

        $loginResponse->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_user_cannot_determine_target_update_using_user_id(): void
    {
        $victim = User::factory()->create([
            'name' => 'Victim User',
            'phone' => '081111111111',
            'password' => bcrypt('victim_pass'),
        ]);

        $attacker = User::factory()->create([
            'name' => 'Attacker User',
            'phone' => '082222222222',
            'password' => bcrypt('attacker_pass'),
        ]);

        $response = $this
            ->actingAs($attacker)
            ->patch('/profile', [
                'user_id' => $victim->id,
                'id' => $victim->id,
                'name' => 'Attacker Updated',
                'email' => $attacker->email,
                'phone' => '083333333333',
                'current_password' => 'attacker_pass',
            ]);

        $victim->refresh();
        $attacker->refresh();

        // Victim is untouched
        $this->assertSame('081111111111', $victim->phone);
        $this->assertSame('Victim User', $victim->name);

        // Attacker is the one whose phone updated
        $this->assertSame('083333333333', $attacker->phone);
    }

    public function test_user_cannot_determine_target_update_using_another_users_phone(): void
    {
        $victim = User::factory()->create([
            'name' => 'Victim User',
            'phone' => '081111111111',
            'password' => bcrypt('victim_pass'),
        ]);

        $attacker = User::factory()->create([
            'name' => 'Attacker User',
            'phone' => '082222222222',
            'password' => bcrypt('attacker_pass'),
        ]);

        $response = $this
            ->actingAs($attacker)
            ->patch('/profile?phone=081111111111', [
                'target_phone' => $victim->phone,
                'name' => 'Attacker Updated',
                'email' => $attacker->email,
                'phone' => '083333333333',
                'current_password' => 'attacker_pass',
            ]);

        $victim->refresh();
        $attacker->refresh();

        $this->assertSame('081111111111', $victim->phone);
        $this->assertSame('083333333333', $attacker->phone);
    }

    public function test_guest_cannot_access_profile_update(): void
    {
        $user = User::factory()->create(['phone' => '081111111111']);

        $response = $this->patch('/profile', [
            'name' => 'New Name',
            'email' => 'guest@example.com',
            'phone' => '082222222222',
            'current_password' => 'password',
        ]);

        $response->assertRedirect('/login');
        $user->refresh();
        $this->assertSame('081111111111', $user->phone);
    }

    public function test_submitting_same_phone_does_not_invalidate_session(): void
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'phone' => '081111111111',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Updated Name',
                'email' => $user->email,
                'phone' => '081111111111',
                'current_password' => 'secret123',
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/profile');
        $this->assertAuthenticatedAs($user);

        $user->refresh();
        $this->assertSame('Updated Name', $user->name);
        $this->assertSame('081111111111', $user->phone);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }
}
