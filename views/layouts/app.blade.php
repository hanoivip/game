<html xmlns="http://www.w3.org/1999/xhtml" lang="{{ app()->getLocale() }}">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>PlaySite - @yield('title')</title>
</head>

<body>
      @if (Auth::check())
            <p class="User"> Chào <b>{{ Auth::user()->getAuthIdentifier() }}</b>, 
            <a href="{{route('logout')}}" title="Thoát">
            <b>Thoát</b></a></p>
      @endif
      @yield('content')
</body></html>