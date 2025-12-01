@php
    $breadcrumbContent = getContent('breadcrumb.content', true);
@endphp

<div class="banner" style="background-image: url({{ frontendImage('breadcrumb', @$breadcrumbContent->data_values->image, '1800x385') }})">
    <div class="banner__content">
        <div class="container">
            <div class="row g-3 justify-content-center">
                <div class="col-lg-10 text-center">
                    <h3 class="mt-0 text--white">{{ __($pageTitle) }}</h3>
                </div>
            </div>
        </div>
    </div>
</div>
