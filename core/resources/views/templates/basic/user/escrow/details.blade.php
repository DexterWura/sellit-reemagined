@extends($activeTemplate . 'layouts.frontend')
@section('content')
    <section class="section bg--light">
        <div class="container">
            <div class="row gy-4">

                <div class="col-md-6">
                    <div class="card custom--card">
                        <div class="card-header bg--base d-flex flex-wrap align-items-center justify-content-between">
                            <h6 class="text-white">
                                @if ($escrow->buyer_id == auth()->user()->id)
                                    @lang('Buying') {{ __($escrow->category->name) }}
                                @else
                                    @lang('Selling') {{ __($escrow->category->name) }}
                                @endif
                            </h6>

                            @if ($escrow->status != Status::ESCROW_NOT_ACCEPTED)
                                <a href="{{ route('user.escrow.milestone.index', $escrow->id) }}" class="btn btn-sm btn--dark">
                                    @lang('See Milestones') <i class="las la-arrow-right"></i>
                                </a>
                            @endif
                        </div>

                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                <div class="list-group-item">
                                    <small class="text-muted">@lang('Escrow Number')</small>
                                    <span>{{ $escrow->escrow_number }}</span>
                                </div>

                                <div class="list-group-item">
                                    <small class="text-muted">@lang('Title')</small>
                                    <span>{{ $escrow->title }}</span>
                                </div>

                                <div class="list-group-item">
                                    @if ($escrow->buyer_id == auth()->id())
                                        <small class="text-muted">@lang('Seller')</small>
                                        <span>{{ __(@$escrow->seller->username ?? $escrow->invitation_mail) }}</span>
                                    @else
                                        <small class="text-muted">@lang('Buyer')</small>
                                        {{ __(@$escrow->buyer->username ?? $escrow->invitation_mail) }}
                                    @endif
                                </div>

                                <div class="list-group-item">
                                    <small class="text-muted">@lang('Charge Payer')</small>
                                    @if ($escrow->charge_payer == Status::CHARGE_PAYER_SELLER)
                                        <span class="badge badge--dark">@lang('Seller')</span>
                                    @elseif($escrow->charge_payer == Status::CHARGE_PAYER_BUYER)
                                        <span class="badge badge--info">@lang('Buyer')</span>
                                    @else
                                        <span class="badge badge--success">@lang('50%-50%')</span>
                                    @endif
                                </div>

                                <div class="list-group-item">
                                    <small class="text-muted">@lang('Status')</small>
                                    @php echo $escrow->escrowStatus @endphp
                                </div>

                                <div class="list-group-item">
                                    <small class="text-muted">@lang('Amount')</small>
                                    <span>{{ showAmount($escrow->amount) }}</span>
                                </div>

                                <div class="list-group-item">
                                    <small class="text-muted">@lang('Charge')</small>
                                    <span>{{ showAmount($escrow->charge) }}</span>
                                </div>

                                <div class="list-group-item">
                                    <small class="text-muted">@lang('Created Milestone')</small>
                                    <span>
                                        {{ showAmount($escrow->milestones->sum('amount')) }}

                                    </span>
                                </div>

                                <div class="list-group-item">
                                    <small class="text-muted">@lang('Milestone Funded')</small>
                                    <span>
                                        {{ showAmount($escrow->milestones->where('payment_status', Status::MILESTONE_FUNDED)->sum('amount')) }}

                                    </span>
                                </div>

                                <div class="list-group-item">
                                    <small class="text-muted">@lang('Milestone Unfunded')</small>
                                    <span>
                                        {{ showAmount($escrow->milestones->where('payment_status', Status::MILESTONE_UNFUNDED)->sum('amount')) }}

                                    </span>
                                </div>

                                <div class="list-group-item">
                                    <small class="text-muted">@lang('Rest Amount')</small>
                                    <span>
                                        {{ showAmount($escrow->restAmount()) }}
                                    </span>
                                </div>

                                @if ($escrow->status == Status::ESCROW_DISPUTED)
                                    <div class="list-group-item">
                                        <small class="text-muted">@lang('Disputed By')</small>
                                        <span>
                                            {{ $escrow->disputer->username }}
                                        </span>
                                    </div>

                                    <div class="list-group-item">
                                        <h6 class="m-0 text--danger">@lang('Dispute Reason')</h6>
                                        <p class="m-0">{{ __($escrow->dispute_note) }}</p>
                                    </div>
                                @endif
                            </div>

                        </div>

                        @if ($escrow->status == Status::ESCROW_ACCEPTED || $escrow->status == Status::ESCROW_NOT_ACCEPTED)
                            @php
                                $hasSellerAndBuyer = $escrow->seller_id && $escrow->buyer_id;
                            @endphp

                            <div class="card-footer d-flex flex-wrap justify-content-center gap-2 bg-white">
                                @if ($escrow->status == Status::ESCROW_NOT_ACCEPTED)
                                    <button class="btn btn--danger confirmationBtn" data-question="@lang('Are you sure to cancel this escrow?')"
                                        data-action="{{ route('user.escrow.cancel', $escrow->id) }}"><i
                                            class="la la-times"></i>@lang('Cancel')</button>

                                    @if ($escrow->creator_id != auth()->id() && $hasSellerAndBuyer)
                                        <button class="btn btn--success confirmationBtn" data-question="@lang('Are you sure to accept this escrow?')"
                                            data-action="{{ route('user.escrow.accept', $escrow->id) }}"><i
                                                class="la la-check"></i>@lang('Accept')</button>
                                    @endif
                                @else
                                    {{-- payment dispute button --}}
                                    @if ($hasSellerAndBuyer)
                                        <button class="btn btn--danger text-white user-action"> <i class="las la-exclamation-triangle"></i>
                                            @lang('Dispute Escrow')</button>
                                    @endif
                                    {{-- If all amount is paid and the escrow is accepted --}}
                                    @if ($escrow->restAmount() <= 0 && $escrow->buyer_id == auth()->user()->id && $hasSellerAndBuyer)
                                        <button class="btn btn--primary confirmationBtn" data-question="@lang('Are you sure to dispatch this escrow?')"
                                            data-action="{{ route('user.escrow.dispatch', $escrow->id) }}"><i class="la la-money-check-alt"></i>
                                            @lang('Dispatch Payment')</button>
                                    @endif
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card custom--card">
                        <div class="card-header bg--base d-flex flex-wrap align-items-center justify-content-between">
                            <h6 class="text-white">@lang('Conversations')</h6>
                            <button type="button" class="btn btn-sm btn--dark reloadButton"><i class="las la-redo-alt"></i></button>
                        </div>
                        <div class="card-body">
                            <div class="messaging msg_history">
                                <div class="inbox_msg">
                                    <ul class="list msg-list d-flex flex-column">
                                        @if ($messages->count() > 0)
                                            @foreach ($messages as $message)
                                                @php
                                                    $classText = $message->sender_id == auth()->user()->id ? 'send' : 'receive';
                                                @endphp
                                                <div class="msg-list__item">
                                                    <div class="msg-{{ $classText }}">
                                                        @if ($escrow->status == Status::ESCROW_DISPUTED && $message->sender_id != auth()->id())
                                                            <p class="mb-0">
                                                                @if ($message->admin)
                                                                    <span class="fw-bold text--danger">
                                                                        @lang('SYSTEM')
                                                                    </span>
                                                                @else
                                                                    {{ @$message->sender->username }}
                                                                @endif
                                                            </p>
                                                        @endif
                                                        <div class="msg-{{ $classText }}__content">
                                                            <p class="msg-{{ $classText }}__text mb-0">
                                                                {{ __($message->message) }}
                                                            </p>
                                                        </div>
                                                        <ul
                                                            class="list msg-{{ $classText }}__history @if ($classText == 'send') justify-content-end @endif">
                                                            <div class="msg-receive__history-item">
                                                                {{ $message->created_at->format('h:i A') }}</div>
                                                            <div class="msg-receive__history-item">{{ $message->created_at->diffForHumans() }}</div>
                                                        </ul>
                                                    </div>
                                                    </li>
                                            @endforeach
                                        @else
                                            <div class="empty-message text-center">
                                                <div class="empty-message__icon">
                                                    <i class="la la-comment-slash"></i>
                                                </div>
                                                <div class="empty-message__heading">
                                                    @lang('No conversation yet')
                                                </div>

                                            </div>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($escrow->status != Status::ESCROW_CANCELLED && $escrow->status != Status::ESCROW_COMPLETED)
                        <div class="msg-option">
                            <form class="message-form">
                                <div class="msg-option__content rounded-pill">
                                    <div class="msg-option__group ">
                                        <input type="text" class="form-control msg-option__input" name="message" autocomplete="off"
                                            placeholder="@lang('Send Message')">
                                        <button type="submit" class="btn bg--base msg-option__button reloadButton rounded-pill">
                                            <i class="lab la-telegram-plane"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <div class="modal fade " id="actionModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Dispute Escrow')</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="{{ route('user.escrow.dispute', $escrow->id) }}" method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="form-label">@lang('Dispute Reason')</label>
                            <textarea class="form-control form--control-textarea" name="dispute_reason" rows="3" placeholder="@lang('Enter the reason')" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn--base h-45 w-100">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-confirmation-modal />
