<?php

namespace App\Http\Controllers\Admin\CMS;

use App\Http\Controllers\Controller;
use App\Models\CMS\KbArticle;
use App\Models\CMS\KbCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class KbArticleController extends Controller
{
    public function index()
    {
        $articles = KbArticle::with('category')
            ->orderByDesc('published_at')
            ->get();

        return view('admin.cms.kb.articles.index', compact('articles'));
    }

    public function create()
    {
        $categories = KbCategory::where('is_active', 1)->orderBy('name')->get();
        return view('admin.cms.kb.articles.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'         => 'required|string|max:255',
            'slug'          => 'nullable|string|max:255|unique:kb_articles',
            'summary'       => 'nullable|string',
            'content'       => 'nullable|string',
            'category_id'   => 'required|exists:kb_categories,id',
            'attachment'    => 'nullable|file|max:8192', // up to 8MB
            'published_at'  => 'nullable|date',
            'is_active'     => 'boolean',
        ]);

        $data = $request->all();

        // Slug auto-generation
        if (!$data['slug']) {
            $data['slug'] = Str::slug($request->title);
        }

        // Attachment upload
        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')
                ->store('cms/kb/attachments', 'public');
        }

        KbArticle::create($data);

        return redirect()->route('admin.cms.kb.articles.index')
            ->with('success', 'Article created successfully.');
    }

    public function edit(KbArticle $article)
    {
        $categories = KbCategory::where('is_active', 1)->orderBy('name')->get();
        return view('admin.cms.kb.articles.edit', compact('article', 'categories'));
    }

    public function update(Request $request, KbArticle $article)
    {
        $request->validate([
            'title'         => 'required|string|max:255',
            'slug'          => 'nullable|string|max:255|unique:kb_articles,slug,' . $article->id,
            'summary'       => 'nullable|string',
            'content'       => 'nullable|string',
            'category_id'   => 'required|exists:kb_categories,id',
            'attachment'    => 'nullable|file|max:8192',
            'published_at'  => 'nullable|date',
            'is_active'     => 'boolean',
        ]);

        $data = $request->all();

        if (!$data['slug']) {
            $data['slug'] = Str::slug($request->title);
        }

        if ($request->hasFile('attachment')) {
            if ($article->attachment) {
                Storage::disk('public')->delete($article->attachment);
            }

            $data['attachment'] = $request->file('attachment')
                ->store('cms/kb/attachments', 'public');
        }

        $article->update($data);

        return redirect()->route('admin.cms.kb.articles.index')
            ->with('success', 'Article updated successfully.');
    }

    public function destroy(KbArticle $article)
    {
        if ($article->attachment) {
            Storage::disk('public')->delete($article->attachment);
        }

        $article->delete();

        return redirect()->route('admin.cms.kb.articles.index')
            ->with('success', 'Article deleted successfully.');
    }
}
