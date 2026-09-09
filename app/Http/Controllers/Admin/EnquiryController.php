<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use App\Models\User;
use Illuminate\Http\Request;

class EnquiryController extends Controller
{
    public function index(Request $request)
    {
        $query = Enquiry::with(['package', 'owner']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('business_name', 'like', "%{$search}%");
            });
        }

        if ($stage = $request->input('stage')) {
            $query->where('stage', $stage);
        }

        if ($source = $request->input('source')) {
            $query->where('source', $source);
        }

        $enquiries = $query->latest()->paginate(20);

        $stats = [
            'total' => Enquiry::count(),
            'new' => Enquiry::where('stage', 'new')->count(),
            'won' => Enquiry::where('stage', 'won')->count(),
            'this_month' => Enquiry::whereMonth('created_at', now()->month)->count(),
        ];

        return view('admin.enquiries.index', compact('enquiries', 'stats'));
    }

    public function show(Enquiry $enquiry)
    {
        $enquiry->load(['package', 'owner', 'scan']);
        $staff = User::all();

        return view('admin.enquiries.show', compact('enquiry', 'staff'));
    }

    public function updateStage(Request $request, Enquiry $enquiry)
    {
        $validated = $request->validate([
            'stage' => 'required|in:' . implode(',', Enquiry::STAGES),
        ]);

        $enquiry->update([
            'stage' => $validated['stage'],
            'last_stage_changed_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Stage updated to ' . str_replace('_', ' ', $validated['stage']) . '.');
    }

    public function updateOwner(Request $request, Enquiry $enquiry)
    {
        $validated = $request->validate([
            'owner_id' => 'nullable|exists:users,id',
        ]);

        $enquiry->update(['owner_id' => $validated['owner_id'] ?? null]);

        return redirect()->back()->with('success', 'Owner updated.');
    }

    public function updateNotes(Request $request, Enquiry $enquiry)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string',
        ]);

        $enquiry->update(['notes' => $validated['notes']]);

        return redirect()->back()->with('success', 'Notes saved.');
    }
}
