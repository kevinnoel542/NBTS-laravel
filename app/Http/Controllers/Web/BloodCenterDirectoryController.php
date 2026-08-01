<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\BloodCenterIndexRequest;
use App\Models\BloodCenter;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

final class BloodCenterDirectoryController extends Controller
{
    public function index(BloodCenterIndexRequest $request): View
    {
        $cities = BloodCenter::query()
            ->active()
            ->whereNotNull('city')
            ->distinct()
            ->orderBy('city')
            ->pluck('city');

        $centers = BloodCenter::query()
            ->select(['id', 'name', 'address', 'city', 'phone', 'email', 'opening_hours', 'services', 'capacity_label', 'estimated_wait_minutes', 'center_type', 'image_path'])
            ->active()
            ->when($request->search(), function (Builder $query, string $search): void {
                $query->where(function (Builder $searchQuery) use ($search): void {
                    $searchQuery
                        ->where('name', 'like', '%'.$search.'%')
                        ->orWhere('address', 'like', '%'.$search.'%')
                        ->orWhere('city', 'like', '%'.$search.'%');
                });
            })
            ->when($request->city(), fn (Builder $query, string $city): Builder => $query->where('city', $city))
            ->orderBy('city')
            ->orderBy('name')
            ->paginate(9)
            ->withQueryString();

        return view('web.centers.index', compact('centers', 'cities'));
    }

    public function show(BloodCenter $bloodCenter): View
    {
        abort_unless($bloodCenter->is_active, 404);

        $relatedCenters = BloodCenter::query()
            ->select(['id', 'name', 'address', 'city', 'opening_hours', 'center_type'])
            ->active()
            ->whereKeyNot($bloodCenter->id)
            ->when($bloodCenter->city, fn (Builder $query, string $city): Builder => $query->where('city', $city))
            ->orderBy('name')
            ->limit(3)
            ->get();

        return view('web.centers.show', compact('bloodCenter', 'relatedCenters'));
    }
}
