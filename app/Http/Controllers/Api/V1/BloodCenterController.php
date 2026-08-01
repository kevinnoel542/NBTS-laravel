<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\BloodCenterResource;
use App\Models\BloodCenter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class BloodCenterController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min(max($request->integer('per_page', 20), 1), 50);
        $search = trim($request->string('q')->toString());
        $city = trim($request->string('city')->toString());
        $service = trim($request->string('service')->toString());

        $bloodCenters = BloodCenter::query()
            ->active()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($searchQuery) use ($search): void {
                    $searchQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%");
                });
            })
            ->when($city !== '', fn ($query) => $query->where('city', $city))
            ->when($service !== '', fn ($query) => $query->whereJsonContains('services', $service))
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return BloodCenterResource::collection($bloodCenters);
    }

    public function show(BloodCenter $bloodCenter): BloodCenterResource
    {
        abort_unless($bloodCenter->is_active, 404);

        return new BloodCenterResource($bloodCenter);
    }
}
