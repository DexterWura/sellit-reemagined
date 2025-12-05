@extends('admin.layouts.app')

@section('title')
    Verification Debug
@endsection

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="row align-items-end">
            <div class="col-lg-8">
                <div class="page-header-title">
                    <i class="ik ik-settings bg-blue"></i>
                    <div class="d-inline">
                        <h5>Domain Verification Debug</h5>
                        <span>Check if admin settings are communicating with verification system</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="ik ik-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.verification.index') }}">Verification</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Debug</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3>Marketplace Settings (Controlled by Admin/Marketplace/Config)</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <td><strong>Require Domain Verification:</strong></td>
                                    <td>
                                        <span class="badge {{ $debug['marketplace_settings']['require_domain_verification'] ? 'bg-success' : 'bg-danger' }}">
                                            {{ $debug['marketplace_settings']['require_domain_verification'] ? 'ENABLED' : 'DISABLED' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Require Website Verification:</strong></td>
                                    <td>
                                        <span class="badge {{ $debug['marketplace_settings']['require_website_verification'] ? 'bg-success' : 'bg-danger' }}">
                                            {{ $debug['marketplace_settings']['require_website_verification'] ? 'ENABLED' : 'DISABLED' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Require Social Media Verification:</strong></td>
                                    <td>
                                        <span class="badge {{ $debug['marketplace_settings']['require_social_media_verification'] ? 'bg-success' : 'bg-danger' }}">
                                            {{ $debug['marketplace_settings']['require_social_media_verification'] ? 'ENABLED' : 'DISABLED' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Allowed Methods:</strong></td>
                                    <td>
                                        @if($debug['marketplace_settings']['domain_verification_methods'])
                                            @foreach($debug['marketplace_settings']['domain_verification_methods'] as $method)
                                                <span class="badge bg-info">{{ $method }}</span>
                                            @endforeach
                                        @else
                                            <span class="text-muted">None</span>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3>MarketplaceSetting Static Methods</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <td><strong>requireDomainVerification():</strong></td>
                                    <td>
                                        <span class="badge {{ $debug['marketplace_setting_methods']['requireDomainVerification'] ? 'bg-success' : 'bg-danger' }}">
                                            {{ $debug['marketplace_setting_methods']['requireDomainVerification'] ? 'TRUE' : 'FALSE' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>requireWebsiteVerification():</strong></td>
                                    <td>
                                        <span class="badge {{ $debug['marketplace_setting_methods']['requireWebsiteVerification'] ? 'bg-success' : 'bg-danger' }}">
                                            {{ $debug['marketplace_setting_methods']['requireWebsiteVerification'] ? 'TRUE' : 'FALSE' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>requireSocialMediaVerification():</strong></td>
                                    <td>
                                        <span class="badge {{ $debug['marketplace_setting_methods']['requireSocialMediaVerification'] ? 'bg-success' : 'bg-danger' }}">
                                            {{ $debug['marketplace_setting_methods']['requireSocialMediaVerification'] ? 'TRUE' : 'FALSE' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>getDomainVerificationMethods():</strong></td>
                                    <td>
                                        @if($debug['marketplace_setting_methods']['getDomainVerificationMethods'])
                                            @foreach($debug['marketplace_setting_methods']['getDomainVerificationMethods'] as $method)
                                                <span class="badge bg-info">{{ $method }}</span>
                                            @endforeach
                                        @else
                                            <span class="text-muted">Empty array</span>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3>Verification Statistics</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h3>{{ number_format($debug['total_verifications']) }}</h3>
                                    <p class="mb-0">Total Verifications</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h3>{{ number_format($debug['pending_verifications']) }}</h3>
                                    <p class="mb-0">Pending</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h3>{{ number_format($debug['total_verifications'] - $debug['pending_verifications']) }}</h3>
                                    <p class="mb-0">Completed</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center">
                                    <h3>{{ number_format($debug['failed_verifications']) }}</h3>
                                    <p class="mb-0">Failed</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3>Debug Information</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h5><i class="ik ik-info"></i> How to Use This Debug Page</h5>
                        <ul class="mb-0">
                            <li><strong>Marketplace Settings</strong> show verification requirements configured in Admin → Marketplace → Config</li>
                            <li><strong>MarketplaceSetting Methods</strong> show if the code can read the settings correctly</li>
                            <li><strong>Statistics</strong> show actual verification activity in your system</li>
                            <li><strong>All verification is controlled by the marketplace config page</strong></li>
                        </ul>
                    </div>

                    <div class="alert alert-warning">
                        <h5><i class="ik ik-alert-triangle"></i> Common Issues</h5>
                        <ul class="mb-0">
                            <li>If settings show disabled but should be enabled, check Admin → Marketplace → Config</li>
                            <li>If static methods return wrong values, cache may need clearing</li>
                            <li>Verification is automatically enabled by default for new installations</li>
                            <li>Make sure to run database migrations if tables are missing</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
