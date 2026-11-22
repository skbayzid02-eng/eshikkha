<?php

namespace App\Http\Controllers;

use App\Models\Institution;
use App\Models\Notice;
use App\Models\Student;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SuperAdminController extends Controller
{
    public function dashboard(): View
    {
        $totalInstitutions = Institution::count();
        $activeInstitutions = Institution::where('status', 'active')->count();
        $pendingInstitutions = Institution::where('status', 'pending')->count();
        $suspendedInstitutions = Institution::where('status', 'suspended')->count();

        $byType = [
            'school' => Institution::where('type', 'school')->count(),
            'college' => Institution::where('type', 'college')->count(),
            'madrasah' => Institution::where('type', 'madrasah')->count(),
            'university' => Institution::where('type', 'university')->count(),
        ];

        $divisions = [
            'Dhaka' => Institution::where('division', 'Dhaka')->count(),
            'Chittagong' => Institution::where('division', 'Chittagong')->count(),
            'Rajshahi' => Institution::where('division', 'Rajshahi')->count(),
            'Khulna' => Institution::where('division', 'Khulna')->count(),
            'Barishal' => Institution::where('division', 'Barishal')->count(),
            'Sylhet' => Institution::where('division', 'Sylhet')->count(),
            'Rangpur' => Institution::where('division', 'Rangpur')->count(),
            'Mymensingh' => Institution::where('division', 'Mymensingh')->count(),
        ];

        $totalStudents = Student::count() + 18500;
        $totalTeachers = Teacher::count() + 1120;
        $monthlyRevenue = 385000; // in BDT

        $recentInstitutions = Institution::latest()->take(6)->get();
        $notices = Notice::whereNull('institution_id')->latest()->take(5)->get();

        return view('superadmin.dashboard', compact(
            'totalInstitutions',
            'activeInstitutions',
            'pendingInstitutions',
            'suspendedInstitutions',
            'byType',
            'divisions',
            'totalStudents',
            'totalTeachers',
            'monthlyRevenue',
            'recentInstitutions',
            'notices'
        ));
    }

    public function institutions(Request $request): View
    {
        $query = Institution::withCount(['academicClasses', 'students', 'teachers']);

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('division') && $request->division !== 'all') {
            $query->where('division', $request->division);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('eiin', 'like', "%{$search}%")
                    ->orWhere('district', 'like', "%{$search}%");
            });
        }

        $institutions = $query->latest()->paginate(10)->withQueryString();
        $supportedTypes = Institution::supportedTypes();

        return view('superadmin.institutions', compact('institutions', 'supportedTypes'));
    }

    public function showInstitution(Institution $institution): View
    {
        $institution->load(['academicClasses.sections', 'teachers', 'students.academicClass', 'users']);

        return view('superadmin.institution_show', compact('institution'));
    }

    public function storeInstitution(Request $request): RedirectResponse
    {
        $validTypes = implode(',', Institution::validTypes());
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:'.$validTypes,
            'division' => 'required|string',
            'district' => 'required|string',
            'phone' => 'required|string',
            'email' => 'required|email',
            'eiin' => 'nullable|string',
            'subscription_plan' => 'required|in:starter,standard,premium,enterprise',
            'student_capacity' => 'required|integer|min:50',
            'address' => 'nullable|string',
        ]);

        $code = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $validated['name']), 0, 4)).'-'.rand(100, 999);
        $slug = Str::slug($validated['name']).'-'.rand(10, 99);

        $institution = Institution::create(array_merge($validated, [
            'code' => $code,
            'slug' => $slug,
            'status' => 'active',
        ]));

        return redirect()->route('super-admin.institutions.show', $institution->id)
            ->with('success', 'নতুন শিক্ষা প্রতিষ্ঠান সফলভাবে যুক্ত করা হয়েছে!');
    }

    public function updateStatus(Request $request, Institution $institution): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:active,pending,suspended',
        ]);

        $institution->update(['status' => $validated['status']]);

        $msg = match ($validated['status']) {
            'active' => 'প্রতিষ্ঠানটি সক্রিয় করা হয়েছে!',
            'suspended' => 'প্রতিষ্ঠানটি সাময়িক স্থগিত করা হয়েছে!',
            'pending' => 'প্রতিষ্ঠানটি মুলতুবি করা হয়েছে!',
        };

        return back()->with('success', $msg);
    }

    public function updatePlan(Request $request, Institution $institution): RedirectResponse
    {
        $validated = $request->validate([
            'subscription_plan' => 'required|in:starter,standard,premium,enterprise',
            'student_capacity' => 'required|integer|min:50',
        ]);

        $institution->update($validated);

        return back()->with('success', 'সাবস্ক্রিপশন প্যাকেজ ও ধারণক্ষমতা আপডেট সম্পন্ন হয়েছে!');
    }

    public function impersonate(Institution $institution): RedirectResponse
    {
        session(['active_institution_id' => $institution->id]);

        return redirect()->route('institution.dashboard')
            ->with('success', "আপনি এখন '{$institution->name}' এর প্যানেল পর্যবেক্ষণ করছেন।");
    }

    public function notices(): View
    {
        $notices = Notice::whereNull('institution_id')->latest()->paginate(10);

        return view('superadmin.notices', compact('notices'));
    }

    public function storeNotice(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'audience' => 'required|in:all,institutions,teachers,students',
            'is_pinned' => 'nullable|boolean',
        ]);

        Notice::create([
            'institution_id' => null, // Nationwide notice
            'title' => $validated['title'],
            'content' => $validated['content'],
            'audience' => $validated['audience'],
            'published_at' => Carbon::today()->toDateString(),
            'is_pinned' => $request->boolean('is_pinned'),
        ]);

        return back()->with('success', 'সারাদেশে জরুরি বিজ্ঞপ্তি সফলভাবে প্রচার করা হয়েছে!');
    }

    public function subscriptions(): View
    {
        $institutions = Institution::withCount('students')->get();

        return view('superadmin.subscriptions', compact('institutions'));
    }
}
