<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\SaveAdminUserRequest;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    public function index()
    {
        abort_unless(request()->user()?->isAdminMatrix(), 403);

        return view('admin-users.index', [
            'admins' => User::query()
                ->with('branch')
                ->where('role', UserRole::AdminBranch)
                ->latest()
                ->get(),
            'branches' => Branch::query()->active()->orderBy('name')->get(),
        ]);
    }

    public function store(SaveAdminUserRequest $request)
    {
        User::query()->create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'role' => UserRole::AdminBranch,
            'phone' => $request->validated('phone'),
            'branch_id' => $request->validated('branch_id'),
            'password' => Hash::make($request->validated('password')),
            'email_verified_at' => now(),
        ]);

        return redirect()->route('admin-users.index')->with('status', 'Admin de filial cadastrado com sucesso.');
    }
}
