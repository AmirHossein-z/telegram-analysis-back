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

            // $posts = DB::table('posts') ->where('channel_id', $channelId) ->paginate(10, ['*'], 'page', $page);

            if (count($posts) > 0) {
                return response()->json(['status' => true, 'value' => $posts]);

            } else {
                return response()->json(['status' => false, 'value' => []]);
            }
        } catch (\Throwable $e) {
            return response()->json($e);
        }

    }
}