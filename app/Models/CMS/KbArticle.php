<?php

namespace App\Models\CMS;

use Illuminate\Database\Eloquent\Model;

class KbArticle extends Model
{
    protected $table = 'knowledge_base_articles';

    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'content',
        'is_public',
        'sort_order',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    // Each article belongs to a category
    public function category()
    {
        return $this->belongsTo(KnowledgeBaseCategory::class, 'category_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('title');
    }

    public function scopePublicOnly($query)
    {
        return $query->where('is_public', 1);
    }
}
