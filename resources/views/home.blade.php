@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('出席を追加') }}</div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form method="post" action="{{ route('clickAttendance') }}">
                        @csrf
                        <input type="hidden" name="action_flag" id="action_flag" value="">
                        <div class="row mb-0">
                            <div class="col-md-6 text-end">
                                <button type="submit" class="btn btn-primary" onclick="setFlag('checkin')">
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

                    <form method="get">
                        @csrf
                        <div class="row mb-3">
                            <label for="em_no" class="col-md-4 col-form-label text-md-end">{{ __('社員番号') }}</label>

                            <div class="col-md-6">
                                <input id="em_no" type="text" class="form-control @error('em_no') is-invalid @enderror" name="em_no" value="{{ old('em_no') }}" required autocomplete="em_no" autofocus>

                                @error('em_no')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="name" class="col-md-4 col-form-label text-md-end">{{ __('名前') }}</label>

                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>

                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        

                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('登録') }}
                                </button>
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
