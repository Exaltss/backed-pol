<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Personnel;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PersonnelController extends Controller
{
    public function index()
    {
        $personnels = Personnel::with('user')->get();
        return view('dashboard.personel', compact('personnels'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'required|string|max:100',
            'no_hp'        => 'required|string|max:20',
            'pangkat'      => 'required|string|max:50',
            'username'     => 'required|string|unique:users,username',
            'password'     => 'required|string|min:6',
            'foto_profil'  => 'nullable|image|mimes:jpeg,png,jpg|max:10000',
        ]);

        $user = User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role'     => 'personel',
        ]);

        $path = null;
        if ($request->hasFile('foto_profil')) {
            $path = $this->saveFoto($request->file('foto_profil'));
        }

        Personnel::create([
            'user_id'      => $user->id,
            'nama_lengkap' => $request->nama_lengkap,
            'nrp'          => $request->no_hp,
            'pangkat'      => $request->pangkat,
            'foto_profil'  => $path,
            'status_aktif' => 'offline',
        ]);

        return back()->with('success', 'Personel berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $personnel = Personnel::with('user')->findOrFail($id);
        $request->validate([
            'nama_lengkap' => 'required|string|max:100',
            'no_hp'        => 'required|string|max:20',
            'pangkat'      => 'required|string|max:50',
            'foto_profil'  => 'nullable|image|mimes:jpeg,png,jpg|max:10000',
        ]);

        $path = $personnel->foto_profil;
        if ($request->hasFile('foto_profil')) {
            // Hapus foto lama jika ada
            if ($path && file_exists(public_path($path))) {
                @unlink(public_path($path));
            }
            $path = $this->saveFoto($request->file('foto_profil'));
        }

        $personnel->update([
            'nama_lengkap' => $request->nama_lengkap,
            'nrp'          => $request->no_hp,
            'pangkat'      => $request->pangkat,
            'foto_profil'  => $path,
        ]);

        if ($request->filled('password')) {
            $personnel->user->update(['password' => Hash::make($request->password)]);
        }

        return back()->with('success', 'Data personel berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $personnel = Personnel::with('user')->findOrFail($id);
        
        // Sesuaikan metode hapus agar konsisten dengan public_path
        if ($personnel->foto_profil && file_exists(public_path($personnel->foto_profil))) {
            @unlink(public_path($personnel->foto_profil));
        }
        
        $personnel->user ? $personnel->user->delete() : $personnel->delete();
        return back()->with('success', 'Personel berhasil dihapus!');
    }

    /**
     * Method private untuk menyimpan foto langsung ke folder public/profile_photos
     */
    private function saveFoto($file): string
    {
        $filename = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
        $file->move(public_path('profile_photos'), $filename);
        return 'profile_photos/' . $filename;
    }
}