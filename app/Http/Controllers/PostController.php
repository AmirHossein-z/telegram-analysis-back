<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class PostController extends Controller
{
    //

    public function __construct()
    {

    }

    public function getPosts($channelId, Request $request)
    {
        try {
            $page = $request->query('page', 1);

            $posts = DB::table('posts')->where('channel_id', $channelId)->paginate(10);

            if (count($posts) > 0) {
                return response()->json(['status' => true, 'value' => $posts]);

            } else {
                return response()->json(['status' => false, 'value' => []]);
            }
        } catch (\Throwable $e) {
            return response()->json($e);
        }

    }

    public function getPost($postId)
    {
        $post = DB::select('SELECT * FROM posts WHERE id = ? LIMIT 0,1', [$postId]);

        if (count($post) > 0) {
            return response()->json(['status' => true, 'value' => $post]);

        } else {
            return response()->json(['status' => false, 'value' => []]);
        }
    }

    public function top10($channelId)
    {
        try {
            $top10Views = DB::select('SELECT * FROM posts WHERE channel_id = ? ORDER BY view DESC LIMIT 10', [$channelId]);
            $top10Shares = DB::select('SELECT * FROM posts WHERE channel_id = ? ORDER BY share DESC LIMIT 10', [$channelId]);
            return response()->json(['status' => true, 'value' => ['view' => $top10Views, 'share' => $top10Shares]]);
        } catch (\Throwable $e) {
            return response()->json($e);
        }

    }

    public function getPostsStat($channelId)
    {
        try {
            $views = DB::select('SELECT id, view, created_at FROM posts WHERE channel_id = ? ORDER BY created_at ASC', [$channelId]);
            $shares = DB::select('SELECT id, share, created_at FROM posts WHERE channel_id = ? ORDER BY created_at ASC', [$channelId]);

            return response()->json(['status' => true, 'value' => ['view' => $views, 'share' => $shares]]);
        } catch (\Throwable $e) {
            return response()->json(['status' => false, 'value' => $e]);
        }
    }
}