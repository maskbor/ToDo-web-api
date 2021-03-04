<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;


class AuthController extends Controller
{
    public function signup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'c_password' => 'same:password'
        ], [
            'name.required' => 'Имя не может быть пустым.',
            'email.required' => 'Email не может быть пустым.',
            'email.unique' => 'Пользователь с таким Email уже существует.',
            'email.email' => 'Введите правильный Email.',
            'password.required' => 'Пароль не может быть пустым.',
            'password.min' => 'Пароль должен содержать минимум 8 символов.',
            'c_password.same' => 'Пароли не совпадают.',
        ]);

        if ($validator->fails()) {
            return response()->json(
                ["list" => $validator->errors()->all()]
                , 422);
        }

        $user = new User([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);
        $user->save();
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;
        /*if ($request->remember_me)
            $token->expires_at = Carbon::now()->addWeeks(1);*/
        $token->save();

        return response()->json([
            'name' => $user->name,
            'email' => $user->email,
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse(
                $tokenResult->token->expires_at
            )->toDateTimeString(),
            ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string'
        ]);
        $credentials = request(['email', 'password']);
        if(!Auth::attempt($credentials))
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        $user = $request->user();
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;
        /*if ($request->remember_me)
            $token->expires_at = Carbon::now()->addWeeks(1);*/
        $token->save();

        return response()->json([
            'name' => $user->name,
            'email' => $user->email,
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse(
                $tokenResult->token->expires_at
            )->toDateTimeString(),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json([
            'message' => 'Successfully logged out'
        ]. 200);
    }
    public function user(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'name' => $user->name,
            'email' => $user->email
        ]);
    }


}
