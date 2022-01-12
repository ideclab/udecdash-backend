<?php

namespace App\Http\Controllers;

use App\Models\UserReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserReportsController extends Controller
{
    public function sendReport(Request $request) : mixed {
        $request->validate([
            'category' => ['required', Rule::in(['SUGGESTION', 'ERROR'])],
            'description' => 'required|string|min:10',
            'tracker' => 'nullable|json'
        ]);
        $report = new UserReport();
        $report->fill($request->all());
        $report->user_id = Auth::id();
        try {
            $report->save();
            return response()->json(["created" => true]);
        }catch(\Exception $e) {
            return response()->json(["created" => false]);
        }
    }
}