@endsection

@push('style-lib')
    <link rel="stylesheet" href="{{ asset($activeTemplateTrue . 'css/chat.css') }}">
@endpush

@push('style')
    <style>
        .list-group-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";
            $(".msg_history").animate({
                scrollTop: $('.msg_history').prop("scrollHeight")
            }, 1);

            var actionModal = $('#actionModal');

            $('.user-action').on('click', function() {
                actionModal.modal('show');
            });

            $('.message-form').on("submit", function(e) {
                e.preventDefault();
                $(this).find('button[type=submit]');
                let message = $(this).find('[name=message]').val();

                var url = '{{ route('user.escrow.message.reply') }}';
                var data = {
                    _token: "{{ csrf_token() }}",
                    conversation_id: "{{ $conversation->id }}",
                    message: $(this).find('[name=message]').val()
                }

                $.post(url, data, function(response) {
                    if (response['error']) {
                        $.each(response['error'], function(i, v) {
                            notify('error', v);
                        });
                        return true;
                    }

                    var html = `
                            <div class="msg-list__item">
                                <div class="msg-send">
                                    <div class="msg-send__content">
                                        <p class="msg-send__text mb-0">
                                            ${response['message']}
                                        </p>
                                    </div>
                                    <ul class="list msg-send__history  justify-content-end ">
                                        <div class="msg-receive__history-item">${response['created_time']}</div>
                                        <div class="msg-receive__history-item">${response['created_diff']}</div>
                                    </ul>
                                </div>
                            </li>
                    `;

                    $('.msg-list').append(html);
                    $(".msg_history").animate({
                        scrollTop: $('.msg_history').prop("scrollHeight")
                    }, 1);
                });
                $(this).find('[name=message]').val('')

            });

            $('.reloadButton').on("click", function() {
                var url = '{{ route('user.escrow.message.get') }}';
                var data = {
                    conversation_id: "{{ $conversation->id }}"
                }
                $.get(url, data, function(response) {

                    if (response['error']) {
                        $.each(response['error'], function(i, v) {
                            notify('error', v);
                        });
                        return true;
                    }

                    $('.msg-list').html(response);
                    $(".msg_history").animate({
                        scrollTop: $('.msg_history').prop("scrollHeight")
                    }, 1);
                });
            });

        })(jQuery);
    </script>
@endpush
