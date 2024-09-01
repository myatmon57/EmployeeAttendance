@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card" class="mt-5">
                <div class="card-header card-header-large">{{ __('出席履歴') }}</div>

                <div class="card-body">
                    <form method="get" action="{{ route('allAttendance') }}">
                        @csrf
                        <div class="row">
                            <!-- Date Filter -->
                            <div class="col-md-2">
                                <label for="filter_date">日付</label>
                                <input type="date" name="filter_date" id="filter_date" class="form-control" value="{{ request('filter_date') }}">
                            </div>

                            <!-- Status Filter -->
                            <div class="col-md-2">
                                <label for="filter_status">ステータス</label>
                                <select name="filter_status" id="filter_status" class="form-control">
                                    <option value="">全て</option>
                                    <option value="0" {{ request('filter_status') === '0' ? 'selected' : '' }}>間に合う</option>
                                    <option value="1" {{ request('filter_status') === '1' ? 'selected' : '' }}>遅刻</option>
                                </select>
                            </div>

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

                            <!-- Submit Button -->
                            <div class="col-md-4 align-content-end">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-primary btn-block">検索</button>
                                <a href="{{ url()->current() }}" class="btn btn-secondary">クリア</a>
                            </div>
                        </div>
                        <div class="container mt-4">
                        
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>日付</th>
                                            <th>社員番号</th>
                                            <th>社員名</th>
                                            <th>メール</th>
                                            <th>チェックイン</th>
                                            <th>チェックアウト</th>
                                            <th>ステータス</th>
                                            <!-- <th>チェックインデバイス</th> -->
                                            <th>遅い理由</th>
                                            <th>早期チェックアウト理由</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($combinedData as $info)
                                            <tr>
                                                <td>{{ $info['attendance_date'] }}</td>
                                                <td>{{ $info['user_no'] }}</td>
                                                <td>{{ $info['user_name'] }}</td>
                                                <td>{{ $info['user_email'] }}</td>
                                                <td>{{ $info['attendance_checkIn'] }}</td>
                                                <td>{{ $info['attendance_checkOut'] }}</td>
                                                <td class="{{ $info['attendance_status'] == 0 ? 'text-success' : 'text-danger' }}">{{ $info['attendance_status'] == 0 ? '間に合う' : '遅刻' }}</td>
                                                <!-- <td class="{{ $info['hostCheck'] == 1 ? 'text-success' : 'text-danger' }}">{{ $info['hostCheck'] == 1 ? 'オフィスデバイス' : 'その他' }}</td> -->
                                                <td>{{ $info['attendance_comment'] }}</td>
                                                <td>{{ $info['attendance_commentOut'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-center">
                                <nav aria-label="Page navigation">
                                    <ul class="pagination">
                                        {{ $attendances->links('pagination::bootstrap-4') }}
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
