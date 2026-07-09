<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Donation;
use App\Models\Campaign;
use App\Models\BloodCenter;
use App\Models\Article;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $completedDonations = Donation::where('status', 'completed')->count();

        $stats = [
            'donors' => User::where('role', 'donor')->count(),
            'donations' => $completedDonations,
            'lives_saved' => $completedDonations * 3,
            'centers' => BloodCenter::where('is_active', true)->count(),
            'campaigns' => Campaign::whereIn('status', ['ongoing', 'upcoming'])->count(),
        ];

        $campaigns = Campaign::whereIn('status', ['ongoing', 'upcoming'])
            ->with(['bloodCenter'])
            ->latest('start_date')
            ->take(3)
            ->get();

        $centers = BloodCenter::where('is_active', true)
            ->latest()
            ->take(3)
            ->get();

        $articles = Article::where('status', 'published')
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('welcome', compact('stats', 'campaigns', 'centers', 'articles'));
    }
}
