<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Http\Resources\CampaignResource;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    public function index()
    {
        $campaigns = Campaign::with('bloodCenter')->where('status', 'active')->latest()->get();
        return CampaignResource::collection($campaigns);
    }

    public function show($id)
    {
        $campaign = Campaign::with('bloodCenter')->find($id);
        
        if (!$campaign) {
            return response()->json(['message' => 'Campaign not found'], 404);
        }

        return new CampaignResource($campaign);
    }
}
