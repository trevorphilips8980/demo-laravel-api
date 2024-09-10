<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\JsonService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Stevebauman\Location\Facades\Location;


class UserController extends Controller
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
     *     path="/api/profile-update",
     *     summary="Profile Update",
     *     tags={"User"},
     *     description="Profile Update",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"name"},
     *                  @OA\Property(
     *                     property="name",
     *                     type="string",
     *                     format="string",
     *                     description="Name",
     *                     example="John"
     *                 ),
     *                  @OA\Property(
     *                     property="role_name",
     *                     type="string",
     *                     format="string",
     *                     description="User's role",
     *                 )
     *             )
     *         )
     *     ),
     *      @OA\Response(
     *         response=201,
     *         description="User profile has been updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User profile has been updated successfully"),
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
    public function profileUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'role_name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->jsonService->sendResponse(false, ['data' => $validator->errors()], 'Whoops! Something went wrong.', 400);
        }

        $user = Auth::guard('api')->user();

        $user->update([
            'name' => $request->name,
            'role_name' => $request->role_name,
        ]);

        // Return response
        return $this->jsonService->sendResponse(true, [], __('Profile has been updated successfully.'), 200);
    }

    /**
     * @OA\Post(
     *     path="/api/get-location",
     *     summary="get location",
     *     tags={"User"},
     *     description="Get user location",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="User's location fetch successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User location fetched!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid request")
     *         )
     *     )
     * )
     */
    public function getLocation(Request $request)
    {
        try {
            $location = Location::get($request->ip());
            $latitude = $location->latitude;
            $longitude = $location->longitude;

            \Log::info('latitude: ' . $latitude . ' longitude: ' . $longitude);
            // $user = Auth::guard('api')->user();
            // $showData = ['first_name', 'last_name', 'full_name', 'email', 'phone_number', 'status'];
            // $userData = collect(new UserResource($user))->only($showData);
            // $response = [
            //     'user' => $userData,
            // ];

            return $this->jsonService->sendResponse(true, $response, __('User location fetched!'), 200);
        } catch (\Exception $e) {
            return $this->jsonService->sendResponse(false, [], __('Whoops! Something went wrong.'), 500);
        }
    }
}
