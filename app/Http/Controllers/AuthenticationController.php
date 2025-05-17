<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Laravel\Sanctum\TransientToken;

class AuthenticationController extends Controller
{
    public function __construct(Request $request) {
        parent::__construct($request);
        $this->setModel(User::class);
    }

    public function register()
    {
        $rules = [
            'username' => 'required|unique:users',
            'password' => ['required', 'confirmed'],
            'password_confirmation' => 'required',
        ];

        if (App::environment('production')) {
            $rules['password'][] = Password::min(8)->mixedCase()->numbers()->uncompromised();
        }

        $this->validator($rules, [
            'username',
            'password',
            'password_confirmation'
        ]);
        $this->merge('password', Hash::make($this->request->password));
        $this->create();
        return $this->created('registration successful.');
    }

    public function login()
    {
        $this->validator([
            'username' => 'required',
            'password' => 'required',
            'remember' => 'nullable',
        ], [
            'username',
            'password',
            'remember'
        ]);
        if(!auth()->attempt(
            $this->request->except('remember'),
            $this->request->boolean('remember')
        )) {
            return $this->unauthenticated('invalid credentials.');
        }
        return $this->ok([
            'message' => 'login successful.',
            'token' => auth()->user()->createToken('undertone.')->plainTextToken,
        ]);
    }

    public function logout()
    {
        $user = auth()->user();

        if (!$user) {
            return $this->unauthenticated('please login first.');
        }

        $token = $user->currentAccessToken();

        if ($token instanceof TransientToken) {
            return $this->ok('log out successful (test mode).');
        }

        $token->delete();
        return $this->ok('log out successful.');
    }

    public function fail()
    {
        return $this->unauthenticated('please login first.');
    }
}
