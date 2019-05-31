<?php

namespace App\Http\Controllers;

use App\User;
use App\Repositories\UserRepository;
use App\Http\Requests\{
    UserLoginRequest,
    UserCreateRequest
};

class UserController extends Controller
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Handles Registration Request
     *
     * @param UserCreateRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(UserCreateRequest $request)
    {
        $user = $this->userRepository->create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => bcrypt($request->get('password'))
        ]);

        $token = $user->createToken('CashMachineApi')->accessToken;

        return response()->json([
            'token' => $token
        ], 200);
    }

    /**
     * Handles Login Request
     *
     * @param UserLoginRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(UserLoginRequest $request)
    {
        $credentials = [
            'email' => $request->get('email'),
            'password' => $request->get('password')
        ];

        if (auth()->attempt($credentials)) {
            $token = auth()->user()->createToken('CashMachineApi')->accessToken;
            return response()->json([
                'token' => $token
            ], 200);
        } else {
            return response()->json([
                'error' => 'Unauthorized'
            ], 401);
        }
    }

    /**
     * Returns Authenticated User Details
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function details()
    {
        return response()->json([
            'user' => auth()->user()
        ], 200);
    }
}
