<?php

namespace App\Models\CMS;

use Illuminate\Database\Eloquent\Model;

class ContactInfo extends Model
{
    protected $table = 'contact_info';

    protected $fillable = [
        'phone',
        'email',
        'address',
        'google_map_embed',
        'hours',
        'facebook_url',
        'instagram_url',
        'linkedin_url',
        'twitter_url',
        'youtube_url',
    ];

    // Useful helper: always get the first (and only) row
    public static function getInfo()
    {
        return self::first();
    }
}
