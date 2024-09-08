<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserInfoController extends Controller
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
        // Start a query for filtering users
        $query = User::query();

        // Check if the filter by name is applied
        if ($request->has('filter_name') && $request->input('filter_name') != '') {
            $query->where('name', 'LIKE', '%' . $request->input('filter_name') . '%');
        }

        // Filter by role
        if ($request->has('role') && $request->input('role') !== '') {
            $query->where('role', $request->input('role'));
        }

        // Order the users by 社員番号 in ascending order
        $query->orderBy('no', 'asc');
        // Paginate the users and retain the filter in the query string
        $users = $query->paginate(10)->withQueryString();

        return view('allUserInfo', compact('users'));
    }

    public function exportCsv(Request $request)
        {
            // Start a query for filtering users
            $query = User::query();

            // Check if the filter by name is applied
            if ($request->has('filter_name') && $request->input('filter_name') != '') {
                $query->where('name', 'LIKE', '%' . $request->input('filter_name') . '%');
            }

            // Filter by role
            if ($request->has('role') && $request->input('role') !== '') {
                $query->where('role', $request->input('role'));
            }

            // Order the users by 社員番号 in ascending order
            $query->orderBy('no', 'asc');

            // Get all the filtered users
            $users = $query->get();

            // Define the CSV filename
            $fileName = 'users.csv';

            // Define the headers for the CSV
            $headers = [
                'Content-type'        => 'text/csv',
                'Content-Disposition' => 'attachment; filename=' . $fileName,
                'Pragma'              => 'no-cache',
                'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
                'Expires'             => '0'
            ];

            // Define the columns for the CSV
            $columns = ['社員番号', '社員名', 'メール', 'パソコン番号', 'ステータス'];

            // Create a callback to write the data
            $callback = function() use ($users, $columns) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $columns);

                foreach ($users as $user) {
                    fputcsv($file, [
                        $user->no,
                        $user->name,
                        $user->email,
                        $user->pc_name,
                        $user->role == 0 ? 'ユーザー' : ($user->role == 1 ? '管理者' : 'マネージャー'),
                    ]);
                }

                fclose($file);
            };

            // Return the response as a stream of CSV
            return response()->stream($callback, 200, $headers);
        }

}
