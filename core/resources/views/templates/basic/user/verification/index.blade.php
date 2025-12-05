@extends($activeTemplate . 'layouts.frontend')

@section('content')
@push('breadcrumb')
<li class="breadcrumb-item">
    <a href="{{ route('user.home') }}">@lang('Dashboard')</a>
</li>
<li class="breadcrumb-item active" aria-current="page">@lang('Domain Verifications')</li>
@endpush
<section class="section bg--light">
<div class="container">
    <div class="card custom--card">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-3">
            <h5 class="card-title mb-0">
                <i class="las la-shield-alt me-2"></i>@lang('Domain Verifications')
            </h5>
        </div>
        <div class="card-body">
            @if($verifications->count() > 0)
            <div class="table-responsive">
                <table class="table table--striped">
                    <thead>
                        <tr>
                            <th>@lang('Domain')</th>
                            <th>@lang('Listing')</th>
                            <th>@lang('Method')</th>
                            <th>@lang('Status')</th>
                            <th>@lang('Attempts')</th>
                            <th>@lang('Expires')</th>
                            <th>@lang('Action')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($verifications as $verification)
                        <tr>
                            <td>
                                <strong>{{ $verification->domain }}</strong>
                            </td>
                            <td>
                                @if($verification->listing)
                                    <a href="{{ route('user.listing.show', $verification->listing->id) }}">
                                        {{ Str::limit($verification->listing->title, 30) }}
                                    </a>
                                @else
                                    <span class="text-muted">@lang('N/A')</span>
                                @endif
                            </td>
                            <td>
                                @if($verification->verification_method == 'file')
                                    <span class="badge badge--info">@lang('File Upload')</span>
                                @else
                                    <span class="badge badge--primary">@lang('DNS Record')</span>
                                @endif
                            </td>
                            <td>
                                @switch($verification->status)
                                    @case('pending')
                                        <span class="badge badge--warning">@lang('Pending')</span>
                                        @break
                                    @case('verified')
                                        <span class="badge badge--success">@lang('Verified')</span>
                                        @break
                                    @case('failed')
                                        <span class="badge badge--danger">@lang('Failed')</span>
                                        @break
                                    @case('expired')
                                        <span class="badge badge--secondary">@lang('Expired')</span>
                                        @break
                                @endswitch
                            </td>
                            <td>{{ $verification->attempts }}</td>
                            <td>
                                @if($verification->expires_at)
                                    {{ showDateTime($verification->expires_at, 'd M, Y') }}
                                    @if($verification->expires_at->isPast())
                                        <span class="text-danger">(@lang('Expired'))</span>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($verification->status == 0 && $verification->isValid())
                                    <a href="{{ route('user.verification.show', $verification->id) }}" 
                                       class="btn btn--sm btn--primary">
                                        <i class="las la-check-circle"></i> @lang('Verify')
                                    </a>
                                @elseif($verification->status == 1)
                                    <span class="text-success">
                                        <i class="las la-check-double"></i> @lang('Completed')
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($verifications->hasPages())
                {{ paginateLinks($verifications) }}
            @endif
            @else
            <div class="text-center py-5">
                <i class="las la-shield-alt display-3 text-muted mb-3"></i>
                <h5 class="text-muted">@lang('No domain verifications found')</h5>
                <p class="text-muted">@lang('Domain verifications will appear here when you create a domain or website listing')</p>
            </div>
            @endif
        </div>
    </div>
</div>
</section>
@endsection

