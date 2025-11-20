<?php

namespace App\Models\CMS;

use Illuminate\Database\Eloquent\Model;

class KnowledgeBaseCategory extends Model
{
    protected $table = 'knowledge_base_categories';

    protected $fillable = [
        'name',
        'slug',
        'sort_order',
    ];

    // A category has many articles
    public function articles()
    {
        return $this->hasMany(KnowledgeBaseArticle::class, 'category_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
