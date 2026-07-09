<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BloodCenter;
use Illuminate\Http\Request;

class BloodCenterDirectoryController extends Controller
{
    public function index(Request $request)
    {
        $activeCenters = BloodCenter::where('is_active', true);

        $cityFilters = (clone $activeCenters)
            ->whereNotNull('city')
            ->select('city')
            ->distinct()
            ->orderBy('city')
            ->pluck('city');

        $centerStats = [
            'active' => (clone $activeCenters)->count(),
            'cities' => $cityFilters->count(),
            'with_phone' => (clone $activeCenters)->whereNotNull('phone')->count(),
            'with_hours' => (clone $activeCenters)->whereNotNull('opening_hours')->count(),
        ];

        $query = BloodCenter::where('is_active', true);

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('address', 'like', '%' . $request->search . '%')
                  ->orWhere('city', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        $centers = $query
            ->orderBy('city')
            ->orderBy('name')
            ->paginate(9);

        return view('web.centers.index', compact('centers', 'cityFilters', 'centerStats'));
    }

    public function show(BloodCenter $center)
    {
        $relatedCenters = BloodCenter::query()
            ->whereKeyNot($center->id)
            ->where('is_active', true)
            ->when($center->city, fn ($query) => $query->where('city', $center->city))
            ->orderBy('name')
            ->take(4)
            ->get();

        if ($relatedCenters->count() < 4) {
            $fallbackCenters = BloodCenter::query()
                ->whereKeyNot($center->id)
                ->where('is_active', true)
                ->whereNotIn('id', $relatedCenters->pluck('id'))
                ->orderBy('city')
                ->orderBy('name')
                ->take(4 - $relatedCenters->count())
                ->get();

            $relatedCenters = $relatedCenters->concat($fallbackCenters);
        }

        return view('web.centers.show', compact('center', 'relatedCenters'));
    }
}
