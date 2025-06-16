<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\RedirectIfAuthenticatedwithRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear usuarios con diferentes roles para las pruebas
        $this->admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => 'admin'
        ]);

        $this->gestionCalidad = User::factory()->create([
            'name' => 'Gestión Calidad User',
            'email' => 'gestion@example.com',
            'role' => 'gestion_calidad'
        ]);

        $this->personalTecnico = User::factory()->create([
            'name' => 'Personal Técnico User',
            'email' => 'tecnico@example.com',
            'role' => 'personal_tecnico'
        ]);

        $this->pasante = User::factory()->create([
            'name' => 'Pasante User',
            'email' => 'pasante@example.com',
            'role' => 'pasante'
        ]);
    }

    /** @test */
    public function it_can_assign_role_to_user()
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'pasante'
        ]);

        $this->assertEquals('pasante', $user->role);

        $user->role = 'admin';
        $user->save();

        $this->assertEquals('admin', $user->fresh()->role);
    }

    /** @test */
    public function role_middleware_allows_correct_roles()
    {
        $middleware = new RoleMiddleware();
        $request = new Request();
        
        // Simular un usuario autenticado
        Auth::shouldReceive('check')->andReturn(true);
        Auth::shouldReceive('user')->andReturn($this->admin);
        
        $next = function ($request) {
            return 'allowed';
        };

        $response = $middleware->handle($request, $next, 'admin');
        $this->assertEquals('allowed', $response);
    }

    /** @test */
    public function role_middleware_blocks_incorrect_roles()
    {
        $middleware = new RoleMiddleware();
        $request = new Request();
        
        // Simular un usuario autenticado con rol incorrecto
        Auth::shouldReceive('check')->andReturn(true);
        Auth::shouldReceive('user')->andReturn($this->pasante);
        
        $next = function ($request) {
            return 'allowed';
        };

        $response = $middleware->handle($request, $next, 'admin');
        $this->assertNotEquals('allowed', $response);
    }

    /** @test */
    public function role_middleware_allows_multiple_roles()
    {
        $middleware = new RoleMiddleware();
        $request = new Request();
        
        // Simular un usuario autenticado
        Auth::shouldReceive('check')->andReturn(true);
        Auth::shouldReceive('user')->andReturn($this->gestionCalidad);
        
        $next = function ($request) {
            return 'allowed';
        };

        $response = $middleware->handle($request, $next, 'admin', 'gestion_calidad');
        $this->assertEquals('allowed', $response);
    }

    /** @test */
    public function redirect_middleware_redirects_to_correct_dashboard()
    {
        $middleware = new RedirectIfAuthenticatedwithRole();
        $request = new Request();

        // Probar redirección para cada rol
        $roles = [
            'admin' => 'admin.dashboard',
            'gestion_calidad' => 'gestion_calidad.dashboard',
            'personal_tecnico' => 'personal_tecnico.dashboard',
            'pasante' => 'pasante.dashboard'
        ];

        foreach ($roles as $role => $route) {
            Auth::shouldReceive('check')->andReturn(true);
            Auth::shouldReceive('user')->andReturn(User::factory()->create(['role' => $role]));

            $next = function ($request) {
                return 'next';
            };

            $response = $middleware->handle($request, $next);
            $this->assertNotEquals('next', $response);
        }
    }

    /** @test */
    public function it_validates_role_during_user_creation()
    {
        $validRoles = ['admin', 'gestion_calidad', 'personal_tecnico', 'pasante'];
        
        foreach ($validRoles as $role) {
            $user = User::factory()->create([
                'name' => 'Test User',
                'email' => "test_{$role}@example.com",
                'role' => $role
            ]);

            $this->assertDatabaseHas('users', [
                'email' => "test_{$role}@example.com",
                'role' => $role
            ]);
        }

        // Intentar crear un usuario con un rol inválido
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        User::factory()->create([
            'name' => 'Invalid Role User',
            'email' => 'invalid@example.com',
            'role' => 'invalid_role'
        ]);
    }
} 