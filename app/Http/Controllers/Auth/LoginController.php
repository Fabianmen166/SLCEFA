<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
    protected function redirectTo()
    {
        $role = auth()->user()->role;
        switch ($role) {
            case 'admin':
                return route('admin.dashboard');
            case 'gestion_calidad':
                return route('gestion_calidad.dashboard');
            case 'personal_tecnico':
                return route('personal_tecnico.dashboard');
            case 'pasante':
                return route('pasante.dashboard');
        }
    }
}
