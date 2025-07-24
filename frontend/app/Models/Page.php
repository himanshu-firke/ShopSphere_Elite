<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Page extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'slug',
        'content',
        'meta_title',
        'meta_description',
        'is_active',
        'layout',
        'parent_id',
        'position'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean'
    ];

    /**
     * Get the parent page.
     */
    public function parent()
    {
        return $this->belongsTo(Page::class, 'parent_id');
    }

    /**
     * Get the child pages.
     */
    public function children()
    {
        return $this->hasMany(Page::class, 'parent_id')->ordered();
    }

    /**
     * Get all page revisions.
     */
    public function revisions()
    {
        return $this->hasMany(PageRevision::class)->latest();
    }

    /**
     * Set the page's title and automatically generate the slug.
     */
    public function setTitleAttribute($value)
    {
        $this->attributes['title'] = $value;
        $this->attributes['slug'] = Str::slug($value);
    }

    /**
     * Get the page's URL.
     */
    public function getUrlAttribute(): string
    {
        $segments = collect([$this->slug]);
        $page = $this;

        while ($page->parent_id) {
            $page = $page->parent;
            $segments->prepend($page->slug);
        }

        return '/' . $segments->join('/');
    }

    /**
     * Get the page's breadcrumb trail.
     */
    public function getBreadcrumbAttribute(): array
    {
        $breadcrumb = collect([$this]);
        $page = $this;

        while ($page->parent_id) {
            $page = $page->parent;
            $breadcrumb->prepend($page);
        }

        return $breadcrumb->map(function ($page) {
            return [
                'title' => $page->title,
                'url' => $page->url
            ];
        })->toArray();
    }

    /**
     * Scope a query to only include active pages.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to order by position.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('position');
    }

    /**
     * Create a new revision of the page.
     */
    public function createRevision(): void
    {
        $this->revisions()->create([
            'title' => $this->title,
            'content' => $this->content,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'created_by' => auth()->id()
        ]);
    }

    /**
     * Restore a specific revision.
     */
    public function restoreRevision(PageRevision $revision): void
    {
        $this->update([
            'title' => $revision->title,
            'content' => $revision->content,
            'meta_title' => $revision->meta_title,
            'meta_description' => $revision->meta_description
        ]);

        $this->createRevision();
    }
} 