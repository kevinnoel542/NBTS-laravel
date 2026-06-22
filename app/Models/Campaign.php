<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'start_date',
        'end_date',
        'blood_center_id',
        'location',
        'image_path',
        'status',
        'campaign_type',
        'target_blood_group',
        'low_stock_alert_id',
    ];

    public function bloodCenter()
    {
        return $this->belongsTo(BloodCenter::class);
    }

    public function lowStockAlert()
    {
        return $this->belongsTo(LowStockAlert::class);
    }
}
