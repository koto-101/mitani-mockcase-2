@php
    use Illuminate\Support\Str;
    $routeName = Route::currentRouteName();
    $isAdmin = request()->is('admin/*');
    $isSpecialAttendanceView = !$isAdmin && ($routeName === 'attendance') && ($status === 'done');
@endphp

<header class="header">
    <div class="header__inner">
        <div class="header__logo">
                <img src="{{ asset('img/logo.png') }}" alt="ロゴ">
        </div>

        @auth
            @if (!in_array($routeName, ['verification.notice']))
                <nav class="header__nav">
                    <ul>
                        @if ($isSpecialAttendanceView)
                            {{-- 一般ユーザーの勤怠完了画面だけ表示 --}}
                            <li><a href="/attendance/list">今月の出勤一覧</a></li>
                            <li><a href="/stamp_correction_request/list">申請一覧</a></li>

                        @elseif ($isAdmin)
                            {{-- 管理者メニュー --}}
                            <li><a href="/admin/attendance/list">勤怠一覧</a></li>
                            <li><a href="/admin/staff/list">スタッフ一覧</a></li>
                            <li><a href="/admin/requests">申請一覧</a></li>

                        @else
                            {{-- 通常の一般ユーザーメニュー --}}
                            <li><a href="/attendance">勤怠</a></li>
                            <li><a href="/attendance/list">勤怠一覧</a></li>
                            <li><a href="/stamp_correction_request/list">申請</a></li>
                        @endif

                        <li>
                            <form action="{{ $isAdmin ? url('/admin/logout') : url('/logout') }}" method="POST" style="display:inline;">
                                @csrf
                                <input type="hidden" name="redirect" value="/login">
                                <button type="submit">ログアウト</button>
                            </form>
                        </li>
                    </ul>
                </nav>
            @endif
        @endauth
    </div>
</header>
