<?php

namespace App\Http\Controllers\Web;

use App\BloodGroup;
use App\CampaignType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\CampaignIndexRequest;
use App\Models\Campaign;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

final class CampaignDirectoryController extends Controller
{
    public function index(CampaignIndexRequest $request): View
    {
        $campaigns = Campaign::query()
            ->select(['id', 'title', 'description', 'start_date', 'end_date', 'blood_center_id', 'location', 'image_path', 'status', 'campaign_type', 'target_blood_group'])
            ->publiclyVisible()
            ->with('bloodCenter:id,name,city')
            ->when($request->search(), function (Builder $query, string $search): void {
                $query->where(function (Builder $searchQuery) use ($search): void {
                    $searchQuery
                        ->where('title', 'like', '%'.$search.'%')
                        ->orWhere('description', 'like', '%'.$search.'%')
                        ->orWhere('location', 'like', '%'.$search.'%');
                });
            })
            ->when($request->campaignType(), fn (Builder $query, CampaignType $type): Builder => $query->where('campaign_type', $type))
            ->when($request->bloodGroup(), fn (Builder $query, BloodGroup $bloodGroup): Builder => $query->where('target_blood_group', $bloodGroup))
            ->orderedForPublic()
            ->paginate(9)
            ->withQueryString();

        return view('web.campaigns.index', compact('campaigns'));
    }

    public function show(Campaign $campaign): View
    {
        abort_unless($campaign->isPubliclyVisible(), 404);

        $campaign->load('bloodCenter:id,name,address,city,phone,email,opening_hours');

        $relatedCampaigns = Campaign::query()
            ->select(['id', 'title', 'start_date', 'end_date', 'blood_center_id', 'location', 'status', 'campaign_type', 'target_blood_group'])
            ->publiclyVisible()
            ->whereKeyNot($campaign->id)
            ->with('bloodCenter:id,name,city')
            ->orderedForPublic()
            ->limit(3)
            ->get();

        return view('web.campaigns.show', compact('campaign', 'relatedCampaigns'));
    }
}
