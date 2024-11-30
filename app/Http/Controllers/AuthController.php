<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
//use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;//認証メールを送るのに必要
use Illuminate\Support\Facades\Password; //←パスワードリセットの通知メールを送る処理に必要
use Illuminate\Auth\Events\PasswordReset; //←パスワードリセットDB更新処理に必要
use Illuminate\Support\Facades\Hash; //←パスワードリセットDB更新処理に必要
use Illuminate\Support\Str; //←パスワードリセットDB更新処理に必要

class AuthController extends Controller
{
    //Registre
    public function register(Request $request)
    {
    $validatedData = $request->validate([
    'name' => 'required|string|max:255',
                    'email' => 'required|string|email|max:255|unique:users',
                    'password' => 'required|string|min:8',
    ]);
    
    $user = User::create([
            'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
    ]);

    // 確認メールを送信
    $user->sendEmailVerificationNotification();
    
    $token = $user->createToken('auth_token')->plainTextToken;

    
    return response()->json([
                'access_token' => $token,
                    'token_type' => 'Bearer',
    ]);
    }


    //Verify Regster
    public function verify($user_id, Request $request){

        //送信されてきたリクエストが有効な著名を持っているかを検査
        if(!$request->hasValidSignature()){
            //特定のページに(例えばホームなど)に戻す。自由にURLを適時カスマイズする。
            return redirect()->to('/');
        }

        $user = User::findOrFail($user_id);

        if(!$user->hasVerifiedEmail()){
            //markEmailAsVerified()でUserテーブルの"email_verifiyed_at"に日付を保存してる？
            $user->markEmailAsVerified();
        }

        //メール認証後に特定のページに移動。自由にURLを適時カスマイズする。
        return redirect()->to('/');
    }

    //Verify Mail Resend
    public function resend(Request $request){
        $user = User::where('email','=',$request->email)->first();
        $user->sendEmailVerificationNotification();
        return response()->json(['message' => 'メール承認のリンクを再送しました']);
    }


    //Login
    public function login(Request $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
        return response()->json([
        'message' => 'Invalid login details'
                ], 401);
            }
        
        $user = User::where('email', $request['email'])->firstOrFail();
        
        $token = $user->createToken('auth_token')->plainTextToken;
        
        return response()->json([
                'access_token' => $token,
                'token_type' => 'Bearer',
        ]);
    }


    //me(check Auth)
    public function me(Request $request)
    {
        return $request->user();
    }

    //send reset password mail
    public function sendemail(Request $request){

        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            // リセットリンク送信成功
            return response()->json([
                'status' => 'success',
                'message' => __($status),
            ], 200); 
        } else {
            // リセットリンク送信失敗
            return response()->json([
                'status' => 'error',
                'message' => __($status),
            ], 400); 
        }

    }

    //reset password action
    public function passwordreset(Request $request){
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));
    
                $user->save();
    
                event(new PasswordReset($user));
            }
        );
    
        if($status === Password::PASSWORD_RESET){
            return response()->json([
                'status' => true,
                'message' => __($status),
            ], 200); 
        }else{
            return response()->json([
                'status' => false,
                'message' => __($status),
            ], 400); 
        }
    }

}
