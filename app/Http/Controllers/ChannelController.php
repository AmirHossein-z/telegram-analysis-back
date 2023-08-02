<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use danog\MadelineProto\API;

class ChannelController extends Controller
{
    // public function getByUserId($userId, Request $request, $filter = null)
    // {
    //     try {
    //         $page = $request->query('page', 1);
    //         $limit = 10;

    //         $query = DB::table('channels')->where('user_id', $userId);

    //         if ($filter === 'bestView') {
    //             $query->orderByDesc('view');
    //         } else if ($filter === 'leastView') {
    //             $query->orderBy('view');
    //         } else if ($filter === 'bestShare') {
    //             $query->orderByDesc('share');
    //         } else if ($filter === 'leastShare') {
    //             $query->orderBy('share');
    //         }

    //         $channels = $query->paginate($limit, ['*'], 'page', $page);

    //         if ($channels->count() > 0) {
    //             return response()->json(['status' => true, 'value' => $channels]);
    //         } else {
    //             return response()->json(['status' => false, 'value' => []]);
    //         }
    //     } catch (\Throwable $e) {
    //         return response()->json($e);
    //     }
    // }
    // public function getByUserId($userId, Request $request, $filter = null)
    // {
    //     try {
    //         $page = $request->query('page', 1);
    //         $limit = 10;
    //         $offset = ($page - 1) * $limit;

    //         $query = DB::table('channels')->where('user_id', $userId);

    //         if ($filter === 'bestView') {
    //             $query->orderByDesc('view');
    //         } else if ($filter === 'leastView') {
    //             $query->orderBy('view');
    //         } else if ($filter === 'bestShare') {
    //             $query->orderByDesc('share');
    //         } else if ($filter === 'leastShare') {
    //             $query->orderBy('share');
    //         }

    //         $channels = $query->offset($offset)->limit($limit)->get();

    //         if (count($channels) > 0) {
    //             return response()->json(['status' => true, 'value' => $channels]);
    //         } else {
    //             return response()->json(['status' => false, 'value' => []]);
    //         }
    //     } catch (\Throwable $e) {
    //         return response()->json($e);
    //     }
    // }

    public function getByUserId($userId, Request $request)
    {
        try {
            $page = $request->query('page', 1);
            $filter = $request->query('filter', null);
            $tagName = $request->query('tagName', null);
            $limit = 10;

            $query = DB::table('channels')->where('user_id', $userId);

            if ($filter === 'bestView') {
                $query->orderByDesc('view');
            } else if ($filter === 'leastView') {
                $query->orderBy('view');
            } else if ($filter === 'bestShare') {
                $query->orderByDesc('share');
            } else if ($filter === 'leastShare') {
                $query->orderBy('share');
            }

            if (!empty($tagName)) {
                $query->where('tags', 'like', '%' . '#' . $tagName . '%');
            }

            $channels = $query->paginate($limit, ['*'], 'page', $page);

            if ($channels->count() > 0) {
                return response()->json(['status' => true, 'value' => $channels]);
            } else {
                return response()->json(['status' => false, 'value' => []]);
            }
        } catch (\Throwable $e) {
            return response()->json($e);
        }
    }
    // public function getByUserId($userId, Request $request)
    // {
    //     try {
    //         $page = $request->query('page', 1);

    //         $channels = DB::table('channels')->where('user_id', $userId)->paginate(10);

    //         if (count($channels) > 0) {
    //             return response()->json(['status' => true, 'value' => $channels]);

    //         } else {
    //             return response()->json(['status' => false, 'value' => []]);
    //         }
    //     } catch (\Throwable $e) {
    //         return response()->json($e);
    //     }
    // }

    public function getChannel($channelId)
    {

        $channelInfo = DB::select('SELECT * FROM channels WHERE id = ? LIMIT 0,1', [$channelId]);

        if (count($channelInfo) > 0) {
            return response()->json(['status' => true, 'value' => $channelInfo]);

        } else {
            return response()->json(['status' => false, 'value' => []]);
        }
    }

    public function getAllTags($userId)
    {

        $tags = DB::select('SELECT id AS channelId,tags FROM channels WHERE user_id = ?', [$userId]);

        if (count($tags) > 0) {
            return response()->json(['status' => true, 'value' => $tags]);

        } else {
            return response()->json(['status' => false, 'value' => []]);
        }
    }

}