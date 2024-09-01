@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header card-header-large">{{ __('出席を追加') }}</div>

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

                    <form id="actionForm" method="POST" action="{{ route('clickAttendance') }}">
                        @csrf
                        <input type="hidden" name="action_flag" id="action_flag" value="">
                        
                        <div class="row mb-3">
                            <div class="col-md-6 text-end">
                                <button type="button" class="btn btn-primary" onclick="confirmAction('checkin')">
                                    {{ __('チェックイン') }}
                                </button>
                            </div>
                            <div class="col-md-6">
                                <button type="button" class="btn btn-secondary" onclick="confirmAction('checkout')">
                                    {{ __('チェックアウト') }}
                                </button>
                            </div>
                        </div>
                        <div class="row mb-0">
                            <label for="reason" class="col-md-4 col-form-label text-md-end">{{ __('理由') }}<span class="text-danger small" style="font-size: 0.7rem;">（オプショナル）</span></label>

                            <div class="col-md-6">
                                <textarea id="reason" type="reason" class="form-control @error('reason') is-invalid @enderror" name="reason" value="{{ old('reason') }}" autocomplete="reason" placeholder="遅刻する場合は、理由を書いたほうがよろしいです。"></textarea>
                                @error('reason')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card" class="mt-5">
                <div class="card-header card-header-large">{{ __('出席履歴') }}</div>

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

                            <!-- device Filter -->
                            <!-- <div class="col-md-2">
                                <label for="filter_device">チェックインデバイス</label>
                                <select name="filter_device" id="filter_device" class="form-control">
                                    <option value="">全て</option>
                                    <option value="0" {{ request('filter_device') === '1' ? 'selected' : '' }}>オフィスデバイス</option>
                                    <option value="1" {{ request('filter_device') === '0' ? 'selected' : '' }}>その他</option>
                                </select>
                            </div> -->

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

<script>

    function confirmAction(action) {
        let message = action === 'checkin' ? 'チェックインしますか？' : 'チェックアウトしますか？';
        return Swal.fire({
            title: '確認',
            text: message,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'はい',
            cancelButtonText: 'キャンセル'
        }).then((result) => {
            if (result.isConfirmed) {
                setFlag(action);
                document.getElementById('actionForm').submit();
            } else {
                return false; // Cancel form submission
            }
        });
    }
    function setFlag(flag) {
        document.getElementById('action_flag').value = flag;
    }
</script>
