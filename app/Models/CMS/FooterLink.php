<?php

namespace App\Models\CMS;

use Illuminate\Database\Eloquent\Model;

class FooterLink extends Model
{
    protected $table = 'footer_links';

    protected $fillable = [
        'group',
        'label',
        'url',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Only active links
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    // Order links within a group
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    // Filter by group (e.g., FooterLink::group('company')->get())
    public function scopeGroup($query, $group)
    {
        return $query->where('group', $group);
    }
}
