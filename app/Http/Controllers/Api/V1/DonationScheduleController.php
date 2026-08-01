<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CampaignIndexRequest;
use App\Http\Resources\Api\V1\DonationScheduleResource;
use App\Models\Campaign;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class DonationScheduleController extends Controller
{
    public function index(CampaignIndexRequest $request): AnonymousResourceCollection
    {
        $query = Campaign::query()
            ->publiclyVisible()
            ->with('bloodCenter');

        if (($search = $request->search()) !== null) {
            $query->where(function (Builder $searchQuery) use ($search): void {
                $searchQuery
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhereHas('bloodCenter', function (Builder $centerQuery) use ($search): void {
                        $centerQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('city', 'like', "%{$search}%");
                    });
            });
        }

        if (($status = $request->status()) !== null) {
            $query->where('status', $status);
        }

        if (($campaignType = $request->campaignType()) !== null) {
            $query->where('campaign_type', $campaignType);
        }

        if (($bloodGroup = $request->bloodGroup()) !== null) {
            $query->where('target_blood_group', $bloodGroup);
        }

        if (($centerId = $request->centerId()) !== null) {
            $query->where('blood_center_id', $centerId);
        }

        return DonationScheduleResource::collection(
            $query->orderedForPublic()
                ->paginate($request->perPage())
                ->withQueryString(),
        );
    }

    public function show(Campaign $campaign): DonationScheduleResource
    {
        abort_unless($campaign->isPubliclyVisible(), 404);

        return new DonationScheduleResource($campaign->load('bloodCenter'));
    }
}
