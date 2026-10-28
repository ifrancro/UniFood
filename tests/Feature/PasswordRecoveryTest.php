<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordRecoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_reset_hashes_token_changes_password_and_rejects_reuse(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);
        $record = DB::table('password_reset_tokens')->where('email', $user->email)->first();
        $this->assertNotSame($token, $record->token);
        $this->assertTrue(Hash::check($token, $record->token));
        $data = ['email' => $user->email, 'token' => $token, 'password' => 'NuevaClave123!', 'password_confirmation' => 'NuevaClave123!'];
        $this->post('/password/reset', $data)->assertRedirect('/login');
        $this->assertTrue(Hash::check('NuevaClave123!', $user->fresh()->password));
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);
        $this->post('/password/reset', $data)->assertSessionHasErrors('email');
    }

    public function test_expired_token_cannot_change_password(): void
    {
        $user = User::factory()->create();
        $before = $user->password;
        $token = Password::createToken($user);
        $minutes = config('auth.passwords.users.expire') + 1;
        DB::table('password_reset_tokens')->where('email', $user->email)->update(['created_at' => now()->subMinutes($minutes)]);
        $this->post('/password/reset', ['email' => $user->email, 'token' => $token, 'password' => 'NuevaClave123!', 'password_confirmation' => 'NuevaClave123!'])->assertSessionHasErrors('email');
        $this->assertSame($before, $user->fresh()->password);
    }

    public function test_link_requests_are_throttled_and_store_a_hash(): void
    {
        $user = User::factory()->create();
        $this->post('/password/olvide', ['email' => $user->email])->assertSessionHas('success');
        $hash = DB::table('password_reset_tokens')->where('email', $user->email)->value('token');
        $this->assertStringStartsWith('$2y$', $hash);
        $this->post('/password/olvide', ['email' => $user->email])->assertSessionHasErrors('email');
        $this->assertSame($hash, DB::table('password_reset_tokens')->where('email', $user->email)->value('token'));
    }
}
