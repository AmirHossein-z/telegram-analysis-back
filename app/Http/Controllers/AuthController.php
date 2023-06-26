<?php

namespace App\Http\Controllers;

// use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->only(['email', 'password']);

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['status' => 'error', 'message' => 'کاربری با این مشخصات در سامانه وجود ندارد'], 401);
        }

        return $this->respondWithToken($token);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8'
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $emailError = $errors->first('email');
            $passwordError = $errors->first('password');

            return response()->json([
                'status' => 'error',
                'message' => [
                    // 'email' => $emailError,
                    // 'password' => $passwordError
                    'email' => 'ایمیل معتبر نیست',
                    'password' => 'پسورد معتبر نیست',
                ]
            ], 400);
        }

        // $email = $request->input('email');
        // $password = Hash::make($request->input('password'));
        // $date_created = date('Y-m-d H:i:s');
        // $date_updated = date('Y-m-d H:i:s');
        // DB::insert('INSERT INTO users(email,password,date_created,date_updated) VALUES (?,?,?,?)', [$email, $password, $date_created, $date_updated]);

        $email = $request->input('email');
        $password = Hash::make($request->input('password'));
        $date_created = date('Y-m-d H:i:s');
        $date_updated = date('Y-m-d H:i:s');
        $user = User::create([
            'email' => $email,
            'password' => $password,
            'date_created' => $date_created,
            'date_updated' => $date_updated
        ]);

        return response()->json(['status' => 'success', 'message' => 'با موفقیت ثبت نام کردید']);
    }
    // public function register(Request $request)
    // {
    //     $validated = $this->validate($request, [
    //         'email' => 'required|email',
    //         'password' => 'required'
    //     ]);
    //     $email = $request->input('email');
    //     $password = Hash::make($request->input('password'));
    //     DB::insert('INSERT INTO users(email,password) VALUES (?,?)', [$email, $password]);
    //     return response()->json(['status' => 'success', 'message' => 'با موفقیت ثبت نام کردید']);
    // }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    // public function me()
    // {
    //     return response()->json(auth()->user());
    // }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();
        return response()->json(['status' => 'success', 'message' => 'شما از سایت خارج شدید']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            // 'expires_in' => auth()->factory()->getTTL() * 60
            'expires_in' => 10
        ]);
    }
}