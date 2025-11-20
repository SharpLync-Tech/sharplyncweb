<?php

namespace App\Models\CMS;

use Illuminate\Database\Eloquent\Model;

class SeoMeta extends Model
{
    protected $table = 'seo_meta';

    protected $fillable = [
        'page_slug',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_title',
        'og_description',
        'og_image',
    ];

    // Quick fetch by slug
    public static function forPage($slug)
    {
        return self::where('page_slug', $slug)->first();
    }
}
