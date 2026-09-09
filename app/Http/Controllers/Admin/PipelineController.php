<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;

class PipelineController extends Controller
{
    public function index()
    {
        $pipeline = [];
        foreach (Enquiry::STAGES as $stage) {
            $pipeline[$stage] = Enquiry::with(['package', 'owner'])
                ->where('stage', $stage)
                ->latest()
                ->get();
        }

        return view('admin.pipeline.index', compact('pipeline'));
    }
}
