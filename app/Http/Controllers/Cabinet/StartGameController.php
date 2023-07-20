<?php

namespace App\Http\Controllers\Cabinet;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

use Illuminate\Http\Request;

use App\Models\Game;
use App\Models\GameSession;
use App\Services\TomHorn\Helper;

use App\Jobs\StrikeJob;

use App\Services\TomHorn\Client as TomHornClient;
use App\Services\Mancala\Client as MancalaClient;

class StartGameController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:games,id',
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    'status' => 'error',
                    'error' => $validator->errors()->toJson()
                ], 400
            );
        }

        $game = Game::find($validator->validated()["id"]);
        $user = $request->user();

        if ($game->type === config('enums.game_types')['tomhorn']) {
            return $this->tomhorn_start($user, $game);
        } else if ($game->type === config('enums.game_types')['mancala']) {
            return $this->mancala_start($user, $game);
        }

        return response()->json(
            [
                'status' => 'error',
                'error' => 'Unknown game'
            ], 500
        );
    }

    public function tomhorn_start($user, $game)
    {
        $client = app()->make(TomHornClient::class);

        $game_session = GameSession
            ::whereNull('end')
            ->where(
                'user_id', '=', $user->id
            )
            ->first();
        
        if (!$game_session) {
            $sessionID = $client->createSession($user->identity);
            $game_session = GameSession::create([
                'user_id'    => $user->id,
                'type'       => config('enums.game_types')['tomhorn'],
                'session_id' => strval($sessionID),
            ]);
        }
        $sessionID = intval($game_session->session_id);

        $game_info = $client->getModuleInfo($sessionID, $game->info);

        if (!$game_info) {
            return response()->json([
                'status'  => 'error',
                'message' => "Error occured while trying to create game page, try again later"
            ]);
        }

        dispatch(new StrikeJob([
            'user' => $user
        ]));
        
        return response()->json([
            'status'  => 'success',
            'type'    => 'TomHorn',
            //'html' => Helper::preparePage($game_info)
            'params'  => $game_info
        ]);
    }

    public function mancala_start($user, $game)
    {
        $client = app()->make(MancalaClient::class);
        $res = $client->GetToken(
            intval($game->info),
            strval($user->id),
            $user->currency,
            false
        );
        $res['status'] = 'success';

        $token = $res['token'];
        $game_session = GameSession::create([
            'user_id'    => $user->id,
            'type'       => config('enums.game_types')['mancala'],
            'session_id' => $token,
        ]);

        return response()->json($res);
    }
}
