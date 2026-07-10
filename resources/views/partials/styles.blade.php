<link rel="stylesheet" type="text/css" href="{{ url('/assets/css/sidebar-menu.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ url('/assets/css/simplebar.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ url('/assets/css/apexcharts.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ url('/assets/css/prism.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ url('/assets/css/rangeslider.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ url('/assets/css/quill.snow.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ url('/assets/css/google-icon.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ url('/assets/css/remixicon.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ url('/assets/css/swiper-bundle.min.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ url('/assets/css/fullcalendar.main.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ url('/assets/css/jsvectormap.min.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ url('/assets/css/lightpick.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ url('/assets/scss/style.css') }}" />
@php
    $faviconStoreSetting = $storeSetting ?? \App\Models\StoreSetting::current();

    $faviconUrl = $faviconStoreSetting?->logo_path
        ? asset('storage/' . $faviconStoreSetting->logo_path)
        : url('/assets/images/favicon.png');
@endphp

<link rel="icon" type="image/png" href="{{ $faviconUrl }}">