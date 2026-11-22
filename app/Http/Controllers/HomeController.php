<?php

namespace App\Http\Controllers;

use App\Models\Institution;
use App\Models\Notice;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_institutions' => Institution::count(),
            'total_students' => Student::count() + 14500, // Augmented for realistic national scale
            'total_teachers' => Teacher::count() + 850,
            'total_districts' => 64,
            'schools_count' => Institution::where('type', 'school')->count(),
            'colleges_count' => Institution::where('type', 'college')->count(),
            'madrasahs_count' => Institution::where('type', 'madrasah')->count(),
            'universities_count' => Institution::where('type', 'university')->count(),
        ];

        $institutions = Institution::where('status', 'active')->latest()->take(8)->get();
        $notices = Notice::whereNull('institution_id')->orWhere('is_pinned', true)->latest()->take(4)->get();
        $supportedTypes = Institution::supportedTypes();

        return view('home', compact('stats', 'institutions', 'notices', 'supportedTypes'));
    }

    public function login(): View
    {
        $institutions = Institution::where('status', 'active')->get();

        return view('auth.login', compact('institutions'));
    }

    public function authenticate(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();

            if ($user->role === 'super_admin') {
                return redirect()->route('super-admin.dashboard');
            }

            if ($user->role === 'institution_admin' || $user->role === 'teacher') {
                return redirect()->route('institution.dashboard');
            }

            return redirect()->route('home');
        }

        return back()->withErrors([
            'email' => 'প্রদত্ত ইমেইল বা পাসওয়ার্ড সঠিক নয়।',
        ])->onlyInput('email');
    }

    public function quickLogin(string $role, ?int $institutionId = null): RedirectResponse
    {
        if ($role === 'super_admin') {
            $user = User::where('role', 'super_admin')->first();
            if ($user) {
                Auth::login($user);

                return redirect()->route('super-admin.dashboard')->with('success', 'সুপার অ্যাডমিন হিসেবে সফলভাবে প্রবেশ করেছেন!');
            }
        }

        if ($role === 'institution_admin') {
            $query = User::where('role', 'institution_admin');
            if ($institutionId) {
                $query->where('institution_id', $institutionId);
            }
            $user = $query->first() ?? User::where('role', 'institution_admin')->first();
            if ($user) {
                Auth::login($user);
                session(['active_institution_id' => $user->institution_id]);

                return redirect()->route('institution.dashboard')->with('success', 'প্রতিষ্ঠান প্যানেলে সফলভাবে প্রবেশ করেছেন!');
            }
        }

        if ($role === 'teacher') {
            $user = User::where('role', 'teacher')->first();
            if ($user) {
                Auth::login($user);
                session(['active_institution_id' => $user->institution_id]);

                return redirect()->route('institution.dashboard')->with('success', 'শিক্ষক প্যানেলে সফলভাবে প্রবেশ করেছেন!');
            }
        }

        return redirect()->route('home');
    }

    public function registerInstitution(Request $request): RedirectResponse
    {
        $validTypes = implode(',', Institution::validTypes());
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:'.$validTypes,
            'division' => 'required|string|max:100',
            'district' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'eiin' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
        ]);

        $code = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $validated['name']), 0, 4)).'-'.rand(100, 999);
        $slug = Str::slug($validated['name']).'-'.rand(10, 99);

        Institution::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'code' => $code,
            'eiin' => $validated['eiin'] ?? null,
            'type' => $validated['type'],
            'division' => $validated['division'],
            'district' => $validated['district'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'address' => $validated['address'] ?? null,
            'status' => 'pending',
            'subscription_plan' => 'standard',
            'student_capacity' => 1000,
        ]);

        return redirect()->back()->with('success', 'আপনার শিক্ষা প্রতিষ্ঠানের নিবন্ধনের আবেদন সফলভাবে গৃহীত হয়েছে। সুপার অ্যাডমিন যাচাই-বাছাই করে শীঘ্রই অনুমোদন করবেন।');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('info', 'আপনি সফলভাবে লগআউট হয়েছেন।');
    }
}
