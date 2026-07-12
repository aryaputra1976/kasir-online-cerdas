<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/sidebar-menu.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/simplebar.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/apexcharts.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/prism.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/rangeslider.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/quill.snow.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/google-icon.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/remixicon.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/swiper-bundle.min.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/fullcalendar.main.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/jsvectormap.min.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/lightpick.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ asset('assets/scss/style.css') }}" />
@php
    $faviconStoreSetting = $storeSetting ?? \App\Models\StoreSetting::current();

    $faviconUrl = $faviconStoreSetting?->logo_path
        ? asset('storage/' . $faviconStoreSetting->logo_path)
        : asset('assets/images/favicon.png');
@endphp

<link rel="icon" type="image/png" href="{{ $faviconUrl }}">
