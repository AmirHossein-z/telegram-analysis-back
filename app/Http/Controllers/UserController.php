<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserInfo()
    {
        return response()->json(auth()->user());
    }

    public function addApiInfo(Request $request)
    {
        $validator = validator::make($request->all(), [
            'apiId' => 'required',
            'apiHash' => 'required',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $apiIdError = $errors->first('apiId');
            $apiHashError = $errors->first('apiHash');

            $message = [
                strlen($apiIdError) > 0 ? ['apiId' => 'api_id معتبر نیست'] : null,
                strlen($apiHashError) > 0 ? ['apiHash' => 'api_hash معتبر نیست'] : null,
            ];

            $message = array_values(array_filter($message));

            return response()->json([
                'status' => 'error',
                'message' => $message
            ], 400);
        }
        $apiId = $request->input('apiId');
        $apiHash = $request->input('apiHash');
        $dateUpdated = date('Y-m-d H:i:s');
        $t = DB::update('UPDATE users set api_id = ?,api_hash = ?,updated_at = ? WHERE id = ?', [$apiId, $apiHash, $dateUpdated, auth()->user()->id]);

        return response()->json(['status' => 'success', 'message' => 'اطلاعات با موفقیت ثبت شد!']);
    }
}