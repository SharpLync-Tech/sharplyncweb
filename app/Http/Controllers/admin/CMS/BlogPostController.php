<?php

namespace App\Http\Controllers\Admin\CMS;

use App\Http\Controllers\Controller;
use App\Models\CMS\BlogPost;
use App\Models\CMS\BlogCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BlogPostController extends Controller
{
    public function index()
    {
        $posts = BlogPost::with('category')
            ->orderByDesc('published_at')
            ->get();

        return view('admin.cms.blog.posts.index', compact('posts'));
    }

    public function create()
    {
        $categories = BlogCategory::orderBy('name')->where('is_active', 1)->get();
        return view('admin.cms.blog.posts.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'         => 'required|string|max:255',
            'slug'          => 'nullable|string|max:255|unique:blog_posts',
            'summary'       => 'nullable|string',
            'content'       => 'nullable|string',
            'category_id'   => 'required|exists:blog_categories,id',
            'featured_image' => 'nullable|file|mimes:png,jpg,jpeg,webp|max:4096',
            'published_at'  => 'nullable|date',
            'is_active'     => 'boolean',
        ]);

        $data = $request->all();

        // Auto-generate slug if not provided
        if (!$data['slug']) {
            $data['slug'] = Str::slug($request->title);
        }

        // Upload featured image
        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $request->file('featured_image')
                ->store('cms/blog/posts', 'public');
        }

        BlogPost::create($data);

        return redirect()->route('admin.cms.blog.posts.index')
            ->with('success', 'Blog post created successfully.');
    }

    public function edit(BlogPost $post)
    {
        $categories = BlogCategory::orderBy('name')->where('is_active', 1)->get();
        return view('admin.cms.blog.posts.edit', compact('post', 'categories'));
    }

    public function update(Request $request, BlogPost $post)
    {
        $request->validate([
            'title'         => 'required|string|max:255',
            'slug'          => 'nullable|string|max:255|unique:blog_posts,slug,' . $post->id,
            'summary'       => 'nullable|string',
            'content'       => 'nullable|string',
            'category_id'   => 'required|exists:blog_categories,id',
            'featured_image' => 'nullable|file|mimes:png,jpg,jpeg,webp|max:4096',
            'published_at'  => 'nullable|date',
            'is_active'     => 'boolean',
        ]);

        $data = $request->all();

        if (!$data['slug']) {
            $data['slug'] = Str::slug($request->title);
        }

        // Handle image replacement
        if ($request->hasFile('featured_image')) {
            if ($post->featured_image) {
                Storage::disk('public')->delete($post->featured_image);
            }

            $data['featured_image'] = $request->file('featured_image')
                ->store('cms/blog/posts', 'public');
        }

        $post->update($data);

        return redirect()->route('admin.cms.blog.posts.index')
            ->with('success', 'Blog post updated successfully.');
    }

    public function destroy(BlogPost $post)
    {
        if ($post->featured_image) {
            Storage::disk('public')->delete($post->featured_image);
        }

        $post->delete();

        return redirect()->route('admin.cms.blog.posts.index')
            ->with('success', 'Blog post deleted successfully.');
    }
}
