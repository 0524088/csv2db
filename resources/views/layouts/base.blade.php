<!DOCTYPE html>
<html lang="zh">
    <head>

        @include('layouts.header')
    </head>
    <body class="gradient-custom">
        @if(Session::has('token'))
            @include('layouts.navbar')
        @endif
        <div class="container-fluid">
            @yield('content')
        </div>
    </body>
</html>