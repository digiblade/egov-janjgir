<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\GalleryAlbum;
use App\Models\GalleryCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['category' => ['nullable', 'string', 'max:100']]);
        $albums = GalleryAlbum::query()->visibleInGallery()
            ->whereHas('visibleImages.media', fn ($q) => $q->where('visibility', 'public'))
            ->with(['category', 'coverImage' => fn ($q) => $q->where('visibility', 'public')])
            ->withCount('visibleImages')
            ->when($filters['category'] ?? null, fn ($q, $slug) => $q->whereHas('category', fn ($c) => $c->where('slug', $slug)))
            ->orderBy('display_order')->orderByDesc('event_date')->paginate(12)->withQueryString();
        $categories = GalleryCategory::query()->active()->orderBy('display_order')->get();

        return view('site.gallery.index', compact('albums', 'categories', 'filters'));
    }

    public function show(GalleryAlbum $galleryAlbum): View
    {
        abort_unless(GalleryAlbum::query()->visibleInGallery()->whereKey($galleryAlbum->getKey())->whereHas('visibleImages.media', fn ($q) => $q->where('visibility', 'public'))->exists(), 404);
        $galleryAlbum->load([
            'category',
            'coverImage' => fn ($q) => $q->where('visibility', 'public'),
            'visibleImages' => fn ($q) => $q->with(['media' => fn ($media) => $media->where('visibility', 'public')]),
        ]);

        return view('site.gallery.show', compact('galleryAlbum'));
    }
}
