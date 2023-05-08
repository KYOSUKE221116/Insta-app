<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Comment;

class CommentController extends Controller
{
    public function __construct(Comment $comment){
        $this->comment = $comment;
    }

    public function store($post_id, Request $request){
        $request->validate([
            'comment_body' . $post_id => 'required|max:150'
        ],
        [
            'comment_body' . $post_id . '.required' => 'Cannot submit an empty comment.',
            'comment_body' . $post_id . '.max' => 'The comment must not be greater than 150 characters.'
        ]);

        $this->comment->user_id = Auth::user()->id;
        $this->comment->post_id = $post_id;
        $this->comment->body = $request->input('comment_body' . $post_id);
        $this->comment->save();

        return redirect()->back();
    }

    public function destroy($id)
    {
        $this->comment->destroy($id);
        return redirect()->back();
    }
}
