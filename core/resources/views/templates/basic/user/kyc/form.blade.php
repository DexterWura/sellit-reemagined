@extends($activeTemplate . 'layouts.frontend')
@section('content')
<section class="section bg--light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card custom--card">
                    <div class="card-body">
                        <form action="{{ route('user.kyc.submit') }}" enctype="multipart/form-data" method="post">
                            @csrf
                            <x-viser-form identifierValue="kyc" identifier="act" />
                            
                                <button class="btn btn--base w-100" type="submit">@lang('Submit')</button>
                         
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('style-lib')
    <link rel="stylesheet" href="{{ asset('assets/global/css/select2.min.css') }}">
@endpush

@push('script-lib')
    <script src="{{ asset('assets/global/js/select2.min.js') }}"></script>
@endpush
