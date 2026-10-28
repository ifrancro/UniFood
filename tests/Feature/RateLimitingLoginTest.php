<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class RateLimitingLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_failed_login_attempts_are_throttled_after_five_failures(): void
    {
        $this->seed();
        $email = 'estudiante@unifood.bo';

        // Primeros 5 intentos fallidos
        for ($i = 1; $i <= 5; $i++) {
            $response = $this->post('/login', [
                'email' => $email,
                'password' => 'clave_incorrecta',
            ]);

            $response->assertSessionHasErrors('email');
            $this->assertStringContainsString(
                'Las credenciales proporcionadas no coinciden',
                session('errors')->first('email')
            );
        }

        // Intento 6: debe ser bloqueado por RateLimiter
        $response = $this->post('/login', [
            'email' => $email,
            'password' => 'clave_incorrecta',
        ]);

        $response->assertSessionHasErrors('email');
        $response->assertSessionHas('lockout_seconds');
        $this->assertStringContainsString(
            'Demasiados intentos fallidos',
            session('errors')->first('email')
        );

        // Al visitar /login con la sesión de bloqueo, debe mostrarse el banner integrado
        $loginPage = $this->get('/login');
        $loginPage->assertOk();
        $loginPage->assertSee('data-lockout-timer', false);
        $loginPage->assertSee('Acceso temporalmente bloqueado');
    }

    public function test_successful_login_clears_rate_limiter(): void
    {
        $this->seed();
        $email = 'estudiante@unifood.bo';

        // 2 intentos fallidos
        $this->post('/login', ['email' => $email, 'password' => 'erronea']);
        $this->post('/login', ['email' => $email, 'password' => 'erronea']);

        // Intento exitoso con la contraseña correcta del seeder ('password')
        $response = $this->post('/login', [
            'email' => $email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/catalogo');
        $this->assertAuthenticated();

        // Verificar que el RateLimiter se limpió
        $throttleKey = mb_strtolower($email) . '|127.0.0.1';
        $this->assertSame(0, RateLimiter::attempts($throttleKey));
    }
}

