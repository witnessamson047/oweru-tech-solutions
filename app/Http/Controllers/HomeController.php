<?php

namespace App\Http\Controllers;

use App\Models\ServicePackage;
use App\Models\CarePlan;

class HomeController extends Controller
{
    public function index()
    {
        $packages = ServicePackage::active()->orderBy('sort_order')->get();
        $carePlans = CarePlan::active()->get();
        $currency = request()->cookie('currency', 'TZS');

        // Home ordering: featured ("Recommended") packages lead, then cheapest first.
        $sortForHome = fn (string $group) => $packages
            ->where('group', $group)
            ->sortBy([['is_featured', 'desc'], ['price_tzs', 'asc']])
            ->values();

        $individualPackages = $sortForHome('individuals');
        $smePackages = $sortForHome('sme');
        $corporatePackages = $sortForHome('corporate');

        // Homepage highlight row: up to 3 featured packages (one per category when
        // possible), falling back to the cheapest overall to fill the row.
        $featured = $packages->filter(fn ($p) => $p->is_featured)->values();
        $picks = $featured->take(3);
        if ($picks->count() < 3) {
            $rest = $packages->whereNotIn('id', $picks->pluck('id'))->sortBy('price_tzs')->values();
            $picks = $picks->concat($rest->take(3 - $picks->count()))->values();
        }

        return view('pages.home', [
            'individualPackages' => $individualPackages,
            'smePackages' => $smePackages,
            'corporatePackages' => $corporatePackages,
            'highlightedPackages' => $picks->take(3)->load('serviceLine'),
            'carePlans' => $carePlans,

            'currency' => $currency,
        ]);
    }
}
