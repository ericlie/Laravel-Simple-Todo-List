<?php

namespace App\Http\Controllers;

use App\Events\UserAccountActivated;
use App\User;
use Illuminate\Http\Request;

class ActivationController extends Controller
{
    public function activate(Request $request, $token)
    {
        $user = User::whereActivationToken($token)->first();
        if (! $user) {
            return response()->json([
                'message' => 'page not found!',
            ], 404);
        }
        $user->activation_token = null;
        $user->save();
        event(new UserAccountActivated($user));
        return response()->json([
            'message' => 'account activated',
            'user' => $user,
        ]);
    }
}
