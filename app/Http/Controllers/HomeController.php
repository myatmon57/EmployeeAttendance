<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    public function clickAttendance(Request $request)
    {

        // Get the currently authenticated user
        $action = $request->action_flag;
        $user = Auth::user();
        if ($action == 'checkin') {
            Attendance::create([
                'user_id' => $user->id,
            ]);
            return redirect()->back()->with('success', '出席チェックインが正常に記録されました。');

        } else {
            $today = Carbon::now()->toDateString();
            Attendance::where('user_id', $user->id)
                                ->whereDate('checkin', $today)
                                ->update(['checkout' => Carbon::now()]);
            return redirect()->back()->with('success', '出席チェックアウトが正常に記録されました。');

        }
        

    }

    // public function showAllAttendance()
    // {

    // }
}
