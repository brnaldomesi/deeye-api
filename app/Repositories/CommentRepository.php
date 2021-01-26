<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Auth;

use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Activity;

class CommentRepository
{
    public function commentsForPost($postId)
    {
        $limit = config('app.pagination_limit');
        $post = Post::find($postId);
        return $post->comments()->whereNull('parent_id')->orderBy('updated_at', 'desc')->paginate($limit);
    }

    public function commentsForComment($commentId)
    {
        $limit = config('app.pagination_limit');
        $comment = Comment::find($commentId);
        return $comment->comments()->orderBy('updated_at', 'desc')->paginate($limit);
    }

    public function createCommentForPost($postId, $text)
    {
        $activity = new Activity;
        $activity->save();

        $post = Post::find($postId);

        $comment = new Comment;
        $comment->text = $text;
        $comment->activity_id = $activity->id;
        $user = User::find(Auth::user()->id);
        $comment->profile_id = $user->profile->id;
        $comment->post_id = $post->id;
        $comment->save();

        return $comment;
    }

    public function createCommentForComment($parentId, $text)
    {
        $activity = new Activity;
        $activity->save();

        $parent = Comment::find($parentId);

        $comment = new Comment;
        $comment->text = $text;
        $comment->activity_id = $activity->id;
        $user = User::find(Auth::user()->id);
        $comment->profile_id = $user->profile->id;
        $comment->post_id = $parent->post->id;
        $comment->parent_id = $parent->id;
        $comment->save();

        return $comment;
    }

}