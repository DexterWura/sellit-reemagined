@extends('admin.layouts.app')
@section('panel')
    <div class="row gy-4">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.charge.global') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="form-group col-md-4">
                                <label>
                                    @lang('Charge Cap')
                                    <code class="text--primary">(@lang('Keep 0 for no charge cap'))</code>
                                </label>
                                <div class="input-group ">
                                    <input type="number" step="any" class="form-control" name="charge_cap" value="{{ getAmount(gs('charge_cap')) }}"
                                        required>
                                    <span class="input-group-text">{{ __(gs('cur_text')) }}</span>
                                </div>
                            </div>

                            <div class="form-group col-md-4">
                                <label>
                                    @lang('Fixed Charge')
                                    <code class="text--primary">
                                        (@lang('If the amount doesn\'t match any range'))
                                    </code>
                                </label>
                                <div class="input-group ">
                                    <input type="number" step="any" class="form-control" name="fixed_charge"
                                        value="{{ getAmount(gs('fixed_charge')) }}" required>
                                    <span class="input-group-text">{{ __(gs('cur_text')) }}</span>
                                </div>
                            </div>

                            <div class="form-group col-md-4">
                                <label>
                                    @lang('Percent Charge')
                                    <code class="text--primary">
                                        (@lang('If the amount doesn\'t match any range'))
                                    </code>
                                </label>
                                <div class="input-group ">
                                    <input type="number" step="any" class="form-control" name="percent_charge"
                                        value="{{ getAmount(gs('percent_charge')) }}" required>
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn--primary h-45 w-100">@lang('Update')</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive--sm table-responsive">
                        <table class="table table--light style--two custom-data-table">
                            <thead>
                                <tr>
                                    <th>@lang('SL')</th>
                                    <th>@lang('Minimum')</th>
                                    <th>@lang('Maximum')</th>
                                    <th>@lang('Fixed Charge')</th>
                                    <th>@lang('Percent Charge')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($charges as $charge)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            {{ showAmount($charge->minimum) }}

                                        </td>
                                        <td>
                                            {{ showAmount($charge->maximum) }}

                                        </td>
                                        <td>
                                            {{ showAmount($charge->fixed_charge) }}

                                        </td>
                                        <td>
                                            {{ showAmount($charge->percent_charge) }}%
                                        </td>
                                        <td>
                                            <div class="button--group">

                                                <button type="button" class="btn btn-sm btn-outline--primary cuModalBtn"
                                                    data-resource="{{ $charge }}" data-modal_title="@lang('Update Charge Range')" data-has_status="1">
                                                    <i class="la la-pencil"></i>@lang('Edit')
                                                </button>

                                                <button type="button" class="btn btn-sm btn-outline--danger confirmationBtn"
                                                    data-question="@lang('Are you sure to remove this charge range?')" data-action="{{ route('admin.charge.remove', $charge->id) }}">
                                                    <i class="las la-trash"></i>
                                                    @lang('Remove')
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Update Modal -->
    <div class="modal fade" id="cuModal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="{{ route('admin.charge.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>@lang('Minimum Amount') </label>
                            <div class="input-group ">
                                <input type="number" step="any" class="form-control" name="minimum" required>
                                <span class="input-group-text">{{ __(gs('cur_text')) }}</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>@lang('Maximum Amount') </label>
                            <div class="input-group ">
                                <input type="number" step="any" class="form-control" name="maximum" required>
                                <span class="input-group-text">{{ __(gs('cur_text')) }}</span>

                            </div>
                        </div>
                        <div class="form-group">
                            <label>@lang('Fixed Charge') </label>
                            <div class="input-group ">
                                <input type="number" step="any" class="form-control" name="fixed_charge" required>
                                <span class="input-group-text">{{ __(gs('cur_text')) }}</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>@lang('Percent Charge') </label>
                            <div class="input-group ">
                                <input type="number" step="0.01" class="form-control" name="percent_charge" required>
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn--primary w-100 h-45">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    <div class="d-inline">
        <div class="input-group ">
            <input type="text" name="search_table" class="form-control bg--white" placeholder="@lang('Search')...">
            <button class="btn btn--primary input-group-text"><i class="fa fa-search"></i></button>
        </div>
    </div>
    <!-- Modal Trigger Button -->
    <button type="button" class="btn btn-sm btn-outline--primary me-2 h-45 cuModalBtn" data-modal_title="@lang('Add Charge Range')">
        <i class="las la-plus"></i>
        @lang('Add New')
    </button>
@endpush
