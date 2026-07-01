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

        if ($request->filled('search')) {
            $query->where(function ($campaignQuery) use ($request) {
                $campaignQuery->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%')
                    ->orWhere('location', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $status = $request->status === 'active' ? 'ongoing' : $request->status;
            $query->where('status', $status);
        }

        $campaigns = $query->latest()->paginate(9)->withQueryString();
        return view('web.campaigns.index', compact('campaigns'));
    }

    public function show(Campaign $campaign)
    {
        $campaign->load('bloodCenter');

        $relatedCampaigns = Campaign::with('bloodCenter')
            ->whereKeyNot($campaign->id)
            ->when($campaign->blood_center_id, fn ($query) => $query->where('blood_center_id', $campaign->blood_center_id))
            ->latest()
            ->take(3)
            ->get();

        return view('web.campaigns.show', compact('campaign', 'relatedCampaigns'));
    }
}
