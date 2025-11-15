<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
    public function index()
    {
        $users = User::orderBy('id', 'desc')->paginate(20);
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'nullable|string|unique:users,username',
            'password' => 'required|min:6|confirmed',
            'is_admin' => 'sometimes|boolean'
        ]);
        $data['password'] = Hash::make($data['password']);
        $data['is_admin'] = $request->boolean('is_admin');
        // Ensure an email exists (DB requires unique non-null). Use username-based placeholder when not provided.
        if (empty($data['username'])) {
            $data['email'] = 'user' . time() . '@no-email.local';
        } else {
            $data['email'] = $data['username'] . '@no-email.local';
        }
        User::create($data);
        return redirect()->route('admin.users.index')->with('success', 'User created');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'nullable|string|unique:users,username,' . $user->id,
            'password' => 'nullable|min:6|confirmed',
            'is_admin' => 'sometimes|boolean'
        ]);
        if ($data['password'] ?? null) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        $data['is_admin'] = $request->boolean('is_admin');
        // Maintain or set a placeholder email if none exists. If username provided, make an email from it.
        if (empty($user->email) || str_ends_with($user->email, '@no-email.local')) {
            if (!empty($data['username'])) {
                $data['email'] = $data['username'] . '@no-email.local';
            } else {
                $data['email'] = $user->email ?? ('user' . time() . '@no-email.local');
            }
        }
        $user->update($data);
        return redirect()->route('admin.users.index')->with('success', 'User updated');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User deleted');
    }
}
