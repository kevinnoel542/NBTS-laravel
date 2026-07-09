<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use Illuminate\Http\Request;

class CampaignDirectoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Campaign::with('bloodCenter');
        $selectedStatus = $request->status === 'active' ? 'ongoing' : $request->status;

        if ($request->filled('search')) {
            $query->where(function ($campaignQuery) use ($request) {
                $campaignQuery->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%')
                    ->orWhere('location', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $selectedStatus);
        }

        if ($request->filled('type')) {
            $query->where('campaign_type', $request->type);
        }

        if ($request->filled('target')) {
            $query->where('target_blood_group', $request->target);
        }

        $statuses = [
            'upcoming' => 'Upcoming',
            'ongoing' => 'Active',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];

        $campaignTypes = Campaign::query()
            ->whereNotNull('campaign_type')
            ->distinct()
            ->orderBy('campaign_type')
            ->pluck('campaign_type')
            ->values();

        $targetGroups = Campaign::query()
            ->whereNotNull('target_blood_group')
            ->distinct()
            ->orderBy('target_blood_group')
            ->pluck('target_blood_group')
            ->values();

        $campaignStats = [
            'total' => Campaign::count(),
            'active' => Campaign::whereIn('status', ['ongoing', 'upcoming'])->count(),
            'emergency' => Campaign::where('campaign_type', 'emergency')->count(),
            'centers' => Campaign::whereNotNull('blood_center_id')->distinct('blood_center_id')->count('blood_center_id'),
        ];

        $featuredCampaign = Campaign::with('bloodCenter')
            ->whereIn('status', ['ongoing', 'upcoming'])
            ->orderByRaw("CASE status WHEN 'ongoing' THEN 0 WHEN 'upcoming' THEN 1 ELSE 2 END")
            ->orderBy('start_date')
            ->first();

        $campaigns = $query
            ->orderByRaw("CASE status WHEN 'ongoing' THEN 0 WHEN 'upcoming' THEN 1 WHEN 'completed' THEN 2 ELSE 3 END")
            ->latest('start_date')
            ->paginate(9)
            ->withQueryString();

        return view('web.campaigns.index', compact(
            'campaigns',
            'statuses',
            'selectedStatus',
            'campaignTypes',
            'targetGroups',
            'campaignStats',
            'featuredCampaign'
        ));
    }

    public function show(Campaign $campaign)
    {
        $campaign->load('bloodCenter');

        $relatedCampaigns = Campaign::with('bloodCenter')
            ->whereKeyNot($campaign->id)
            ->when($campaign->blood_center_id, fn ($query) => $query->where('blood_center_id', $campaign->blood_center_id))
            ->latest('start_date')
            ->take(3)
            ->get();

        if ($relatedCampaigns->count() < 3) {
            $extraCampaigns = Campaign::with('bloodCenter')
                ->whereKeyNot($campaign->id)
                ->whereNotIn('id', $relatedCampaigns->pluck('id'))
                ->latest('start_date')
                ->take(3 - $relatedCampaigns->count())
                ->get();

            $relatedCampaigns = $relatedCampaigns->merge($extraCampaigns);
        }

        return view('web.campaigns.show', compact('campaign', 'relatedCampaigns'));
    }
}
