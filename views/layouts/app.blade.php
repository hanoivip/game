<html xmlns="http://www.w3.org/1999/xhtml" lang="{{ app()->getLocale() }}">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="access-token" content="{{ current_user_device_token() }}">
<title>Game Module Dev - @yield('title')</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
<!-- Remember to include jQuery :) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.0.0/jquery.min.js"></script>
<!-- jQuery Modal -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.css" />
</head>
<body>
    @if (Auth::check())
        <p class="User"> Chào <b>{{ Auth::user()->getAuthIdentifier() }}</b>, 
        <a href="{{route('logout')}}" title="Thoát">
        <b>Thoát</b></a></p>
    @endif
    @if(isset($message))
        <div class="alert alert-success alert-dismissible">
        {{ $message }}
        </div>
    @endif
    @if(isset($error))
        <div class="alert alert-danger alert-dismissible">
        {{ $error }}
        </div>
    @endif
    @yield('content')
    <div id="ex1" class="modal">
        <p>Thanks for clicking. That felt good.</p>
        <a href="#" rel="modal:close">Close</a>
    </div>
	<script type="text/javascript" src="{{asset('js/myapp.js')}}"></script>
    @stack('scripts')
</body>
</html>