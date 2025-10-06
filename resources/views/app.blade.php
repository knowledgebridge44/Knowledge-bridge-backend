<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Knowledge Bridge') }}</title>
    
    {{-- Inject runtime configuration for React app --}}
    <script>
        window.__APP_CONFIG__ = {
            apiBaseUrl: '{{ url('') }}',
            csrfToken: '{{ csrf_token() }}'
        };
    </script>
    
    {{-- Vite React App --}}
    @if (app()->environment('local'))
        {{-- Development: Vite dev server --}}
        <script type="module" src="http://localhost:5173/@vite/client"></script>
        <script type="module" src="http://localhost:5173/src/main.tsx"></script>
    @else
        {{-- Production: Built assets --}}
        <link rel="stylesheet" href="{{ asset('frontend/assets/index.css') }}">
        <script type="module" src="{{ asset('frontend/assets/index.js') }}"></script>
    @endif
</head>
<body>
    <div id="root"></div>
</body>
</html>



