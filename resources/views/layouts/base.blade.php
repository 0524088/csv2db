<!DOCTYPE html>
<html lang="zh">
    <head>
        <!-- ========== Required meta tags =========== -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, minimum-scale=1">
        <meta name="description" content="提供最具有獲利效率的動能投資策略和最佳財富管理服務">

        <!-- csrf -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- allow https to http  -->
        <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">

        <title>@yield('title')</title>
        @include('layouts.header')
    </head>
    <body class="gradient-custom">
        @include('layouts.navbar')
        @yield('content')
    </body>
</html>