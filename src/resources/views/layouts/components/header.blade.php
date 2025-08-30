@php
    use Illuminate\Support\Str;
    $routeName = Route::currentRouteName();
    $isAdmin = request()->is('admin/*');
@endphp

<header class="header">
    <div class="header__logo">
        <a href="{{ $isAdmin ? '/admin/attendance/list' : '/attendance' }}">
            <img src="{{ asset('img/logo.png') }}" alt="ロゴ">
        </a>
    </div>

    @auth
        <nav class="header__nav">
            <ul>
                @if ($isAdmin)
                    @if (Str::startsWith($routeName, 'admin.users.'))
                        {{-- パターン3: 管理者 - 個別勤怠一覧表示中 --}}
                        <li><a href="/admin/attendances">今月の出勤一覧</a></li>
                        <li><a href="/admin/requests">申請一覧</a></li>
                    @else
                        {{-- パターン4: 管理者 - ダッシュボードなど --}}
                        <li><a href="/admin/attendances">勤怠一覧</a></li>
                        <li><a href="/admin/users">スタッフ一覧</a></li>
                        <li><a href="/admin/requests">申請一覧</a></li>
                    @endif
                    <li>
                        <form action="/admin/logout" method="POST">
                            @csrf
                            <button type="submit"
                                    onclick="event.preventDefault(); document.getElementById('admin-logout-form').submit();">
                                ログアウト
                            </button>
                        </form>
                        <form id="admin-logout-form" action="/admin/logout" method="POST" style="display: none;">
                            @csrf
                            <input type="hidden" name="redirect" value="/login">
                        </form>
                    </li>
                @else
                    {{-- パターン2: 一般ユーザー --}}
                    <li><a href="/attendance">勤怠</a></li>
                    <li><a href="/attendance/list">勤怠一覧</a></li>
                    <li><a href="/stamp_correction_request/list">申請</a></li>
                    <li>
                        <form action="/logout" method="POST">@csrf
                            <button type="submit"
                                    onclick="event.preventDefault(); document.getElementById('user-logout-form').submit();">
                                ログアウト
                            </button>
                        </form>
                        <form id="user-logout-form" action="/logout" method="POST" style="display: none;">
                            @csrf
                            <input type="hidden" name="redirect" value="/login">
                        </form>
                    </li>
                @endif
            </ul>
        </nav>
    @endauth
</header>
