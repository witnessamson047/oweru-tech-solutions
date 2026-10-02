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
        $currency = request()->cookie('currency', 'TZS');

        $relatedPackages = ServicePackage::active()
            ->where(function ($query) use ($package) {
                if ($package->service_line_id) {
                    $query->where('service_line_id', $package->service_line_id);
                } else {
                    $query->where('group', $package->group);
                }
            })
            ->where('id', '!=', $package->id)
            ->orderBy('sort_order')
            ->limit(3)
            ->get();

        return view('pages.package-detail', [
            'package' => $package,
            'relatedPackages' => $relatedPackages,
            'currency' => $currency,
        ]);
    }
}
