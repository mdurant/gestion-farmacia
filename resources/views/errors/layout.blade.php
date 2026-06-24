<!DOCTYPE html>
<html lang="es-CL">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $meta['title'] ?? 'Error' }} ({{ $code }}) — Acalis Pharma</title>
    @include('partials.theme-script')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="vx-app vx-http-error-body font-sans antialiased" x-data>
    @yield('content')
</body>
</html>
