<?php

namespace App\Http\Controllers;

use App\Jobs\QRCodeGenerator;
use App\Models\LeaderBoard;
use App\Models\WinnerBoard;
use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Info(title="LeaderBoard APIs", version="0.1")
 */

class LeaderBoardController extends Controller
{
   /**
     * @OA\Get(
     *     path="/api/leaderboard",
     *     tags={"Leaderboard"},
     *     summary="Get a list of leaders sorted by points in descending",
     *     @OA\Response(response="200", description="Leaders Fetched Successfully")
     * )
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        $leaderboard = LeaderBoard::all('id', 'name', 'age', 'points', 'address')->sortByDesc('points');
        $collection = collect($leaderboard->values()->all());
        return response()->json($collection);
    }

    /**
     * @OA\Post(
     *     path="/api/leader/create",
     *     tags={"Leaderboard"},
     *     summary="To create a new user",
     *  @OA\RequestBody(required=true,
     *     @OA\MediaType(mediaType="application/json",
     *        @OA\Schema(required={"name", "age", "address"},
     *           @OA\Property(property="name",description="name",type="string", default="Jiffin"),
     *           @OA\Property(property="age",description="age",type="integer", default="33"),
     *           @OA\Property(property="address",description="address",type="string"),
     *          ),),),
     *  @OA\Response(response=200, description="User point updated successfully.",
     *     @OA\MediaType(mediaType="application/json"),),
     * ))
     * Update Points
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $leader = new LeaderBoard();
        $leader->name = $request->input('name');
        $leader->age = $request->input('age');
        $leader->points = 0;
        $leader->address = $request->input('address');
        $leader->save();

        # Publishing to queue
        $filename = str_replace(" ", "_", $leader->name);
        $qr_data = ["address" => $leader->address,"filename" => $filename];
        QRCodeGenerator::dispatch($qr_data);

        return response()->json(["success" => true, "data" => $leader, "message" => "Leader created."]);
    }


    /**
     * @OA\Get(
     *     path="/api/leader/show/{id}",
     *     tags={"Leaderboard"},
     *     summary="To get the details of a leader",
     *     @OA\Parameter(name="id", in="path", description="leader id", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response="200", description="Leader Details Fetched Successfully")
     * )
     */
    public function show(int $id): \Illuminate\Http\JsonResponse
    {
        $leader = LeaderBoard::find($id);

        if (is_null($leader)) {
            return response()->json(["success" => false, "message" => "Leader not found."]);
        } else {
            return response()->json(["success" => true, "data" => $leader, "message" => "Leader retrieved successfully."]);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/leader/point",
     *     tags={"Leaderboard"},
     *     summary="To update a point of user (increment/decrement points)",
     *  @OA\RequestBody(required=true,
     *     @OA\MediaType(mediaType="application/json",
     *        @OA\Schema(required={"id", "mode"},
     *           @OA\Property(property="id",description="leader-board-id",type="integer"),
     *           @OA\Property(property="mode",description="increment or decrement",type="string",default="increment"),
     *          ),),),
     *  @OA\Response(response=200, description="User point updated successfully.",
     *     @OA\MediaType(mediaType="application/json"),),
     * ))
     * Update Points
     * @param Request $request
     * @return JsonResponse
     */
    public function point_update(Request $request): \Illuminate\Http\JsonResponse
    {

        $id = $request->input('id');
        if (LeaderBoard::where('id', $id)->exists())
        {
            $leader = LeaderBoard::find($id);
            if ($request->get('mode') == 'increment'){
                $leader->points = $leader->points + 1;
            } else {
                if ($leader->points > 0) {
                    $leader->points = $leader->points - 1;
                } else {
                    $leader->points = 0;
                }
            }
            $leader->save();
            return response()->json(["success" => true, "message" => "User point updated successfully."]);
        }
        return response()->json(["success" => false, "message" => "Leader not found."]);
    }

    /**
     * This function is used to delete a leader
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id): \Illuminate\Http\JsonResponse
    {
//        $id = $request->input('id');
        if (LeaderBoard::where('id', $id)->exists()) {
            $leader = LeaderBoard::find($id);
            $leader->delete();
            return response()->json(["success" => true, "message" => "Leader deleted successfully."]);
        }
        return response()->json(["success" => false, "message" => "Leader board not found."]);
    }

    /**
     * @OA\Get(
     *     path="/api/leader/points/reset",
     *     tags={"Leaderboard"},
     *     summary="To reset all users points to zero",
     *     @OA\Response(response="200", description="Points reset successfully.")
     * )
     */
    public function reset_points(): \Illuminate\Http\JsonResponse
    {
        LeaderBoard::whereNull('deleted_at')->update(['points' => 0]);
        return response()->json(["success" => true, "message" => "Points reset successfully."]);
    }

