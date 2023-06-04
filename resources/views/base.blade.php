<!DOCTYPE html>
<html lang="en">
<head>
    <title>Class Tree</title>
    @if ($routesGenerator ?? null)
        {!! $routesGenerator !!}
    @endif

    <script src="{{ asset('js/app.js') }}"></script>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
    @yield('body')
</body>
</html>
