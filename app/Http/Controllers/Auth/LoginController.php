<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Auth\AuthServiceInterface;
use App\Helpers\Traits\ResponseTrait;

class LoginController extends Controller
{

    use ResponseTrait;

    protected $authService;

    public function __construct(AuthServiceInterface $authService)
    {
        $this->authService = $authService;
    }

    public function login(Request $request)
    {
        $credentials = $request->only(['email', 'password']);
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        return $this->authService->login($credentials);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);
        $userData = $request->only(['name', 'email', 'password']);

        return $this->authService->register($userData);
    }

    public function logout(Request $request)
    {
        return $this->authService->logout($request->user());
    }
}
