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
}