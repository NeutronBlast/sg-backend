<?php

namespace App\Http\Controllers;

use App\Models\Participation;
use App\Models\User;
use Carbon\Carbon;
use Dotenv\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $participants = DB::table('participations')
            ->join('users', 'users.id', '=', 'participations.user_id')
            ->select('users.id', 'users.email', 'users.role', DB::raw('CONCAT(first_name, " ", last_name) AS full_name'),
                'users.date_of_birth', 'participations.status')
            ->get();

        return response()->json($participants);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $v = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'first_name' => [
                'min:2',
                'max:100'
            ],
            'last_name' => [
                'min:2',
                'max:100'
            ],
            'email' => [
                'max:150',
                'regex:/^\S+@\S+\.\S+$/',
                'unique:users'
            ],
            'password' => [
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/'
            ]
        ]);

        if ($v->fails()){
            return response()->json([
                'status' => 'error',
                'errors' => $v->errors()
            ], 422);
        }

        $user = new User();
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->date_of_birth = $request->date_of_birth;
        $user->role = 'Participant';

        $user->save();

        $now = Carbon::now()->toDateTimeString();

        $participation = new Participation();
        $participation->status = 'Active';
        $participation->participation_date = $now;
        $participation->user_id = $user->id;

        $participation->save();

        return response()->json([
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'date_of_birth' => $user->date_of_birth,
            'participation_date' => $participation->participation_date,
            'status' => $participation->status
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $participant = DB::table('participations')
            ->join('users', 'users.id', '=', 'participations.user_id')
            ->where('users.id', '=', $id)
            ->select('users.id', 'users.email', 'users.role', DB::raw('CONCAT(first_name, " ", last_name) AS full_name'),
                'participations.status')
            ->first();

        return response()->json($participant);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $user = User::find($id);
        $participation = Participation::where('user_id', $id)->first();

        $v = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'first_name' => [
                'min:2',
                'max:100'
            ],
            'last_name' => [
                'min:2',
                'max:100'
            ],
            'email' => [
                'regex:/^\S+@\S+\.\S+$/',
                'unique:users'
            ],
            'password' => [
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/'
            ]
        ]);

        if ($v->fails()){
            return response()->json([
                'status' => 'error',
                'errors' => $v->errors()
            ], 422);
        }

        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->date_of_birth = $request->date_of_birth;
        $user->save();

        return response()->json([
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'date_of_birth' => $user->date_of_birth,
            'participation_date' => $participation->participation_date,
            'status' => $participation->status
        ], 200);
    }

    public function changeStatus(Request $request, int $id): JsonResponse
    {
        $user = User::find($id);
        $participation = Participation::where('user_id', $id)->first();

        $participation->status = $request->status;
        $participation->save();

        return response()->json([
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'date_of_birth' => $user->date_of_birth,
            'participation_date' => $participation->participation_date,
            'status' => $participation->status
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        Log::alert($id);
        $user = User::find($id);
        if (!$user){
            return response()->json([
                'status' => 'error',
                'message' => 'user does not exist'
            ], 422);
        }

        $user->delete();
        return response()->json([
            'status' => 'success'
        ], 200);
    }
}
