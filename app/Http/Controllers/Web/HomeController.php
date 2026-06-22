<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Donation;
use App\Models\Campaign;
use App\Models\BloodCenter;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // Statistics for landing page
        $stats = [
            'donors' => User::where('role', 'donor')->count() + 1250, 
            'donations' => Donation::where('status', 'completed')->count() + 4500,
            'lives_saved' => (Donation::where('status', 'completed')->count() + 4500) * 3,
        ];

        $campaigns = Campaign::where('status', 'active')
            ->with(['bloodCenter'])
            ->latest()
            ->take(3)
            ->get();

        return view('welcome', compact('stats', 'campaigns'));
    }
}
