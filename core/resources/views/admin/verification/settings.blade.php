@extends('admin.layouts.app')

@section('title')
    Domain Verification Settings
@endsection

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="row align-items-end">
            <div class="col-lg-8">
                <div class="page-header-title">
                    <i class="ik ik-shield bg-blue"></i>
                    <div class="d-inline">
                        <h5>Domain Verification Settings</h5>
                        <span>Configure domain ownership verification requirements</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="ik ik-home"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Verification Settings</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3>Verification Configuration</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.verification.settings.update') }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label class="form-label">Require Domain Verification</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="require_verification" name="require_verification" value="1" {{ $settings->require_verification ? 'checked' : '' }}>
                                <label class="form-check-label" for="require_verification">
                                    When enabled, domain ownership verification becomes mandatory during listing creation
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Allowed Verification Methods</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="method_file" name="allowed_methods[]" value="file" {{ in_array('file', $settings->allowed_methods) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="method_file">
                                            <strong>File Upload</strong><br>
                                            <small class="text-muted">Users upload a verification file to their domain</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="method_dns" name="allowed_methods[]" value="dns" {{ in_array('dns', $settings->allowed_methods) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="method_dns">
                                            <strong>DNS Record</strong><br>
                                            <small class="text-muted">Users add a TXT record to their domain DNS</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="max_verification_attempts" class="form-label">Maximum Verification Attempts</label>
                            <input type="number" class="form-control" id="max_verification_attempts" name="max_verification_attempts" value="{{ $settings->max_verification_attempts }}" min="1" max="20">
                            <small class="form-text text-muted">Number of verification attempts allowed before marking as failed</small>
                        </div>

                        <div class="form-group">
                            <label for="verification_timeout_seconds" class="form-label">Verification Timeout (Seconds)</label>
                            <input type="number" class="form-control" id="verification_timeout_seconds" name="verification_timeout_seconds" value="{{ $settings->verification_timeout_seconds }}" min="30" max="3600">
                            <small class="form-text text-muted">Time allowed for verification completion</small>
                        </div>

                        <button type="submit" class="btn btn-primary">Save Settings</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    'use strict';

    // Ensure at least one verification method is selected
    $('form').on('submit', function(e) {
        var checkedMethods = $('input[name="allowed_methods[]"]:checked').length;
        if (checkedMethods === 0) {
            e.preventDefault();
            iziToast.error({
                title: 'Error',
                message: 'At least one verification method must be selected'
            });
            return false;
        }
    });
</script>
@endpush
