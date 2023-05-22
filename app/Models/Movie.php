<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    use HasFactory;

    protected $fillable=[
        'title',
        'summary',
        'cover_image',
        'imdb_rating',
        'pdf_link',
        'author_id'
    ];

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function author()
    {
        return $this->belongsTo(Author::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'movie_tag')->withTimestamps();
    }

    public function genres()
    {
        return $this->belongsToMany(Genre::class, 'movie_genre')->withTimestamps();
    }
}
