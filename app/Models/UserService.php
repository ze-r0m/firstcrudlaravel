<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserService extends Model
{
    use HasFactory;
//    use SoftDeletes;

    protected $fillable = [
        'is_by_agreement',
        'is_hourly_type',
        'is_work_type',
        'hourly_payment',
        'work_payment',
        'is_active',
        'user_id', 'service_id',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
}
