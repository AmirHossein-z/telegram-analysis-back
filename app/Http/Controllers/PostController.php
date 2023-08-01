<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
            $filter = $request->query('filter', null);
            $limit = 10;

            $query = DB::table('posts')->where('channel_id', $channelId);

            if ($filter === 'bestView') {
                $query->orderByDesc('view');
            } else if ($filter === 'leastView') {
                $query->orderBy('view');
            } else if ($filter === 'bestShare') {
                $query->orderByDesc('share');
            } else if ($filter === 'leastShare') {
                $query->orderBy('share');
            }

            $posts = $query->paginate($limit, ['*'], 'page', $page);

            if ($posts->count() > 0) {
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
            $posts = DB::select('SELECT id, view, share, created_at FROM posts WHERE channel_id = ? ORDER BY created_at ASC', [$channelId]);

            $views = [];
            $shares = [];
            $dates = [];

            foreach ($posts as $post) {
                $views[] = ['id' => $post->id, 'view' => $post->view, 'created_at' => $post->created_at];
                $shares[] = ['id' => $post->id, 'share' => $post->share, 'created_at' => $post->created_at];
                $dates[] = ['id' => $post->id, 'created_at' => $post->created_at];
            }

            return response()->json(['status' => true, 'value' => ['view' => $views, 'share' => $shares, 'date' => $dates]]);
        } catch (\Throwable $e) {
            return response()->json(['status' => false, 'value' => $e]);
        }
    }
}