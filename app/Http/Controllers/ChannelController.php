<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChannelController extends Controller
{
    public function getByUserId($userId)
    {
        // $channels = Channel::where('user_id', $userId)->get();
        $test = DB::select('SELECT * FROM channels WHERE user_id = ?', [$userId]);
        if (count($test) > 0) {
            return response()->json(['status' => true, 'values' => $test]);
        } else {
            return response()->json(['status' => false, 'values' => []]);
        }
    }

}