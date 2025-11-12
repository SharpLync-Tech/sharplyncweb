<?php

namespace App\Models\CMS;

use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    // ✅ use existing MySQL (CMS) connection
    protected $connection = 'mysql';

    // ✅ correct table name
    protected $table = 'testimonials';

    // ✅ correct field names based on your current table
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

    // ✅ optional convenience scope
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
}