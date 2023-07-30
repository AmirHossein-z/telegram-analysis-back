<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use danog\MadelineProto\API;

class ChannelController extends Controller
{
    public function getByUserId($userId, Request $request)
    {
        // $channels = DB::select('SELECT * FROM channels WHERE user_id = ?', [$userId]);

        // if (count($channels) > 0) {
        //     return response()->json(['status' => true, 'value' => $channels]);

        // } else {
        //     return response()->json(['status' => false, 'value' => []]);
        // }
        try {
            $page = $request->query('page', 1);

            $channels = DB::table('channels')->where('user_id', $userId)->paginate(10);

            if (count($channels) > 0) {
                return response()->json(['status' => true, 'value' => $channels]);

            } else {
                return response()->json(['status' => false, 'value' => []]);
            }
        } catch (\Throwable $e) {
            return response()->json($e);
        }
    }

    public function getChannel($channelId)
    {

        $channelInfo = DB::select('SELECT * FROM channels WHERE id = ? LIMIT 0,1', [$channelId]);

        if (count($channelInfo) > 0) {
            return response()->json(['status' => true, 'value' => $channelInfo]);

        } else {
            return response()->json(['status' => false, 'value' => []]);
        }
    }

}