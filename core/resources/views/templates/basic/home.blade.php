@extends($activeTemplate . 'layouts.frontend')
@section('content')
    @php
        $bannerContent = getContent('banner.content', true);
        $marketplaceHero = getContent('marketplace_hero.content', true);
        $showOriginalBanner = !@$marketplaceHero->data_values->status;
    @endphp

    {{-- Show original banner only if marketplace hero is disabled --}}
    @if($showOriginalBanner)
    <section class="hero" style="background-image:url({{ frontendImage('banner', @$bannerContent->data_values->background_image, '1800x790') }});">
        <div class="hero__content">
            <div class="container">
                <div class="row g-4 justify-content-center align-items-center justify-xxl-between banner-form">

                    <div class="col-md-9 col-lg-7 col-xxl-6 text-center text-lg-start">
                        <h2 class="hero__content-title text-capitalize text--white mt-0">
                            {{ __(@$bannerContent->data_values->heading) }}
                        </h2>
                        <p class="hero__content-para text--white mx-auto ms-lg-0">
                            {{ __(@$bannerContent->data_values->subheading) }}
                        </p>
                        @include($activeTemplate . 'partials.escrow_form')
                    </div>
                    <div class="col-lg-5 col-xxl-6 d-none d-lg-block">
                        <img src="{{ frontendImage('banner', @$bannerContent->data_values->front_image, '665x575') }}" alt="image" class="img-fluid">
                    </div>
                </div>
            </div>
        </div>
    </section>
    @endif

    @if ($sections->secs != null)
        @foreach (json_decode($sections->secs) as $sec)
            @include($activeTemplate . 'sections.' . $sec)
        @endforeach
    @endif
@endsection

@push('style')
    <style>
        .hero .input-group:has(.select2) {
            border: 0 !important;
            padding: 0px !important;
            border-radius: 6px;
        }
        
        /* Marketplace Section Styles */
        .marketplace-section {
            position: relative;
        }
        .marketplace-section .section-title {
            font-size: 1.75rem;
            color: #1a1a2e;
        }
        .marketplace-section .section-subtitle {
            font-size: 1rem;
        }
        .bg--light {
            background-color: #f8f9fa !important;
        }
        .listing-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .listing-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important;
        }
    </style>
@endpush
