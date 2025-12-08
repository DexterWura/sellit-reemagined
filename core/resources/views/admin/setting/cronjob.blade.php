@extends('admin.layouts.app')
@section('panel')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="las la-clock me-2"></i>@lang('Cronjob Management')
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.setting.cronjob.update') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="alert alert-info">
                                <i class="las la-info-circle"></i>
                                <strong>@lang('Cronjob Setup')</strong><br>
                                @lang('Add this to your server crontab to run every minute:')<br>
                                <code>* * * * * cd {{ base_path() }} && php artisan schedule:run >> /dev/null 2>&1</code>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label class="form-label">@lang('Auction Processing')</label>
                                <div class="input-group">
                                    <input type="hidden" name="auction_processing_enabled" value="0">
                                    <input type="checkbox" name="auction_processing_enabled" value="1" 
                                           @checked($cronjobSettings['auction_processing_enabled']) 
                                           data-bs-toggle="toggle" 
                                           data-onstyle="success" 
                                           data-offstyle="danger"
                                           data-on="@lang('Enabled')" 
                                           data-off="@lang('Disabled')">
                                </div>
                                <small class="form-text text-muted">
                                    @lang('Enable automatic processing of ended auctions')
                                </small>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="form-group">
                                <label class="form-label">@lang('Processing Interval (Minutes)')</label>
                                <input type="number" class="form-control" name="auction_processing_interval" 
                                       value="{{ $cronjobSettings['auction_processing_interval'] }}" 
                                       min="1" max="60" required>
                                <small class="form-text text-muted">
                                    @lang('How often to check for ended auctions (1-60 minutes)')
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-lg-12">
                            <button type="submit" class="btn btn--primary">
                                <i class="las la-save"></i> @lang('Save Settings')
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Manual Processing Card -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="las la-play-circle me-2"></i>@lang('Manual Processing')
                </h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-lg-12">
                        <div class="alert alert-warning">
                            <i class="las la-exclamation-triangle"></i>
                            <strong>@lang('Pending Auctions')</strong>: 
                            <span id="pendingCount">{{ $pendingAuctions }}</span> @lang('auction(s) waiting to be processed')
                        </div>
                    </div>
                </div>

                <form id="runProcessingForm" action="{{ route('admin.setting.cronjob.run') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label class="form-label">@lang('Check Auctions Ending Within (Minutes)')</label>
                                <input type="number" class="form-control" name="minutes" value="60" min="1" max="1440" required>
                                <small class="form-text text-muted">
                                    @lang('Process auctions ending within this time window')
                                </small>
                            </div>
                        </div>
                        <div class="col-lg-6 d-flex align-items-end">
                            <button type="submit" class="btn btn--success btn-lg w-100" id="runBtn">
                                <i class="las la-play"></i> @lang('Run Auction Processing Now')
                            </button>
                        </div>
                    </div>
                </form>

                @if($cronjobSettings['last_auction_processing_run'])
                <div class="row mt-3">
                    <div class="col-lg-12">
                        <small class="text-muted">
                            <i class="las la-clock"></i> 
                            @lang('Last Run'): {{ showDateTime($cronjobSettings['last_auction_processing_run'], 'd M Y, h:i A') }}
                            ({{ diffForHumans($cronjobSettings['last_auction_processing_run']) }})
                        </small>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Logs Card -->
        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="las la-file-alt me-2"></i>@lang('Processing Logs')
                </h5>
                @if($logExists)
                <form action="{{ route('admin.setting.cronjob.clear.logs') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn--danger" onclick="return confirm('Are you sure you want to clear the logs?')">
                        <i class="las la-trash"></i> @lang('Clear Logs')
                    </button>
                </form>
                @endif
            </div>
            <div class="card-body">
                @if($logExists && $logSize > 0)
                    <div class="mb-3">
                        <small class="text-muted">
                            <i class="las la-info-circle"></i>
                            @lang('Log File Size'): {{ number_format($logSize / 1024, 2) }} KB
                            @if($logLastModified)
                            | @lang('Last Modified'): {{ showDateTime($logLastModified, 'd M Y, h:i A') }}
                            @endif
                        </small>
                    </div>
                    <div class="log-container" style="max-height: 400px; overflow-y: auto; background: #1e1e1e; color: #d4d4d4; padding: 15px; border-radius: 5px; font-family: 'Courier New', monospace; font-size: 12px;">
                        @if(count($recentLogs) > 0)
                            @foreach($recentLogs as $log)
                                <div class="log-line">{{ $log }}</div>
                            @endforeach
                        @else
                            <div class="text-muted">@lang('No log entries found')</div>
                        @endif
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="las la-file-alt display-4 text-muted mb-3"></i>
                        <p class="text-muted">@lang('No log file found. Logs will appear here after running the processing command.')</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    (function($) {
        "use strict";
        
        // Auto-refresh pending count
        function refreshStatus() {
            $.ajax({
                url: "{{ route('admin.setting.cronjob.status') }}",
                type: 'GET',
                success: function(response) {
                    $('#pendingCount').text(response.pending_auctions);
                }
            });
        }

        // Refresh every 30 seconds
        setInterval(refreshStatus, 30000);

        // Handle form submission
        $('#runProcessingForm').on('submit', function(e) {
            e.preventDefault();
            const $btn = $('#runBtn');
            const originalText = $btn.html();
            
            $btn.prop('disabled', true).html('<i class="las la-spinner fa-spin"></i> @lang("Processing...")');
            
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
                        notify('success', response.message || '@lang("Processing completed successfully")');
                        refreshStatus();
                        // Reload page after 2 seconds to show updated logs
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        notify('error', response.message || '@lang("Processing failed")');
                    }
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || '@lang("An error occurred")';
                    notify('error', message);
                },
                complete: function() {
                    $btn.prop('disabled', false).html(originalText);
                }
            });
        });
    })(jQuery);
</script>
@endpush

@push('style')
<style>
    .log-container {
        font-size: 12px;
        line-height: 1.6;
    }
    .log-line {
        margin-bottom: 2px;
        word-wrap: break-word;
    }
    .log-line:last-child {
        margin-bottom: 0;
    }
</style>
@endpush

