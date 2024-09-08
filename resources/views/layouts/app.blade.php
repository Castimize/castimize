<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title') - {{ env('APP_NAME') }}</title>

    <meta name="csrf-token" content="{{ csrf_token() }}"/>

    <link rel="manifest" href="/manifest.json" crossorigin="use-credentials">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="theme-color" content="#ffffff">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

{{--    <link rel="stylesheet" href="{{ app()->environment('local') ? mix('css/offerte.admin.css') : asset('css/offerte.admin.css') }}">--}}
    <link rel=stylesheet href=https://cdn.jsdelivr.net/npm/pretty-print-json@2.0/dist/css/pretty-print-json.css>

    @yield('css')
    @stack('styles')
</head>
<body id="app-layout">

<div class="app-wrapper" id="app">
    <x-nav-bar/>

    <section style="padding: 0;">
        <div class="container-fluid">
            @include('flash::message')

            <div class="row">
                <div class="col-md-12">
                    @yield('breadcrumbs')
                </div>
            </div>
        </div>
    </section>

    <section>
        <div class="container-fluid">
            @yield('content')
        </div>
    </section>

    <div class="container-fluid">
        <p class="text-center text-muted">
{{--            <i class="fa fa-fw fa-phone"></i> {{ config('offerte.phone') }} - &copy; {{ date('Y') }} {{ siteName() }}--}}
        </p>
    </div>
</div>

<!-- JavaScripts -->
{{--<script src="{{ mix('js/offerte.admin.js') }}"></script>--}}
<script src=https://cdn.jsdelivr.net/npm/pretty-print-json@2.0/dist/pretty-print-json.min.js></script>

@yield('js')
@stack('scripts')

</body>
</html>
