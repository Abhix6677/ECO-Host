<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deployment extends Model
{
    protected $fillable = [
        'uuid',
        'website_id',
        'user_id',
        'status',
        'live_url',
        'log_output',
        'deployed_at',
    ];

    protected $casts = [
        'deployed_at' => 'datetime',
    ];

    public function website()
    {
        return $this->belongsTo(Website::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
