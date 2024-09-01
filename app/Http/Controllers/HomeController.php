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

        // test hostname on server
        $clientIp = $_SERVER['REMOTE_ADDR']; // Get the client's IP address
        $clientHostName = gethostbyaddr($clientIp); // Get the hostname

        $query = $user->attendances();
        // Apply filtering if date or status filters are present
        if ($request->has('filter_date') && $request->input('filter_date') != '') {
            $query->whereDate('check_in', $request->input('filter_date'));
        }
    
        if ($request->has('filter_status') && $request->input('filter_status') != '') {
            $query->where('status', $request->input('filter_status'));
        }

        // if ($request->has('filter_device') && $request->input('filter_device') != '') {
        //     $query->where('check_in_pc_name', $request->input('filter_device'));
        // }

        // Get the filtered or unfiltered attendance records
        $attendances = $query->paginate(5);
        $combinedData = [];
        // Access the user and their related attendance records
        if ($user) {
            foreach ($attendances as $attendance) {
                $checkIn = $attendance->check_in ? Carbon::parse($attendance->check_in) : '';
                $checkOut = $attendance->check_out ? Carbon::parse($attendance->check_out) : '';
                $date = $checkIn->format('Y-m-d');
                if ($date == null) {
                    $date = $checkOut->format('Y-m-d');
                }
                $checkInTime = $attendance->check_in ? $checkIn->format('h:i A') : '';
                $checkOutTime =  $attendance->check_out ? $checkOut->format('h:i A') : '';
                $hostCheck = 0;
                if ($user->pc_name == $attendance->check_in_pc_name) {
                    $hostCheck = 1;
                }

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
                    'hostCheck' => $hostCheck,
                ];
            }
        
        }
        return view('home', compact('combinedData', 'attendances'));
    }

    /**
     * Show the all user info for 管理者.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function allAttendance(Request $request)
    {
        $query = Attendance::with('user');
        // Apply filtering if date or status filters are present
        if ($request->has('filter_date') && $request->input('filter_date') != '') {
            $query->whereDate('check_in', $request->input('filter_date'));
        }
    
        if ($request->has('filter_status') && $request->input('filter_status') != '') {
            $query->where('status', $request->input('filter_status'));
        }

        if ($request->has('filter_username') && $request->input('filter_username') != '') {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->input('filter_username') . '%');
            });
        }
        // Get the filtered or unfiltered attendance records
        $attendances = $query->paginate(10);
        $combinedData = [];
        // Access the user and their related attendance records
        foreach ($attendances as $attendance) {
                $checkIn = $attendance->check_in ? Carbon::parse($attendance->check_in) : '';
                $checkOut = $attendance->check_out ? Carbon::parse($attendance->check_out) : '';
                $date = $checkIn->format('Y-m-d');
                if ($date == null) {
                    $date = $checkOut->format('Y-m-d');
                }
                $checkInTime = $attendance->check_in ? $checkIn->format('h:i A') : '';
                $checkOutTime =  $attendance->check_out ? $checkOut->format('h:i A') : '';
                $hostCheck = 0;
                
                $combinedData[] = [
                    'user_id' => $attendance->user->id,
                    'user_no' => $attendance->user->no,
                    'user_name' => $attendance->user->name,
                    'user_email' => $attendance->user->email,
                    'attendance_id' => $attendance->id,
                    'attendance_date' => $date,
                    'attendance_status' => $attendance->status,
                    'attendance_comment' => $attendance->comment,
                    'attendance_commentOut' => $attendance->comment_out,
                    'attendance_checkIn' => $checkInTime,
                    'attendance_checkOut' => $checkOutTime,
                    'hostCheck' => $hostCheck,
                ];
            }
        
        return view('alluserAttendance', compact('combinedData', 'attendances'));
    }

    public function clickAttendance(Request $request)
    {
        // Get the currently authenticated user
        $action = $request->action_flag;
        $user = Auth::user();
        $officeTime = Carbon::createFromTime(9, 0);  // Create a Carbon instance for 9:00 AM
        $checkStatus = Carbon::now();  // Get the current time as a Carbon instance
        $status = 0;
        $hostName = gethostname();
        
        $today = Carbon::now()->toDateString();
                                       
        if ($checkStatus->greaterThan($officeTime)) {
            $status = 1;
        }
        $checkInDataExist = Attendance::where('user_id', $user->id)->whereDate('check_in', $today)->Exists();
            if ($action == 'checkin') {
            if ($checkInDataExist) {
                return redirect()->back()->with('error', '出席チェックインはすでに存在しています');
            } else {
                Attendance::create([
                    'user_id' => $user->id,
                    'comment' => $request->reason,
                    'check_in_pc_name' => $hostName,
                    'status' => $status,
                    'check_in' => Carbon::now(),
                ]);
                return redirect()->back()->with('success', '出席チェックインが正常に記録されました。');
            }
        } else {
            if ($checkInDataExist) {
                Attendance::where('user_id', $user->id)
                ->whereDate('check_in', $today)
                ->update(['check_out' => Carbon::now(), 'comment_out' => $request->reason]);
            } else {
                Attendance::create([
                    'user_id' => $user->id,
                    'check_in_pc_name' => $hostName,
                    'comment_out' => $request->reason,
                    'status' => 1,
                    'check_out' => Carbon::now(),
                ]);
            }
            return redirect()->back()->with('success', '出席チェックアウトが正常に記録されました。');
        }
    }
}
