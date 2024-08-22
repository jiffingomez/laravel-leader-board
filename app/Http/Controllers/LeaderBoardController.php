<?php

namespace App\Http\Controllers;

use App\Jobs\QRCodeGenerator;
use App\Models\LeaderBoard;
use App\Models\WinnerBoard;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class LeaderBoardController extends Controller
{
    /**
     * This function is used to return leaderboard
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        $leaderboard = LeaderBoard::all('id', 'name', 'age', 'points', 'address')->sortByDesc('points');
        return response()->json($leaderboard);
    }

    /**
     * This function is used to add new leaders
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
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
     * This function is used to get a leader details
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $id): \Illuminate\Http\JsonResponse
    {
        $leader = LeaderBoard::find($id);

        if (is_null($leader)) {
            return response()->json(["success" => false, "message" => "Leader board not found."]);
        } else {
            return response()->json(["success" => true, "data" => $leader, "message" => "Leader board retrieved successfully."]);
        }
    }

    /**
     * This function is used to update points
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
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
            return response()->json(["success" => true, "message" => "Leader board updated successfully."]);
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
//            $leader->winner_boards(->delete();
            $leader->delete();
            return response()->json(["success" => true, "message" => "Leader deleted successfully."]);
        }
        return response()->json(["success" => false, "message" => "Leader board not found."]);
    }

    /**
     * This function is reset points to 0
     * @return \Illuminate\Http\JsonResponse
     */
    public function reset_points(): \Illuminate\Http\JsonResponse
    {
        LeaderBoard::whereNull('deleted_at')->update(['points' => 0]);
        return response()->json(["success" => true, "message" => "Points reset successfully."]);
    }

    /**
     * This function is used to return grouped data
     * @return \Illuminate\Http\JsonResponse
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
