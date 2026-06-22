<?php

namespace App\Filament\Widgets;

use App\Models\BloodInventory;
use Filament\Widgets\ChartWidget;

class BloodGroupInventoryChart extends ChartWidget
{
    protected static ?string $heading = 'Blood Group Stock';

    protected static ?string $description = 'Available units grouped by blood type.';

    protected static string $color = 'success';

    protected static ?int $sort = 70;

    protected int | string | array $columnSpan = [
        'md' => 1,
        'xl' => 1,
    ];

    public static function canView(): bool
    {
        return (bool) auth()->user()?->can('inventory.view');
    }

    protected function getData(): array
    {
        $groups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
        $inventory = BloodInventory::selectRaw('blood_group, SUM(available_units) as total')
            ->groupBy('blood_group')
            ->pluck('total', 'blood_group');

        return [
            'datasets' => [
                [
                    'label' => 'Available units',
                    'data' => collect($groups)->map(fn (string $group) => (int) ($inventory[$group] ?? 0))->all(),
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $groups,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
