@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card" class="mt-5">
                <div class="card-header card-header-large">{{ __('ユーザー情報') }}</div>

                <div class="card-body">
                    <form method="get" action="{{ route('allUserInfo') }}">
                        @csrf
                        <div class="row">

                            <!-- username Filter -->
                            <div class="col-md-2">
                                <label for="filter_username">社員名</label>
                                <input 
                                    type="text" 
                                    name="filter_username" 
                                    id="filter_username" 
                                    class="form-control" 
                                    value="{{ request('filter_username') }}">
                            </div>
                            <div class="col-md-2">
                                <label for="filter_username">ロール</label>
                                <select id="role" name="role" class="form-control @error('role') is-invalid @enderror" required>
                                    <option value="" disabled selected></option>
                                    <!-- Example role options -->
                                    <option value="0" {{ request('role') == '0' ? 'selected' : '' }}>{{ __('ユーザー') }}</option>
                                    <option value="1" {{ request('role') == '1' ? 'selected' : '' }}>{{ __('管理者') }}</option>
                                    <option value="2" {{ request('role') == '2' ? 'selected' : '' }}>{{ __('マネージャー') }}</option>
                                    <!-- Add more roles as needed -->
                                </select>
                            </div>


                            <!-- Submit Button -->
                            <div class="col-md-4 align-content-end">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-primary btn-block">検索</button>
                                <a href="{{ url()->current() }}" class="btn btn-secondary">クリア</a>
                                <a href="{{ route('users.export-csv', request()->query()) }}" class="btn btn-success">Export CSV</a>
                            </div>
                        </div>
                        <div class="container mt-4">
                        
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>社員番号</th>
                                            <th>社員名</th>
                                            <th>メール</th>
                                            <th>アドレス</th>
                                            <th>電話番号</th>
                                            <th>パソコン番号</th>
                                            <th>ステータス</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($users as $user)
                                            <tr>
                                                <td>{{ $user->no }}</td>
                                                <td>{{ $user->name }}</td>
                                                <td>{{ $user->email }}</td>
                                                <td>{{ $user->address }}</td>
                                                <td>{{ $user->phone_number}}</td>
                                                <td>{{ $user->pc_name }}</td>
                                                <td>{{ $user->role == 0 ? 'ユーザー' : ($user->role == 1 ? '管理者' : 'マネージャー') }}</td>

                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-center">
                                <nav aria-label="Page navigation">
                                    <ul class="pagination">
                                        {{ $users->appends(request()->query())->links('pagination::bootstrap-4') }}
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
