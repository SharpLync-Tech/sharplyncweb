<?php

namespace App\Models\CMS;

use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    protected $connection = 'sharplync_cms'; // ✅ matches your CMS DB
    protected $table = 'testimonials';

    protected $fillable = [
        'customer_name',
        'customer_position',
        'customer_company',
        'testimonial_text',
        'rating',
        'display_order',
        'is_featured',
        'is_active',
        'created_by',
    ];

    public $timestamps = true;

    // Optional: handy scope
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
}