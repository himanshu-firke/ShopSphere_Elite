<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageRevision extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'page_id',
        'title',
        'content',
        'meta_title',
        'meta_description',
        'created_by'
    ];

    /**
     * Get the page that owns the revision.
     */
    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    /**
     * Get the user who created the revision.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Compare this revision with another.
     */
    public function compareWith(PageRevision $other): array
    {
        return [
            'title' => [
                'old' => $other->title,
                'new' => $this->title,
                'changed' => $other->title !== $this->title
            ],
            'content' => [
                'old' => $other->content,
                'new' => $this->content,
                'changed' => $other->content !== $this->content
            ],
            'meta_title' => [
                'old' => $other->meta_title,
                'new' => $this->meta_title,
                'changed' => $other->meta_title !== $this->meta_title
            ],
            'meta_description' => [
                'old' => $other->meta_description,
                'new' => $this->meta_description,
                'changed' => $other->meta_description !== $this->meta_description
            ]
        ];
    }
} 