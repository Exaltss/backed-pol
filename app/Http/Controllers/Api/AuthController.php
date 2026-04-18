<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate(['username'=>'required','password'=>'required']);

        if (!Auth::attempt($request->only('username','password'))) {
            return response()->json(['message'=>'Login Gagal. Cek username & password.'], 401);
        }

        $user = User::where('username', $request->username)->firstOrFail();

        // ✅ HANYA PERSONEL YANG BISA LOGIN KE APLIKASI
        if ($user->role !== 'personel') {
            Auth::logout();
            return response()->json(['message'=>'Akses ditolak. Aplikasi ini hanya untuk Personel.'], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message'      => 'Login berhasil',
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'user'         => $user->load('personnel'),
        ]);
    }

    public function user(Request $request)
    {
        return response()->json($request->user()->load('personnel'));
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    }
}