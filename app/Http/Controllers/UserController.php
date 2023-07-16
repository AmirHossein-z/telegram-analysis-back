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

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    public function addApiInfo(Request $request)
    {
        $validator = validator::make($request->all(), [
            'apiId' => 'required',
            'apiHash' => 'required',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $emailError = $errors->first('apiId');
            $passwordError = $errors->first('apiHash');

            $message = [
                strlen($emailError) > 0 ? ['apiId' => 'api_id معتبر نیست'] : null,
                strlen($passwordError) > 0 ? ['apiHash' => 'api_hash معتبر نیست'] : null,
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
        $t = DB::update('UPDATE users set api_id = ?,api_hash = ?,updated_at = ? WHERE id = ?', [$apiHash, $apiId, $dateUpdated, auth()->user()->id]);

        return response()->json(['status' => 'success', 'message' => 'اطلاعات با موفقیت ثبت شد!']);
    }

    public function test()
    {
        return 'test';
    }
}