<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function users()
    {
        $users = User::all();
        $clientes = Client::all();
        return view('user.index', ['users'=>$users,'clients'=>$clientes]);
    }

   public function store(Request $request)
{
    $data = $request->validate([
        'name'      => 'required|string|max:255',
        'email'     => 'required|email|unique:users,email',
        'password'  => 'required|min:6',
        'role'      => 'required|in:ADMIN,CLIENT',
        'client_id' => 'nullable|required_if:role,CLIENT|exists:clients,id',
    ]);

    User::create([
        'name'      => $data['name'],
        'email'     => $data['email'],
        'password'  => bcrypt($data['password']),
        'role'      => $data['role'],
        'client_id' => $data['role'] === User::ROLE_CLIENT ? $data['client_id'] : null,
    ]);

    return redirect(route('users'))->with('success', 'Usuario creado correctamente.');
}

public function update(Request $request, $id)
{
    $user = User::findOrFail($id);

    $data = $request->validate([
        'name'      => 'required|string|max:255',
        'email'     => 'required|email|unique:users,email,' . $user->id,
        'password'  => 'nullable|min:6',
        'role'      => 'required|in:ADMIN,CLIENT',
        'client_id' => 'nullable|required_if:role,CLIENT|exists:clients,id',
    ]);

    $user->name      = $data['name'];
    $user->email     = $data['email'];
    $user->role      = $data['role'];
    $user->client_id = $data['role'] === User::ROLE_CLIENT ? $data['client_id'] : null;

    if (! empty($data['password'])) {
        $user->password = bcrypt($data['password']);
    }

    $user->save();

    return redirect(route('users'))->with('success', 'Usuario actualizado correctamente.');
}

    public function delete($id)
    {
        $user = User::find($id);
        $user->delete();
        return redirect(route('users'));
    }
}
