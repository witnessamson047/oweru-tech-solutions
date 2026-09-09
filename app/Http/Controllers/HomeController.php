<?php

namespace App\Http\Controllers;

use App\Models\ServicePackage;
use App\Models\CarePlan;
use App\Models\PackageExclusion;
use App\Models\DeliveryCommitment;

class HomeController extends Controller
{
    public function index()
    {
        $packages = ServicePackage::active()->orderBy('sort_order')->get();
        $carePlans = CarePlan::active()->get();
        $exclusions = PackageExclusion::where('active', true)->orderBy('sort_order')->get();
        $commitments = DeliveryCommitment::where('active', true)->orderBy('sort_order')->get();

        $currency = request()->cookie('currency', 'TZS');

        return view('pages.home', [
            'individualPackages' => $packages->where('group', 'individuals'),
            'smePackages' => $packages->where('group', 'sme'),
            'corporatePackages' => $packages->where('group', 'corporate'),
            'carePlans' => $carePlans,
            'exclusions' => $exclusions,
            'commitments' => $commitments,
            'currency' => $currency,
        ]);
    }
}
