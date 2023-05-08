<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\SoftDeletes;


class Post extends Model
{
    use HasFactory, SoftDeletes;

    #A post belongs to a user
    #To get the owner
    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    #to get the categories under a post
    public function categoryPost(){
        return $this->hasMany(CategoryPost::class);
    }

    public function comments(){
        return $this->hasMany(Comment::class);
    }

    #to get the likes
    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    #returns true if the Auth user alredy liked the post
    public function isLiked()
    {
        return $this->likes()->where('user_id', Auth::user()->id)->exists();
    }

}
