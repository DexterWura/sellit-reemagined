@extends('admin.layouts.app')

@section('title')
    Domain Verifications
@endsection

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="row align-items-end">
            <div class="col-lg-8">
                <div class="page-header-title">
                    <i class="ik ik-shield bg-blue"></i>
                    <div class="d-inline">
                        <h5>Domain Verifications</h5>
                        <span>Manage domain ownership verifications</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="ik ik-home"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Verifications</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3>All Verifications</h3>
                    <div class="card-header-right">
                        <a href="{{ route('admin.verification.settings') }}" class="btn btn-sm btn-outline-primary">
                            <i class="ik ik-settings"></i> Settings
                        </a>
                        <a href="{{ route('admin.verification.statistics') }}" class="btn btn-sm btn-outline-info">
                            <i class="ik ik-bar-chart"></i> Statistics
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <form method="GET">
                                <select name="status" class="form-control" onchange="this.form.submit()">
                                    <option value="">All Statuses</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Verified</option>
                                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                                </select>
                            </form>
                        </div>
                        <div class="col-md-3">
                            <form method="GET">
                                <select name="method" class="form-control" onchange="this.form.submit()">
                                    <option value="">All Methods</option>
                                    <option value="file" {{ request('method') == 'file' ? 'selected' : '' }}>File Upload</option>
                                    <option value="dns" {{ request('method') == 'dns' ? 'selected' : '' }}>DNS Record</option>
                                </select>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <form method="GET">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" placeholder="Search by domain or user..." value="{{ request('search') }}">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="submit">
                                            <i class="ik ik-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Verifications Table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Domain</th>
                                    <th>User</th>
                                    <th>Listing</th>
                                    <th>Method</th>
                                    <th>Status</th>
                                    <th>Attempts</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($verifications as $verification)
                                <tr>
                                    <td>
                                        <strong>{{ $verification->domain }}</strong>
                                        @if($verification->listing)
                                            <br><small class="text-muted">{{ $verification->listing->title }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $verification->user->username }}</td>
                                    <td>
                                        @if($verification->listing)
                                            <a href="{{ route('admin.listing.details', $verification->listing->id) }}" target="_blank">
                                                {{ Str::limit($verification->listing->title, 30) }}
                                            </a>
                                        @else
                                            <span class="badge badge-secondary">No Listing</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $verification->verification_method == 'file' ? 'primary' : 'info' }}">
                                            {{ $verification->verification_method == 'file' ? 'File Upload' : 'DNS Record' }}
                                        </span>
                                    </td>
                                    <td>
                                        @switch($verification->status)
                                            @case('pending')
                                                <span class="badge badge-warning">Pending</span>
                                                @break
                                            @case('verified')
                                                <span class="badge badge-success">Verified</span>
                                                @break
                                            @case('failed')
                                                <span class="badge badge-danger">Failed</span>
                                                @break
                                            @case('expired')
                                                <span class="badge badge-secondary">Expired</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td>{{ $verification->attempt_count }}</td>
                                    <td>{{ $verification->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.verification.show', $verification->id) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="ik ik-eye"></i>
                                            </a>
                                            @if($verification->status === 'pending')
                                            <form action="{{ route('admin.verification.expire', $verification->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('POST')
                                                <button type="submit" class="btn btn-sm btn-outline-warning" onclick="return confirm('Expire this verification?')">
                                                    <i class="ik ik-x"></i>
                                                </button>
                                            </form>
                                            @endif
                                            <form action="{{ route('admin.verification.delete', $verification->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this verification?')">
                                                    <i class="ik ik-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">No verifications found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    {{ $verifications->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
