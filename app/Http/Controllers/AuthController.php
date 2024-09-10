<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\JsonService;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    protected $jsonService;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->jsonService = new JsonService;
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Login",
     *     tags={"Auth"},
     *     description="Login a user and return a token",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"email", "password"},
     *                 @OA\Property(
     *                     property="email",
     *                     type="string",
     *                     format="email",
     *                     description="User's email address",
     *                     example="john.doe@gmail.com"
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     type="string",
     *                     format="password",
     *                     description="User's password",
     *                     example="john@123"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful login",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid credentials")
     *         )
     *     )
     * )
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return $this->jsonService->sendResponse(false, [], 'Invalid credentials.', 400);
            }

            $user = JWTAuth::user();
            $showData = ['id', 'name', 'email', 'role_name'];
            $response = collect(new UserResource($user))->only($showData);
            $response['token'] = $token;

            return $this->jsonService->sendResponse(true, $response, __('User logged in successfully'), 200);
        } catch (\Exception $e) {
            return $this->jsonService->sendResponse(false, [], __('Something went wrong. Please try again'), 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Register",
     *     tags={"Auth"},
     *     description="Register User",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 type="object",
     *                 required={ "name", "email", "password", "password_confirmation", "role_name"},
     *                  @OA\Property(
     *                     property="name",
     *                     type="string",
     *                     format="string",
     *                     description="Name",
     *                     example="John"
     *                 ),
     *                 @OA\Property(
     *                     property="email",
     *                     type="string",
     *                     format="email",
     *                     description="User's email address",
     *                     example="john.doe@gmail.com"
     *                 ),
     *                  @OA\Property(
     *                     property="role_name",
     *                     type="string",
     *                     format="string",
     *                     description="User's role_name",
     *                     example="9876543210"
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     type="string",
     *                     format="password",
     *                     description="User's password",
     *                     example="john@123"
     *                 ),
     *                 @OA\Property(
     *                     property="password_confirmation",
     *                     type="string",
     *                     format="password",
     *                     description="User's password comfirmation",
     *                     example="john@123"
     *                 )
     *             )
     *         )
     *     ),
     *      @OA\Response(
     *         response=201,
     *         description="User registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User registered successfully"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Validation error")
     *         )
     *     )
     * )
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role_name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->jsonService->sendResponse(false, ['data' => $validator->errors()], 'Whoops! Something went wrong.', 400);
        }

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_name' => $request->role_name,
        ]);

        // Return response
        return $this->jsonService->sendResponse(true, [], __('User registered successfully.'), 200);
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Logout",
     *     tags={"Auth"},
     *     description="Logout a user and return a token",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful logout",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User logged out successfully!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid credentials")
     *         )
     *     )
     * )
     */
    public function logout()
    {
        try {
            JWTAuth::parseToken()->invalidate(true);
            Auth::guard('api')->logout();
            return $this->jsonService->sendResponse(true, [], __('User logged out successfully!'), 200);
        } catch (\Exception $e) {
            return $this->jsonService->sendResponse(false, [], __('Whips! Something went wrong.'), 500);
        }
    }
}
