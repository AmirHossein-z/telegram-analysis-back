<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use danog\MadelineProto\API;

class ChannelController extends Controller
{
    public function getByUserId($userId)
    {
        // $channels = Channel::where('user_id', $userId)->get();

        $channels = DB::select('SELECT * FROM channels WHERE user_id = ?', [$userId]);

        if (count($channels) > 0) {
            return response()->json(['status' => true, 'value' => $channels]);

        } else {
            return response()->json(['status' => false, 'value' => []]);
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