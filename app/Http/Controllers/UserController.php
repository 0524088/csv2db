<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Validator;
use Response;
use App\Models\User;
use Hash;
use Laravel\Passport\HasApiTokens;
use Illuminate\Support\Str;
use Session;


class UserController extends Controller
{
    
    function register(Request $request) {
        try {
            $account = $request->input('account');
            $password = $request->input('password');
    
            // 驗證是否有輸入值
            $validator = Validator::make($request->all(), [
                'account' => 'required|string',
                'password' => 'required|string',
            ]);
            if( $validator->fails() ) return response(['status' => 'error', 'message' => '請輸入帳號密碼'], 400);
    
            // 判斷使用者名稱是否重複
            $user = User::firstOrNew(['account' =>  $account]);
            if($user->exists === true) return response(['status' => 'error', 'message' => '帳號已存在！'], 400);
    
            $token = Str::random(80);
            $user = User::Create([
                'account' =>  $account,
                'password' => Hash::make($password),
                'token' => $token,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
    
            return response(['status' => 'success', 'user' => $user, 'token' => $token], 200);
        }
        catch(\Exception $e) {
            $error = $e->getMessage();
            return response(['status' => 'error', 'message' => $error], 200);
        }
    }

    function login(Request $request) {
        try {
            $account = $request->input('account');
            $password = $request->input('password');
    
            // 驗證是否有輸入值
            $validator = Validator::make($request->all(), [
                'account' => 'required|string',
                'password' => 'required|string',
            ]);
            if( $validator->fails() ) return response(['status' => 'error', 'message' => '請輸入帳號密碼'], 400);
    
    
            $user = User::where('account', $account)->first();
            // 判斷帳號是否存在
            if($user === null) return response(['status' => 'error', 'message' => '帳號不存在'], 400);
    
            // 判斷密碼是否匹配
            if(!Hash::check($password, $user->password)) return response(['status' => 'error', 'message' => '帳號或密碼錯誤！'], 400);
    
            $token = Str::random(80);
            $user->update([
                'token' => $token,
                'updated_at' => Carbon::now(),
            ]);
    
            Session::put('token', $token);
            return response(['status' => 'success', 'message' => '登入成功！'], 200);
        }
        catch(\Exception $e) {
            $error = $e->getMessage();
            return response(['status' => 'error', 'message' => $error], 400);
        }
    }

    public function logout() {
        try{
            // 尚未登入
            if(!Session::has('token')) {
                return response(['status' => 'error', 'message' => '尚未登入！'], 400);
            }

            // 異地登入
            $token = Session::get('token');
            $user = User::where('token', $token)->first();
            if($user === null) return response(['status' => 'error', 'message' => '用戶已於其他地方登入'], 400);
    
            // 登出流程
            $user = User::where('token', $user->token)->update(['token' => null, 'updated_at' => Carbon::now()]);
            Session::flush();
            return response(['status' => 'success', 'message' => '登出成功！'], 200);
        }
        catch(\Exception $e) {
            $error = $e->getMessage();
            return response(['status' => 'error', 'message' => $error], 400);
        }

    }
}
