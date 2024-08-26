@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('出席を追加') }}</div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-warning" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form method="post" action="{{ route('clickAttendance') }}">
                        @csrf
                        <input type="hidden" name="action_flag" id="action_flag" value="">
                        <div class="row mb-3">
                            <label for="reason" class="col-md-4 col-form-label text-md-end">{{ __('理由') }}</label>

                            <div class="col-md-6">
                                <textarea id="reason" type="reason" class="form-control @error('reason') is-invalid @enderror" name="reason" value="{{ old('reason') }}" autocomplete="reason" autofocus></textarea>
                                @error('reason')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-0">
                            <div class="col-md-6 text-end">
                                <button type="submit" class="btn btn-primary" id="check_in" onclick="setFlag('checkin')">
                                    {{ __('チェックイン') }}
                                </button>
                            </div>
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-primary" onclick="setFlag('checkout')">
                                    {{ __('チェックアウト') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card" class="mt-5">
                <div class="card-header">{{ __('出席履歴') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="get" action="{{ route('home') }}">
                        @csrf
                        <div class="row">
                            <!-- Date Filter -->
                            <div class="col-md-4">
                                <label for="filter_date">日付</label>
                                <input type="date" name="filter_date" id="filter_date" class="form-control" value="{{ request('filter_date') }}">
                            </div>

                            <!-- Status Filter -->
                            <div class="col-md-4">
                                <label for="filter_status">ステータス</label>
                                <select name="filter_status" id="filter_status" class="form-control">
                                    <option value="">全て</option>
                                    <option value="0" {{ request('filter_status') === '0' ? 'selected' : '' }}>Ontime</option>
                                    <option value="1" {{ request('filter_status') === '1' ? 'selected' : '' }}>Late</option>
                                </select>
                            </div>

                            <!-- Submit Button -->
                            <div class="col-md-4 align-content-end">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-primary btn-block">フィルタ</button>
                            </div>
                        </div>
                        <div class="container mt-4">
                        
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>日付</th>
                                            <th>チェックイン</th>
                                            <th>チェックアウト</th>
                                            <th>ステータス</th>
                                            <th>遅い理由</th>
                                            <th>早期チェックアウト理由</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($combinedData as $info)
                                            <tr>
                                                <td>{{ $info['attendance_date'] }}</td>
                                                <td>{{ $info['attendance_checkIn'] }}</td>
                                                <td>{{ $info['attendance_checkOut'] }}</td>
                                                <td class="{{ $info['attendance_status'] == 0 ? 'text-success' : 'text-danger' }}">{{ $info['attendance_status'] == 0 ? 'Ontime' : 'Late' }}</td>
                                                <td>{{ $info['attendance_comment'] }}</td>
                                                <td>{{ $info['attendance_commentOut'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<script>
    function setFlag(flag) {
        document.getElementById('action_flag').value = flag;
    }
</script>
