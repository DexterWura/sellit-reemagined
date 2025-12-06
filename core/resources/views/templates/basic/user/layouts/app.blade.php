@extends($activeTemplate . 'user.layouts.master')
@section('content')
@php
    // Convert templates.basic. to templates/basic/
    $templatePath = str_replace('.', '/', rtrim($activeTemplate, '.'));
    $sidenavPath = resource_path('views/' . $templatePath . 'user/partials/sidenav.json');
    $sidenav = file_exists($sidenavPath) ? file_get_contents($sidenavPath) : '{}';
@endphp
    <!-- page-wrapper start -->
    <div class="page-wrapper default-version">
        @include($activeTemplate . 'user.partials.sidenav')
        @include($activeTemplate . 'user.partials.topnav')

        <div class="container-fluid px-3 px-sm-0">
            <div class="body-wrapper">
                <div class="bodywrapper__inner">

                    @stack('topBar')
                    @include($activeTemplate . 'user.partials.breadcrumb')

                    @yield('panel')

                </div><!-- bodywrapper__inner end -->
            </div><!-- body-wrapper end -->
        </div>
    </div>
@endsection

