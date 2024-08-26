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
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = $user->attendances();
        // Apply filtering if date or status filters are present
        if ($request->has('filter_date') && $request->input('filter_date') != '') {
            $query->whereDate('check_in', $request->input('filter_date'));
        }
    
        if ($request->has('filter_status') && $request->input('filter_status') != '') {
            $query->where('status', $request->input('filter_status'));
        }
    
        // Get the filtered or unfiltered attendance records
        $attendances = $query->get();
        $combinedData = [];
        // Access the user and their related attendance records
        if ($user) {
            foreach ($attendances as $attendance) {
                $checkIn = Carbon::parse($attendance->check_in);
                $checkOut = Carbon::parse($attendance->check_out);

                $date = $checkIn->format('Y-m-d');
                $checkInTime = $time = $checkIn->format('h:i A');
                $checkOutTime = $time = $checkOut->format('h:i A');

                $combinedData[] = [
                    'user_id' => $user->id,
                    'user_no' => $user->no,
                    'user_name' => $user->name,
                    'user_email' => $user->email,
                    'attendance_id' => $attendance->id,
                    'attendance_date' => $date,
                    'attendance_status' => $attendance->status,
                    'attendance_comment' => $attendance->comment,
                    'attendance_commentOut' => $attendance->comment_out,
                    'attendance_checkIn' => $checkInTime,
                    'attendance_checkOut' => $checkOutTime,
                ];
            }
        }
        return view('home', compact('combinedData'));
    }

    public function clickAttendance(Request $request)
    {
        // Get the currently authenticated user
        $action = $request->action_flag;
        $user = Auth::user();
        $officeTime = Carbon::createFromTime(1, 0);  // Create a Carbon instance for 9:00 AM
        $checkStatus = Carbon::now();  // Get the current time as a Carbon instance
        $status = 0;
        
        $today = Carbon::now()->toDateString();
                                       
        if ($checkStatus->greaterThan($officeTime)) {
            $status = 1;
        }
        if ($action == 'checkin') {
            $checkInDataExist = Attendance::where('user_id', $user->id)->whereDate('check_in', $today)->Exists();
            info($checkInDataExist);
            if ($checkInDataExist) {
                return redirect()->back()->with('error', '出席チェックインはすでに存在しています');
            } else {
                Attendance::create([
                    'user_id' => $user->id,
                    'comment' => $request->reason,
                    'status' => $status,
                    'check_in' => Carbon::now(),
                ]);
                return redirect()->back()->with('success', '出席チェックインが正常に記録されました。');
            }
        } else {
            $attendance = Attendance::where('user_id', $user->id)
                                    ->whereDate('check_in', $today);
            if ($attendance) {
                Attendance::where('user_id', $user->id)
                ->whereDate('check_in', $today)
                ->update(['check_out' => Carbon::now(), 'comment_out' => $request->reason]);
            } else {
                Attendance::create([
                    'user_id' => $user->id,
                    'comment_out' => $request->reason,
                    'status' => 1,
                    'check_out' => Carbon::now(),
                ]);
            }
            return redirect()->back()->with('success', '出席チェックアウトが正常に記録されました。');
        }
    }
}
