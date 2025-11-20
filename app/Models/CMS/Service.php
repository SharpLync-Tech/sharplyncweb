<?php

namespace App\Models\CMS;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    // Table name
    protected $table = 'services';

    // Allow mass assignment
    protected $fillable = [
        'title',
        'short_description',
        'long_description',
        'icon_path',
        'image_path',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Helpers
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
}