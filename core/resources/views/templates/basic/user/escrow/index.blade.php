@extends($activeTemplate . 'layouts.frontend')
@section('content')
    <section class="section bg--light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-12">
                    <table class="table custom--table table-responsive--lg escrow-table">
                        <thead>
                            <tr>
                                <th>@lang('Escrow Number')</th>
                                <th>@lang('Title')</th>
                                <th>@lang('Buyer - Seller')</th>
                                <th>@lang('Amount')</th>
                                <th>@lang('Category')</th>
                                <th>@lang('Charge')</th>
                                <th>@lang('Charge Payer')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($escrows as $escrow)
                                <tr>
                                    <td>{{ $escrow->escrow_number }}</td>
                                    <td>{{ __($escrow->title) }}</td>
                                    <td>
                                        @lang('I\'m') @if ($escrow->buyer_id == auth()->user()->id)
                                            @lang('buying from')
                                            {{ __(@$escrow->seller->username ?? $escrow->invitation_mail) }}
                                        @else
                                            @lang('selling to')
                                            {{ __(@$escrow->buyer->username ?? $escrow->invitation_mail) }}
                                        @endif
                                    </td>
                                    <td>
                                        {{ showAmount($escrow->amount) }}</td>
                                    <td>{{ $escrow->category->name }}</td>
                                    <td>
                                        {{ showAmount($escrow->charge) }}</td>
                                    <td>
                                        @if ($escrow->charge_payer == Status::CHARGE_PAYER_SELLER)
                                            <span class="badge badge--dark">@lang('Seller')</span>
                                        @elseif($escrow->charge_payer == Status::CHARGE_PAYER_BUYER)
                                            <span class="badge badge--info">@lang('Buyer')</span>
                                        @else
                                            <span class="badge badge--success">@lang('50%-50%')</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php echo $escrow->escrowStatus @endphp
                                    </td>
                                    <td>
                                        <a href="{{ route('user.escrow.details', $escrow->id) }}" class="btn btn-base--outline btn-sm detailBtn "><i
                                                class="la la-desktop"></i> @lang('Details')</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="100%" class="text-center">{{ __($emptyMessage) }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($escrows->hasPages())
                    <div class="mt-3">
                        {{ $escrows->links() }}
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
