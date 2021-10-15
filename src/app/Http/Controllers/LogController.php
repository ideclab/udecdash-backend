<?php

namespace App\Http\Controllers;

use App\Http\Requests\LogRequest;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogController extends Controller
{
    public function create(LogRequest $request){
        foreach($request->all() as $log){
            $log = (array) $log;
            $log['user_id'] = Auth::user()->canvas_id;
            Log::create($log);
        }
        return response()->json(["saved" => true]);
    }
}