    /**
     * @OA\Get(
     *     path="/api/leader/scores",
     *     tags={"Leaderboard"},
     *     summary="To group by points and show the average age of grouped users",
     *     @OA\Response(response="200", description="Group By Scores retrieved Successfully")
     * )
     */
    public function grouped_by_scores(): \Illuminate\Http\JsonResponse
    {
        $groupedUsers = LeaderBoard::selectRaw('points, GROUP_CONCAT(name) as names, AVG(age) as average_age')
            ->groupBy('points')
            ->get()
            ->mapWithKeys(function ($user) {
                return [$user->points => [
                    'names' => explode(',', $user->names),
                    'average_age' => $user->average_age,
                ]];
            });
        return response()->json($groupedUsers);

    }

    /**
     * This function is used to generate QR code
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function generate_qr_code(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->get('data');
        $filename = $request->get('filename');;
        $size = "100x100";
        $client = new Client();

        try {
            $response = $client->get("http://api.qrserver.com/v1/create-qr-code/?data=" . urlencode($data) . "&size=" . $size);

            if ($response->getStatusCode() === 200) {
                $imageData = $response->getBody()->getContents();

                // Ensure storage directory exists and has write permissions
                $storagePath = public_path('qr_codes'); // Adjust as needed
                if (!is_dir($storagePath) || !is_writable($storagePath)) {
                    return response()->json(["success" => false,
                        "message" => "QR code storage directory not writable: " . $storagePath]);
                }

                // Generate a unique filename with extension based on response content type (if available)
                $contentType = $response->getHeader('Content-Type')[0];
                $extension = in_array($contentType, ['image/jpeg', 'image/png']) ? explode('/', $contentType)[1] : 'png'; // Default to png
                $uniqueFilename = uniqid('qr_') . '.' . $extension;

                $imagePath = $storagePath . '/' . $uniqueFilename;

                file_put_contents($imagePath, $imageData);

                return response()->json(["success" => true, "message" => "QR Code Generated Successfully."]);
            } else {
                return response()->json(["success" => false,
                    "message" => "QR Code generation failed with status code:" . $response->getStatusCode()]);
            }
        } catch (Exception $e) {
            report($e); // Log the error for debugging
            return response()->json(["success" => false, "message" => $e->getMessage()]);
        }
    }

    /**
     * This function is used by job to generate qr code
     * @param $data
     * @param $filename
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function qr_code($data, $filename): \Illuminate\Http\JsonResponse
    {

        $size = "100x100";
        $client = new Client();

        try {
            $response = $client->get("http://api.qrserver.com/v1/create-qr-code/?data=" . urlencode($data) . "&size=" . $size);

            if ($response->getStatusCode() === 200) {
                $imageData = $response->getBody()->getContents();

                // Ensure storage directory exists and has write permissions
                $storagePath = public_path('qr_codes'); // Adjust as needed
                if (!is_dir($storagePath) || !is_writable($storagePath)) {
                    return response()->json(["success" => false,
                        "message" => "QR code storage directory not writable: ".$storagePath]);
                }

                // Generate a unique filename with extension based on response content type (if available)
                $contentType = $response->getHeader('Content-Type')[0];
                $extension = in_array($contentType, ['image/jpeg', 'image/png']) ? explode('/', $contentType)[1] : 'png'; // Default to png
                $uniqueFilename = uniqid('qr_'.$filename.'_') . '.' . $extension;

                $imagePath = $storagePath . '/' . $uniqueFilename;

                file_put_contents($imagePath, $imageData);

                return response()->json(["success" => true, "message" => "QR Code Generated Successfully."]);
            } else {
                return response()->json(["success" => false,
                    "message" => "QR Code generation failed with status code:".$response->getStatusCode()]);
            }
        } catch (Exception $e) {
            report($e); // Log the error for debugging
            return response()->json(["success" => false, "message" => $e->getMessage()]);
        }

    }

    /**
     * This function inserts winner
     * @return \Illuminate\Http\JsonResponse
     */
    public function winner(): \Illuminate\Http\JsonResponse
    {
        $leader = LeaderBoard::orderBy('points', 'desc')->first();

        // Check if multiple users have the same highest points
        $count = LeaderBoard::where('points', $leader->points)->count();

        if ($count > 1) {
            return response()->json(["success" => false, "message" => "Multiple leaders with the same highest points."]);
        }

        # Inserting the winner to the winner board
        $winner = new WinnerBoard();
        $winner->leaderboard_id = $leader->id;
        $winner->highest_score = $leader->points;
        $winner->save();

        return response()->json(["success" => true, "message" => "Successfully inserted Winner", "data" => $leader]);
    }

}
