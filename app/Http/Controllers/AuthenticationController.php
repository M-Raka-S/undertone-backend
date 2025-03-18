<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthenticationController extends Controller
{
    public function __construct(Request $request) {
        parent::__construct($request);
        $this->setModel(User::class);
    }

    public function register()
    {
        $this->validator([
            'username' => 'required|unique:users',
            'password' => [
                'required',
                'confirmed',
                Password::min(8)->mixedCase()->numbers()->uncompromised()
            ],
            'password_confirmation' => 'required',
        ], [
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
        auth()->user()->currentAccessToken()->delete();
        return $this->ok('log out successful.');
    }

    public function fail()
    {
        return $this->unauthenticated('please login first.');
    }
}
