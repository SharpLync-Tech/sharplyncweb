<?php

namespace App\Models\CMS;

use Illuminate\Database\Eloquent\Model;

class AboutValue extends Model
{
    protected $table = 'about_values';

    protected $fillable = [
        'title',
        'content',
        'icon_path',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Only active values
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    // Sort by order
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
