<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Website extends Model
{
    protected $fillable = [
        'uuid',
        'user_id',
        'name',
        'slug',
        'original_filename',
        'storage_path',
        'public_path',
        'size_kb',
        'status',
        'live_url',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function deployments()
    {
        return $this->hasMany(Deployment::class);
    }
}
