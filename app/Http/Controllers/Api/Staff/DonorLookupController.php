<?php

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class DonorLookupController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->user()->can('donors.view')) {
            abort(403);
        }

        $data = $request->validate([
            'query' => 'required|string|min:2',
            'type' => 'nullable|string|in:any,donor_id,phone,name,email,qr',
        ]);

        $query = $data['query'];
        $type = $data['type'] ?? 'any';

        $donors = User::query()
            ->with('donorProfile')
            ->role('donor')
            ->when(in_array($type, ['any', 'phone'], true), function ($builder) use ($query, $type) {
                $builder->when($type === 'phone', fn ($q) => $q->where('phone', 'like', "%{$query}%"));
            })
            ->where(function ($builder) use ($query, $type) {
                if (in_array($type, ['any', 'phone'], true)) {
                    $builder->orWhere('phone', 'like', "%{$query}%");
                }

                if (in_array($type, ['any', 'name'], true)) {
                    $builder->orWhere('name', 'like', "%{$query}%");
                }

                if (in_array($type, ['any', 'email'], true)) {
                    $builder->orWhere('email', 'like', "%{$query}%");
                }

                if (in_array($type, ['any', 'donor_id', 'qr'], true)) {
                    $builder->orWhereHas('donorProfile', fn ($profile) => $profile->where('donor_id', $query));
                }
            })
            ->limit(20)
            ->get();

        return UserResource::collection($donors);
    }

    public function show(User $donor, Request $request)
    {
        if (!$request->user()->can('donors.view') || !$donor->hasRole('donor')) {
            abort(403);
        }

        return new UserResource($donor->load('donorProfile'));
    }
}
