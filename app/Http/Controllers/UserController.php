<?php

namespace App\Http\Controllers;

use App\Models\User;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function __construct(Request $request) {
        parent::__construct($request);
        $this->setModel(User::class);
    }

    public function show($page)
    {
        return $this->read($page);
    }

    public function pick($id)
    {
        return $this->get($id);
    }

    public function make()
    {
        return $this->unauthorized('this behaviour is disabled. please register instead.');
    }

    public function edit($id)
    {
        if(!$this->is_current_user($id)) {
            return $this->unauthorized('changing other\'s profile is prohibited.');
        }
        $rules = [
            'username' => "required|unique:users,username,$id",
            'password' => ['required', 'confirmed'],
            'password_confirmation' => 'required',
        ];

        if (App::environment(['production', 'testing'])) {
            $rules['password'][] = Password::min(8)->mixedCase()->numbers()->uncompromised();
        }

        $this->validator($rules);
        $this->validator([
            'current_password' => "required",
        ]);
        if(!Hash::check(request('current_password'), auth()->user()->password)) {
            return $this->unauthorized('current password is incorrect.');
        }
        return $this->update($id) ? $this->ok('user updated.') : $this->invalid('update failed.');
    }

    public function remove()
    {
        return $this->unauthorized('this behaviour is disabled.');
    }
}
