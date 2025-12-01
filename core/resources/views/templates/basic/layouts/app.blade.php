<!doctype html>
<html lang="{{ config('app.locale') }}" itemscope itemtype="http://schema.org/WebPage">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title> {{ gs()->siteName(__($pageTitle)) }}</title>
    @include('partials.seo')


    <link href="{{ asset('assets/global/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/global/css/all.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/global/css/line-awesome.min.css') }}">

    <link rel="stylesheet" href="{{ asset($activeTemplateTrue . 'css/slick.css') }}" />
    <link rel="stylesheet" href="{{ asset($activeTemplateTrue . 'css/main.css') }}" />
    <link rel="stylesheet" href="{{ asset($activeTemplateTrue . 'css/custom.css') }}" />
    @stack('style-lib')
    @stack('style')
    <link rel="stylesheet"
        href="{{ asset($activeTemplateTrue . 'css/color.php') }}?color={{ gs('base_color') }}&secondColor={{ gs('secondary_color') }}">
</head>

@php echo loadExtension('google-analytics') @endphp

<body>
    @stack('fbComment')


    <div class="preloader">
        <div class="preloader__loader">
            <span></span>
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>

    <div class="back-to-top">
        <span class="back-top">
            <i class="las la-angle-double-up"></i>
        </span>
    </div>

    @yield('panel')


    @if (gs()->gdpr_cookie == Status::ENABLE && !\Cookie::get('gdpr_cookie'))
        @php
            $cookie = App\Models\Frontend::where('data_keys', 'cookie.data')->first();
        @endphp
        <div class="cookies-card text-center hide">
            <div class="cookies-card__icon bg--base">
                <i class="las la-cookie-bite "></i>
            </div>
            <p class="mt-4 cookies-card__content">{{ @$cookie->data_values->short_desc }} <a href="{{ route('cookie.policy') }}"
                    target="_blank">@lang('learn more')</a>
            </p>
            <div class="cookies-card__btn mt-4">
                <a href="javascript:void(0)" class="btn btn--base w-100 policy">@lang('Allow')</a>
            </div>
        </div>
    @endif

    <div class="overlay"></div>

    <script src="{{ asset('assets/global/js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('assets/global/js/bootstrap.bundle.min.js') }}"></script>


    <script src="{{ asset($activeTemplateTrue . 'js/slick.js') }}"></script>
    <script src="{{ asset($activeTemplateTrue . 'js/jquery.nice-select.js') }}"></script>
    <script src="{{ asset($activeTemplateTrue . 'js/app.js') }}"></script>

    @stack('script-lib')

    @php echo loadExtension('tawk-chat') @endphp

    @include('partials.notify')

    @if (gs('pn'))
        @include('partials.push_script')
    @endif
    @stack('script')

    <script>
        (function($) {
            "use strict";
            $(".langSel").on("change", function() {
                window.location.href = "{{ route('home') }}/change/" + $(this).val();
            });

            $('.select2').each(function(index, element) {
                $(element).select2({
                    minimumResultsForSearch: "-1"
                });
            });

            $('.select2-basic').each(function(index, element) {
                $(element).select2({
                    dropdownParent: $(element).closest('.select2-parent')
                });
            });


            $('.policy').on('click', function() {
                $.get('{{ route('cookie.accept') }}',
                    function(response) {
                        $('.cookies-card').addClass('d-none');
                    });
            });

            setTimeout(function() {
                $('.cookies-card').removeClass('hide')
            }, 2000);



            var inputElements = $('[type=text],[type=password],select,textarea');
            $.each(inputElements, function(index, element) {
                element = $(element);
                element.closest('.form-group').find('label').attr('for', element.attr('name'));
                element.attr('id', element.attr('name'))
            });

            $.each($('input, select, textarea'), function(i, element) {
                var elementType = $(element);
                if (elementType.attr('type') != 'checkbox') {
                    if (element.hasAttribute('required')) {
                        $(element).closest('.form-group').find('label').addClass('required');
                    }
                }

            });

            $.each($('input:not([type=checkbox]):not([type=hidden]), select, textarea'), function(i, element) {

                if (element.hasAttribute('required')) {
                    $(element).closest('.form-group').find('label').addClass('required');
                }

            });


            $('.showFilterBtn').on('click', function() {
                $('.responsive-filter-card').slideToggle();
            });



            Array.from(document.querySelectorAll('table')).forEach(table => {
                let heading = table.querySelectorAll('thead tr th');
                Array.from(table.querySelectorAll('tbody tr')).forEach((row) => {
                    Array.from(row.querySelectorAll('td')).forEach((colum, i) => {
                        colum.setAttribute('data-label', heading[i].innerText)
                    });
                });
            });

            let disableSubmission = false;
            $('.disableSubmission').on('submit', function(e) {
                if (disableSubmission) {
                    e.preventDefault()
                } else {
                    disableSubmission = true;
                }
            });
        })(jQuery);
    </script>
</body>

</html>
