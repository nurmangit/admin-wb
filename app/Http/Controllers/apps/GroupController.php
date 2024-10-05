<?php

namespace App\Http\Controllers\apps;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function index()
    {
        return view(
            'content.group.list',
            [
                "roles" => Role::get()
            ]
        );
    }

    public function view($uuid)
    {
        return view('content.group.view');
    }

    public function create()
    {
        // dd(Permission::get()->pluck('name'));
        return view(
            'content.group.create',
            [
                "permissions" => Permission::get()->pluck('name'),
            ]
        );
    }

    public function edit($uuid)
    {
        $role = Role::findOrFail($uuid);
        return view(
            'content.group.edit',
            [
                "role" => $role,
                "permissions" => Permission::get()->pluck('name'),
                "role_permissions" => $role->getAllPermissions()->pluck('name'),
            ]
        );
    }

    public function update(Request $request, $uuid)
    {
        $role = Role::findOrFail($uuid);
        $role->name = strtoupper($request->get('name'));
        $role->update();
        $permissions = $request->except(['name', '_token', '_method']);
        foreach ($permissions as $key => $value) {
            $permission = Permission::findByName(str_replace('_', ' ', $key));
            if ($permission) {
                $permission->assignRole($role);
                $permission->update();
            }
        }
        return redirect()->route(
            'account.group.edit',
            ['uuid' => $uuid]
        )->with('success', 'Group updated successfully');
    }

    public function delete($uuid)
    {
        $vehicleType = Role::findOrFail($uuid);
        $vehicleType->delete();

        return redirect()->route('account.group.list')->with('success', 'Group deleted successfully.');
    }

    public function store(Request $request)
    {
        $role = Role::create(
            [
                'name' => strtoupper($request->get('name')),
                'guard_name' => 'web',
            ]
        );
        $permissions = $request->except(['name', '_token']);
        foreach ($permissions as $key => $value) {
            $permission = Permission::findByName(str_replace('_', ' ', $key));
            if ($permission) {
                $permission->assignRole($role);
                $permission->update();
            }
        }
        return redirect()->route('account.group.list')->with('success', 'Group created successfully');
    }
}
