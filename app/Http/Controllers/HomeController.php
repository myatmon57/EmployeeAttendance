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
        if ($request->has('from_date') && $request->has('to_date') &&
            $request->input('from_date') != '' && $request->input('to_date') != '') {
                $fromDate = $request->input('from_date') . ' 00:00:00';
                $toDate = $request->input('to_date') . ' 23:59:59';
                
                $query->whereBetween('check_in', [$fromDate, $toDate]);
        } else {
            $startOfMonth = Carbon::now()->startOfMonth();
            $endOfMonth = Carbon::now()->endOfMonth();
            $query->whereBetween('check_in', [$startOfMonth, $endOfMonth]);
        }
    
        if ($request->has('filter_status') && $request->input('filter_status') != '') {
            $query->where('status', $request->input('filter_status'));
        }

        // if ($request->has('filter_device') && $request->input('filter_device') != '') {
        //     $query->where('check_in_pc_name', $request->input('filter_device'));
        // }
        $query->orderBy('check_in', 'desc');

        // Get the filtered or unfiltered attendance records
        $attendances = $query->paginate(10)->withQueryString();
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

    // Apply filtering if any filters are present
    $hasFilter = false;

    if ($request->has('from_date') && $request->has('to_date') &&
        $request->input('from_date') != '' && $request->input('to_date') != '') {
            $fromDate = $request->input('from_date') . ' 00:00:00';
            $toDate = $request->input('to_date') . ' 23:59:59';
            
            $query->whereBetween('check_in', [$fromDate, $toDate]);
            $hasFilter = true;
    }

    if ($request->has('filter_status') && $request->input('filter_status') != '') {
        $query->where('status', $request->input('filter_status'));
        $hasFilter = true;
    }

    if ($request->has('filter_username') && $request->input('filter_username') != '') {
        $query->whereHas('user', function ($q) use ($request) {
            $q->where('name', 'LIKE', '%' . $request->input('filter_username') . '%');
        });
        $hasFilter = true;
    }

    $combinedData = [];

    $query->orderBy('check_in', 'desc');

    // If filters are applied, get the paginated result, otherwise return an empty paginator
    if ($hasFilter) {
        $attendances = $query->paginate(10)->withQueryString();
    } else {
        // Return an empty LengthAwarePaginator if no filter is applied
        $attendances = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10);
    }

    // Process attendance data only if there are results
    foreach ($attendances as $attendance) {
        $checkIn = $attendance->check_in ? Carbon::parse($attendance->check_in) : '';
        $checkOut = $attendance->check_out ? Carbon::parse($attendance->check_out) : '';
        $date = $checkIn ? $checkIn->format('Y-m-d') : ($checkOut ? $checkOut->format('Y-m-d') : '');
        $checkInTime = $attendance->check_in ? $checkIn->format('h:i A') : '';
        $checkOutTime = $attendance->check_out ? $checkOut->format('h:i A') : '';
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


    public function exportCsv(Request $request)
{
    $hasFilter = false;
    $query = Attendance::with('user');

    // Apply filtering if any filters are present
    $filename = 'attendance';
    info($request->input('from_date'));
    if ($request->has('from_date') && $request->has('to_date') &&
        $request->input('from_date') != '' && $request->input('to_date') != '') {
        info('here');
        $fromDate = $request->input('from_date') . ' 00:00:00';
        $toDate = $request->input('to_date') . ' 23:59:59';

        $filename .= '_' . \Carbon\Carbon::parse($request->input('from_date'))->format('Ymd') . '_to_' . \Carbon\Carbon::parse($request->input('to_date'))->format('Ymd');

        $query->whereBetween('check_in', [$fromDate, $toDate]);
        $hasFilter = true;
    }

    if ($request->has('filter_status') && $request->input('filter_status') != '') {
        $query->where('status', $request->input('filter_status'));
    }

    if ($request->has('filter_username') && $request->input('filter_username') != '') {
        $query->whereHas('user', function ($q) use ($request) {
            $q->where('name', 'LIKE', '%' . $request->input('filter_username') . '%');
        });
    }

    // Order the results by 社員番号 (Employee Number) in ascending order
    if ($hasFilter) {
        $query->join('users', 'Attendance.user_id', '=', 'users.id') // Join to include the users table
        ->orderBy('users.no', 'asc')
        ->orderBy('check_in', 'asc'); // Reference the no column from users table
        // Get all the filtered users
        $attendances = $query->select('Attendance.*')->get();
    } else {
        return redirect()->back()->with('error', '開始日と終了日を入力してください');
    }

    // Define the CSV filename
    $fileName = $filename . '.csv';

    // Define the headers for the CSV response
    $headers = [
        'Content-type'        => 'text/csv',
        'Content-Disposition' => 'attachment; filename=' . $fileName,
        'Pragma'              => 'no-cache',
        'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
        'Expires'             => '0',
    ];

    // Define the columns for the CSV file
    $columns = ['日付', '社員番号', '社員名', 'メール', 'チェックイン', 'チェックアウト', 'ステータス', '遅い理由', '早期チェックアウト理由'];

    // Create a callback to write the data
    $callback = function() use ($attendances, $columns) {
        $file = fopen('php://output', 'w');
        fputcsv($file, $columns);

        foreach ($attendances as $attendance) {
            $checkIn = $attendance->check_in ? Carbon::parse($attendance->check_in) : null;
            $checkOut = $attendance->check_out ? Carbon::parse($attendance->check_out) : null;
            
            // Format the date based on the check-in or check-out
            $date = $checkIn ? $checkIn->format('Y-m-d') : ($checkOut ? $checkOut->format('Y-m-d') : '');
            
            // Format check-in and check-out times
            $checkInTime = $checkIn ? $checkIn->format('h:i A') : '';
            $checkOutTime = $checkOut ? $checkOut->format('h:i A') : '';
            $status = $attendance->status == '0' ? '間に合う' : '遅刻';
            // Write the row data
            fputcsv($file, [
                $date,
                $attendance->user->no ?? '',        // 社員番号 (Employee Number)
                $attendance->user->name ?? '',      // 社員名 (Employee Name)
                $attendance->user->email ?? '',     // Email
                $checkInTime,                       // チェックイン (Check-in)
                $checkOutTime,                      // チェックアウト (Check-out)
                $status,                            // ステータス (Status)
                $attendance->comment ?? '',         // 遅い理由 (Reason for being late)
                $attendance->comment_out ?? '',     // 早期チェックアウト理由 (Early check-out reason)
            ]);
        }

        fclose($file);
    };

    // Return the response as a streamed CSV
    return response()->stream($callback, 200, $headers);
}

}
