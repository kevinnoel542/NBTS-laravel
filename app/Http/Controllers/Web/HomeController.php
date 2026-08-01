<?php

namespace App\Http\Controllers\Web;

use App\DonationStatus;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\BloodCenter;
use App\Models\Campaign;
use App\Models\Donation;
use App\Models\User;
use App\RoleName;
use Illuminate\Contracts\View\View;

final class HomeController extends Controller
{
    public function __invoke(): View
    {
        $completedDonations = Donation::query()
            ->where('status', DonationStatus::Completed)
            ->count();

        $stats = [
            'campaigns' => Campaign::query()->publiclyVisible()->count(),
            'centers' => BloodCenter::query()->active()->count(),
            'donations' => $completedDonations,
            'donors' => User::query()
                ->whereHas('roles', fn ($query) => $query->where('name', RoleName::Donor->value))
                ->count(),
            'lives_supported' => $completedDonations * 3,
        ];

        $campaigns = Campaign::query()
            ->select(['id', 'title', 'description', 'start_date', 'end_date', 'blood_center_id', 'location', 'status', 'campaign_type', 'target_blood_group'])
            ->publiclyVisible()
            ->with('bloodCenter:id,name,city')
            ->orderedForPublic()
            ->limit(3)
            ->get();

        $centers = BloodCenter::query()
            ->select(['id', 'name', 'address', 'city', 'opening_hours', 'center_type'])
            ->active()
            ->orderBy('city')
            ->orderBy('name')
            ->limit(4)
            ->get();

        $articles = Article::query()
            ->select(['id', 'title', 'slug', 'category', 'summary', 'image_path', 'published_at', 'reading_time_minutes', 'is_featured'])
            ->published()
            ->orderedForPublic()
            ->limit(3)
            ->get();

        return view('welcome', compact('articles', 'campaigns', 'centers', 'stats'));
    }
}
