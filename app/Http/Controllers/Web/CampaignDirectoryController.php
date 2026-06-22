<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use Illuminate\Http\Request;

class CampaignDirectoryController extends Controller
{
    public function index()
    {
        $campaigns = Campaign::with('bloodCenter')->latest()->paginate(9);
        return view('web.campaigns.index', compact('campaigns'));
    }

    public function show(Campaign $campaign)
    {
        return view('web.campaigns.show', compact('campaign'));
    }
}
