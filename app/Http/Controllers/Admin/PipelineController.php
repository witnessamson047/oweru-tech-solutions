<?php

namespace App\Http\Controllers\Admin;

use App\Concerns\SortsListings;
use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class PipelineController extends Controller
{
    use SortsListings;

    /**
     * How many cards to show per column before offering "view all".
     *
     * A kanban board is a *summary* of the pipeline. Without a cap a single
     * busy stage pulls thousands of rows and the page stops being usable.
     */
    private const COLUMN_LIMIT = 12;

    public function index(Request $request)
    {
        try {
            return $this->renderIndex($request);
        } catch (\Exception $e) {
            Log::error('Pipeline error: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);

            // Graceful fallback: a DB outage (or a missing migration) must
            // show staff a friendly page, not a raw Laravel crash.
            return view('admin.error', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function renderIndex(Request $request)
    {
        $search = trim((string) $request->query('search'));
        $owner = $request->query('owner');

        // Counts for every stage in one query, not six.
        $counts = Enquiry::query()
            ->when($search, fn ($q) => $this->applySearch($q, $search))
            ->when($owner, fn ($q) => $q->where('owner_id', $owner))
            ->selectRaw('stage, COUNT(*) as aggregate')
            ->groupBy('stage')
            ->pluck('aggregate', 'stage');

        $pipeline = [];

        foreach (Enquiry::STAGES as $stage) {
            $query = Enquiry::with(['package', 'owner'])
                ->where('stage', $stage)
                ->when($search, fn ($q) => $this->applySearch($q, $search))
                ->when($owner, fn ($q) => $q->where('owner_id', $owner))
                ->latest();

            $pipeline[$stage] = $query->take(self::COLUMN_LIMIT)->get();
        }

        $total = (int) $counts->sum();

        // Enquiry stores budget as a free-text range (e.g. "500k - 1m"), so it
        // cannot be summed. Report the invoiced value from real money instead.
        $value = [
            'won' => (float) Invoice::where('status', Invoice::STATUS_PAID)->sum('total'),
            'outstanding' => (float) Invoice::notCancelled()->sum('balance_due'),
        ];

        return view('admin.pipeline.index', [
            'pipeline' => $pipeline,
            'counts' => $counts,
            'total' => $total,
            'value' => $value,
            'search' => $search,
            'owners' => User::where('role', 'admin')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    private function applySearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('business_name', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        });
    }
}