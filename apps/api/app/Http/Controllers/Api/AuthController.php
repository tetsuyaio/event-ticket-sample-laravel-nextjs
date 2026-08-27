<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $auth) {}

    public function signup(RegisterRequest $request): JsonResponse
    {
        $result = $this->auth->signup($request->validated());

        return response()->json([
            'data' => new UserResource($result->user),
            'token' => $result->token,
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->auth->login($request->validated());

        return response()->json([
            'data' => new UserResource($result->user),
            'token' => $result->token,
        ]);
    }

    public function logout(Request $request): Response
    {
        $this->auth->logout($request->user());

        return response()->noContent();
    }

    public function me(Request $request): UserResource
    {
        return new UserResource($request->user());
    }
}
