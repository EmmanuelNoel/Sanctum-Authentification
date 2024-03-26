<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\User;
use App\Enums\TokenAbility;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // $token = $user->createToken('auth_token')->plainTextToken;
        $accessToken = $user->createToken('access_token', [TokenAbility::ACCESS_API->value], Carbon::now()->addMinutes(config('sanctum.ac_expiration')));
        $refreshToken = $user->createToken('refresh_token', [TokenAbility::ISSUE_ACCESS_TOKEN->value], Carbon::now()->addMinutes(config('sanctum.rt_expiration')));


        return $this->sendResponse($user, 'Successfully Register', 200, $accessToken, $refreshToken);
    }

    public function login(Request $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return $this->sendError('Unauthorized', [], 401);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        // $token = $user->createToken('auth_token')->plainTextToken;
        $validRefreshToken = $user->tokens()
            ->where('name', 'refresh_token')
            ->where('expires_at', '>', Carbon::now())
            ->exists();

          //  var_dump($validRefreshToken);

        if (!$validRefreshToken) {

            $user->tokens()->delete();

            $accessToken = $user->createToken('access_token', [TokenAbility::ACCESS_API->value], Carbon::now()->addMinutes(config('sanctum.ac_expiration')));
            $refreshToken = $user->createToken('refresh_token', [TokenAbility::ISSUE_ACCESS_TOKEN->value], Carbon::now()->addMinutes(config('sanctum.rt_expiration')));

            return $this->sendResponse(null, 'Successfully Login', 200, $accessToken, $refreshToken);
        }

        $accessToken = $user->createToken('access_token', [TokenAbility::ACCESS_API->value], Carbon::now()->addMinutes(config('sanctum.ac_expiration')));
        return $this->sendResponse(null, 'Successfully Login', 200, $accessToken);
    }

    public function logout()
    {
        Auth::user()
            ->tokens()
            ->delete();
        return $this->sendResponse(null, 'Successfully Logout', 200, null);
    }

    public function refreshToken(Request $request)
    {
        $accessToken = $request->user()->createToken('access_token', [TokenAbility::ACCESS_API->value], Carbon::now()->addMinutes(config('sanctum.ac_expiration')));
        return response(['message' => "Token généré", 'token' => $accessToken->plainTextToken]);
        // return $this->sendResponse('Successfully Register', 200, $accessToken);
    }
}
