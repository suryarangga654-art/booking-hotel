<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => User::query()->latest()->get(['id', 'name', 'email', 'no_telepon', 'peran']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'no_telepon' => 'nullable|string|max:20',
            'peran' => 'required|in:tamu,resepsionis,admin',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create($validated);

        return response()->json([
            'success' => true,
            'data' => $user->only(['id', 'name', 'email', 'no_telepon', 'peran']),
        ], 201);
    }

    public function update(Request $request, int $id)
    {
        $user = User::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'no_telepon' => 'nullable|string|max:20',
            'peran' => 'required|in:tamu,resepsionis,admin',
        ]);

        $user->update($validated);

        return response()->json([
            'success' => true,
            'data' => $user->only(['id', 'name', 'email', 'no_telepon', 'peran']),
        ]);
    }

    public function destroy(int $id)
    {
        $user = User::findOrFail($id);

        if ($user->peran === 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Akun admin tidak dapat dihapus dari halaman pengguna.',
            ], 422);
        }

        $user->delete();

        return response()->json(['success' => true, 'message' => 'Pengguna berhasil dihapus.']);
    }
}