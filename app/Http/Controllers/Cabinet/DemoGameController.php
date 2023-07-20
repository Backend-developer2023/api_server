<?php

namespace App\Http\Controllers\Cabinet;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Models\Game;
use App\Services\TomHorn\Helper;

use App\Jobs\StrikeJob;

use App\Services\TomHorn\Client as TomHornClient;
use App\Services\Mancala\Client as MancalaClient;

class DemoGameController extends Controller
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
            return $this->tomhorn_demo($user, $game);
        } else if ($game->type === config('enums.game_types')['mancala']) {
            return $this->mancala_demo($user, $game);
        }

        return response()->json(
            [
                'status' => 'error',
                'error' => 'Unknown game'
            ], 500
        );
    }

    public function tomhorn_demo($user, $game)
    {
        $client = app()->make(TomHornClient::class);
        $game_info = $client->getPlayMoneyModuleInfo($game->info, "USD");

        if (!$game_info) {
            return response()->json([
                'status'  => 'error',
                'message' => "Error occured while trying to create game page, try again later"
            ]);
        }
        
        if ($user) {
            dispatch(new StrikeJob([
                'user' => $user
            ]));
        }

        return response()->json([
            'status'  => 'success',
            'type'    => 'TomHorn',
            //'html' => Helper::preparePage($game_info)
            'params'  => $game_info
        ]);
    }

    public function mancala_demo($user, $game)
    {
        $client = app()->make(MancalaClient::class);
        $res = $client->GetToken(
            intval($game->info),
            "",
            $user ? $user->currency : "RUB",
            true
        );
        $res['status'] = 'success';

        if ($user) {
            dispatch(new StrikeJob([
                'user' => $user
            ]));
        }

        return response()->json($res);
    }
}
