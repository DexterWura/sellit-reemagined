@extends('admin.layouts.app')

@section('title')
    Verification Statistics
@endsection

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="row align-items-end">
            <div class="col-lg-8">
                <div class="page-header-title">
                    <i class="ik ik-bar-chart bg-blue"></i>
                    <div class="d-inline">
                        <h5>Verification Statistics</h5>
                        <span>Overview of domain verification activity</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="ik ik-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.verification.index') }}">Verifications</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Statistics</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <h6 class="text-muted mb-1">Total Verifications</h6>
                            <h4 class="mb-0">{{ number_format($stats['total_verifications']) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <h6 class="text-muted mb-1">Verified Domains</h6>
                            <h4 class="mb-0 text-success">{{ number_format($stats['verified_domains']) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <h6 class="text-muted mb-1">Pending</h6>
                            <h4 class="mb-0 text-warning">{{ number_format($stats['pending_verifications']) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <h6 class="text-muted mb-1">Success Rate</h6>
                            <h4 class="mb-0">
                                @if($stats['total_verifications'] > 0)
                                    {{ number_format(($stats['verified_domains'] / $stats['total_verifications']) * 100, 1) }}%
                                @else
                                    0%
                                @endif
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Method Statistics -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3>Verification Methods</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="text-center">
                                <h4 class="text-primary">{{ $stats['file_method_count'] }}</h4>
                                <p class="text-muted">File Upload</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <h4 class="text-info">{{ $stats['dns_method_count'] }}</h4>
                                <p class="text-muted">DNS Record</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3>Attempt Statistics</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-4">
                            <div class="text-center">
                                <h4>{{ number_format($stats['avg_attempts'], 1) }}</h4>
                                <p class="text-muted">Avg Attempts</p>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="text-center">
                                <h4 class="text-success">{{ $stats['successful_attempts'] }}</h4>
                                <p class="text-muted">Successful</p>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="text-center">
                                <h4 class="text-danger">{{ $stats['failed_attempts'] }}</h4>
                                <p class="text-muted">Failed</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3>Recent Verifications</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Domain</th>
                                    <th>Status</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentVerifications as $verification)
                                <tr>
                                    <td>{{ Str::limit($verification->domain, 25) }}</td>
                                    <td>
                                        <span class="badge badge-{{ $verification->status == 'verified' ? 'success' : 'warning' }}">
                                            {{ ucfirst($verification->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $verification->created_at->diffForHumans() }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">No recent verifications</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3>Recent Attempts</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Domain</th>
                                    <th>Method</th>
                                    <th>Result</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentAttempts as $attempt)
                                <tr>
                                    <td>{{ Str::limit($attempt->domainVerification->domain ?? 'N/A', 20) }}</td>
                                    <td>{{ $attempt->method }}</td>
                                    <td>
                                        @if($attempt->error_message)
                                            <span class="text-danger">Failed</span>
                                        @else
                                            <span class="text-success">Success</span>
                                        @endif
                                    </td>
                                    <td>{{ $attempt->attempted_at->diffForHumans() }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No recent attempts</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Actions -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3>Maintenance Actions</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <form action="{{ route('admin.verification.cleanup') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-warning btn-block" onclick="return confirm('Run cleanup operation?')">
                                    <i class="ik ik-trash"></i> Run Cleanup
                                </button>
                                <small class="form-text text-muted">Expire old verifications and clean up attempt logs</small>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <a href="{{ route('admin.verification.index') }}" class="btn btn-primary btn-block">
                                <i class="ik ik-list"></i> View All Verifications
                            </a>
                            <small class="form-text text-muted">Manage individual verifications</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
