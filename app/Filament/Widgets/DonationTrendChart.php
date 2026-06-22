<?php

namespace App\Filament\Widgets;

use App\Models\Donation;
use Filament\Widgets\ChartWidget;

class DonationTrendChart extends ChartWidget
{
    protected static ?string $heading = 'Donation Trend';

    protected static ?string $description = 'Completed donations over the last six months.';

    protected static string $color = 'danger';

    protected static ?int $sort = 60;

    protected int | string | array $columnSpan = [
        'md' => 1,
        'xl' => 1,
    ];

    public static function canView(): bool
    {
        $user = auth()->user();

        return $user !== null && ($user->can('donations.view') || $user->can('reports.view'));
    }

    protected function getData(): array
    {
        $months = collect(range(5, 0))->map(fn (int $monthsAgo) => now()->startOfMonth()->subMonths($monthsAgo));

        return [
            'datasets' => [
                [
                    'label' => 'Completed donations',
                    'data' => $months->map(fn ($month) => Donation::where('status', 'completed')
                        ->whereYear('donation_date', $month->year)
                        ->whereMonth('donation_date', $month->month)
                        ->count())->all(),
                    'fill' => true,
                    'tension' => 0.35,
                ],
            ],
            'labels' => $months->map(fn ($month) => $month->format('M'))->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
