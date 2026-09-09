<?php

namespace App\Http\Controllers;

use App\Models\ServicePackage;
use App\Models\CarePlan;

class PackageController extends Controller
{
    public function index()
    {
        $packages = ServicePackage::active()->orderBy('group')->orderBy('sort_order')->get();
        $carePlans = CarePlan::active()->get();
        $currency = request()->cookie('currency', 'TZS');

        return view('pages.packages', [
            'packages' => $packages,
            'carePlans' => $carePlans,
            'currency' => $currency,
        ]);
    }

    public function show(ServicePackage $package)
    {
        return redirect()->route('enquiry.create', ['package' => $package->slug]);
    }
}
