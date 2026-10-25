<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use App\Models\OrganizerProfile;
use App\Models\SponsorProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showLogin()      { return view('auth.login'); }
    public function showRegister()   { return view('auth.register'); }
    public function showAdminLogin() { return view('auth.admin-login'); }

    public function login(Request $request)
    {
        $data = $request->validate([
            'role' => 'required|in:sponsor,organizer',
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt(['email' => $data['email'], 'password' => $data['password'], 'role' => $data['role']], $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('home'));
        }

        return back()->withInput($request->only('email', 'role'))
            ->withErrors(['email' => 'Email, kata sandi, atau peran tidak sesuai.']);
    }

    public function adminLogin(Request $request)
    {
        $data = $request->validate(['email' => 'required|email', 'password' => 'required']);

        if (Auth::attempt($data + ['role' => 'admin'], $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->route('admin.dashboard');
        }

        return back()->withInput($request->only('email'))->withErrors(['email' => 'Kredensial Admin tidak valid.']);
    }

    public function register(Request $request)
    {
        $role = $request->input('role');
        abort_unless(in_array($role, ['sponsor', 'organizer'], true), 422);

        $rules = [
            'role' => 'required|in:sponsor,organizer',
            'name' => 'required|string|max:100',
            'kontak' => 'required|string|max:30',
            'email' => 'required|email|max:150|unique:users,email',
            'password' => ['required', Password::min(8)->letters()->numbers()],
            'dokumen' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'setuju' => 'accepted',
        ];
        $rules += $role === 'sponsor' ? [
            'nama_perusahaan' => 'required|string|max:150',
            'nib' => 'required|string|max:30',
            'fokus_csr' => 'required|array|min:1',
            'fokus_csr.*' => 'string|max:50',
        ] : [
            'nama_organisasi' => 'required|string|max:150',
            'jenis' => 'required|in:HIMA/UKM,NGO,NPO,Komunitas',
            'alamat' => 'required|string|max:255',
        ];

        $data = $request->validate($rules);
        $path = $request->file('dokumen')->store('legalitas');

        $user = DB::transaction(function () use ($data, $role, $path) {
            $user = User::create([
                'name' => $data['name'], 'email' => $data['email'], 'password' => $data['password'],
                'role' => $role, 'status_verifikasi' => 'Menunggu',
            ]);

            if ($role === 'sponsor') {
                SponsorProfile::create([
                    'user_id' => $user->id, 'nama_perusahaan' => $data['nama_perusahaan'], 'nib' => $data['nib'],
                    'fokus_csr' => implode(', ', $data['fokus_csr']), 'kontak' => $data['kontak'], 'dokumen_legalitas' => $path,
                ]);
            } else {
                OrganizerProfile::create([
                    'user_id' => $user->id, 'nama_organisasi' => $data['nama_organisasi'], 'jenis' => $data['jenis'],
                    'kontak' => $data['kontak'], 'alamat' => $data['alamat'], 'dokumen_legalitas' => $path,
                ]);
            }
            return $user;
        });

        Notifikasi::kirimAdmin("Akun {$role} baru menunggu verifikasi: ".$user->displayName(), route('admin.akun'));
        Notifikasi::kirim($user->id, 'Akun berhasil dibuat. Admin akan memeriksa identitas dan dokumen Anda.');

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home')->with('success', 'Pendaftaran berhasil. Akun Anda menunggu verifikasi Admin.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
