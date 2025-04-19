<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Login FO
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = Auth::guard('fo')->attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Login gagal. Email atau password salah.'
            ], 401);
        }

        return $this->respondWithToken($token);
    }

    // Register
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role' => 'required|string',
            'alamat' => 'nullable|string',
            'telepon' => 'nullable|string',
            'foto_profil' => 'nullable|file',
            'status' => 'nullable|string',
            'tanggal_bergabung' => 'nullable|string',
            'plat_jeep' => 'nullable|string',
            'foto_jeep' => 'nullable|file',
            'jumlah_jeep' => 'nullable|string',
            'jabatan' => 'nullable|string',
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'alamat' => $request->alamat,
            'telepon' => $request->telepon,
            'foto_profil' => $request->foto_profil,
            'status' => $request->status,
            'tanggal_bergabung' => $request->tanggal_bergabung,
            'plat_jeep' => $request->plat_jeep,
            'foto_jeep' => $request->foto_jeep,
            'jumlah_jeep' => $request->jumlah_jeep,
            'jabatan' => $request->jabatan,
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json(compact('user', 'token'));
    }

    // Ambil data FO yang login
    public function profile()
    {
        $user = Auth::guard('fo')->user();
        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    // Logout
    public function logout(Request $request)
{
    try {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil, token dihapus.'
        ]);
    } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Logout gagal, token tidak valid.',
            'error' => $e->getMessage()
        ], 500);
    }
}

    // Refresh token
    public function refresh()
    {
        $token = JWTAuth::refresh();
        return $this->respondWithToken($token);
    }

    // Helper untuk return token JWT
    protected function respondWithToken($token)
    {
        $user = Auth::guard('fo')->user();
        return response()->json([
            'success' => true,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
            'role' => $user->role
        ]);
    }

    public function me()
    {
        return response()->json(Auth::user());
    }
}
