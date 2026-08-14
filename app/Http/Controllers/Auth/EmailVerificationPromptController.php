<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailVerificationPromptController extends Controller
{
    /**
     * Return the email verification status for the authenticated user.
     */
    public function __invoke(Request $request): JsonResponse
    {
        return response()->json([
            'user_id' => $request->user()->id,
            'email' => $request->user()->email,
            'verified' => (bool) $request->user()->hasVerifiedEmail(),
        ]);
    }
}
