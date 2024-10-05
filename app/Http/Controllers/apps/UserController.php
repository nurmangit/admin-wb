<?php

namespace App\Http\Controllers\apps;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Models\Role;
use App\Models\User;

class UserController extends Controller
{
  public function index()
  {
    return view('content.user.list', [
      "users" => User::get()
    ]);
  }

  public function view($uuid)
  {
    return view('content.user.view', [
      'user' => User::findOrFail($uuid)
    ]);
  }

  public function create()
  {
    return view('content.user.create');
  }

  public function edit($uuid)
  {
    $user = User::with('roles')->findOrFail($uuid);
    $user_role = null;
    foreach ($user->roles as $role) {
      $user_role = $role;
    }
    return view('content.user.edit', [
      "user" => $user,
      "roles" => Role::get(),
      "user_role" => $user_role,
    ]);
  }

  public function update(UserUpdateRequest $request, $uuid)
  {
    $user = User::findOrFail($uuid);
    $validated = $request->validated();

    // Only update the password if it's provided
    if ($request->filled('password')) {
      $validated['password'] = bcrypt($validated['password']); // Hash the password
    } else {
      unset($validated['password']); // Remove password from the validated array if not provided
    }

    $role = Role::findById($validated['group']); // Get the role
    $user->assignRole($role);
    $user->update();

    $validated['is_active'] = $request->has('is_active') ? true : false;

    $user->update($validated);

    return redirect()->route('account.user.edit', ['uuid' => $uuid])
      ->with('success', 'User updated successfully');
  }

  public function delete($uuid)
  {
    $user = User::findOrFail($uuid);
    $user->delete();

    return redirect()->route('account.user.list')
      ->with('success', 'User deleted successfully.');
  }

  public function store(UserStoreRequest $request)
  {
    $validated = $request->validated();

    $validated['is_active'] = $request->has('is_active') ? true : false;

    User::create($validated);

    return redirect()->route('account.user.list')
      ->with('success', 'User created successfully');
  }
}
