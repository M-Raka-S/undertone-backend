<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
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
        if(!$this->current_user($id)) {
            return $this->unauthorized('changing other\'s profile is prohibited.');
        }
        $this->validator([
            'username' => "required|unique:users,username,{$id}",
            'password' => [
                'required',
                'confirmed',
                Password::min(8)->mixedCase()->numbers()->uncompromised()
            ],
            'password_confirmation' => ['required'],
        ]);
        return $this->update($id) ? $this->ok('user updated.') : $this->invalid('update failed.');
    }

    public function remove()
    {
        return $this->unauthorized('this behaviour is disabled.');
    }
}
