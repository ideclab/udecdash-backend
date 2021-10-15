<?php

namespace App\Http\Controllers;

use App\Models\TutorialView;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TutorialsController extends Controller
{
    public function viewed() {
        $viewed = TutorialView::where('user_id', Auth::id())->pluck('identifier');
        return response()->json($viewed);
    }

    public function markAsViewed(Request $request) {
        $request->validate([
            'identifier' => ['required', Rule::in(['WELCOME_MESSAGE', 'REPORTS_LAYOUT'])]
        ]);
        TutorialView::updateOrCreate(['user_id' => Auth::id(), 'identifier' => $request->identifier]);
        $viewed = TutorialView::where('user_id', Auth::id())->pluck('identifier');
        return response()->json($viewed);
    }

    public function resetAll() {
        $response = ['reseted' => false];
        try{
            DB::table('tutorial_views')->where('user_id', Auth::id())
            ->update(['deleted_at' => Carbon::now()]);
            $response = ['reseted' => true];
        }catch(\Exception $e){
        }
        return response()->json($response);
    }
}
