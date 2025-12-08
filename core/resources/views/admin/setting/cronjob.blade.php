@extends('admin.layouts.app')
@section('panel')
<div class="row">
    <div class="col-lg-12">
        <!-- Cronjob Setup Card -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="las la-cog me-2"></i>@lang('Cronjob Setup')
                </h5>
            </div>
            <div class="card-body">
                @if(!$cronJobActive)
                <div class="alert alert-danger mb-4">
                    <div class="d-flex align-items-center">
                        <i class="las la-exclamation-triangle fs-3 me-3"></i>
                        <div>
                            <strong>@lang('Cron Job Not Detected!')</strong><br>
                            @lang('The system cannot detect an active cron job. Please set up the cron job in your cPanel to ensure automated tasks run properly.')
                        </div>
                    </div>
                </div>
                @else
                <div class="alert alert-success mb-4">
                    <div class="d-flex align-items-center">
                        <i class="las la-check-circle fs-3 me-3"></i>
                        <div>
                            <strong>@lang('Cron Job Active')</strong><br>
                            @if($cronJobLastRun)
                                @lang('Last detected run'): {{ showDateTime($cronJobLastRun, 'd M Y, h:i A') }} ({{ diffForHumans($cronJobLastRun) }})
                            @else
                                @lang('Cron job appears to be running')
                            @endif
                        </div>
                    </div>
                </div>
                @endif

                <div class="mb-4">
                    <label class="form-label fw-bold">@lang('cPanel Cron Job Command')</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="cronCommand" value="{{ $cronCommand }}" readonly>
                        <button class="btn btn--primary" type="button" id="copyCronCommand" onclick="copyCronCommand()">
                            <i class="las la-copy"></i> @lang('Copy')
                        </button>
                    </div>
                    <small class="form-text text-muted mt-2">
                        <i class="las la-info-circle"></i>
                        @lang('For cPanel: Copy this command and paste it in the "Command" field. Set the schedule to run every minute (* * * * *). For standard cron: Use the full command below.')
                    </small>
                    
                    <label class="form-label fw-bold mt-3">@lang('Full Cron Command (For Standard Cron)')</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="cronCommandFull" value="{{ $cronCommandFull }}" readonly>
                        <button class="btn btn--primary" type="button" id="copyCronCommandFull" onclick="copyCronCommandFull()">
                            <i class="las la-copy"></i> @lang('Copy')
                        </button>
                    </div>
                    <small class="form-text text-muted mt-2">
                        <i class="las la-info-circle"></i>
                        @lang('Use this full command if you\'re adding it directly to crontab (includes schedule).')
                    </small>
                </div>
            </div>
        </div>

        <!-- Settings Card -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="las la-sliders-h me-2"></i>@lang('Auction Processing Settings')
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.setting.cronjob.update') }}" method="POST">
                    @csrf
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
                        <p class="text-muted">@lang('No log file found. Logs will appear here after running the processing command or when cron job executes.')</p>
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
        
        // Copy cron command function (curl)
        function copyCronCommand() {
            const commandInput = document.getElementById('cronCommand');
            commandInput.select();
            commandInput.setSelectionRange(0, 99999); // For mobile devices
            
            try {
                document.execCommand('copy');
                const btn = document.getElementById('copyCronCommand');
                const originalHtml = btn.innerHTML;
                btn.innerHTML = '<i class="las la-check"></i> @lang("Copied!")';
                btn.classList.add('btn--success');
                btn.classList.remove('btn--primary');
                
                setTimeout(function() {
                    btn.innerHTML = originalHtml;
                    btn.classList.remove('btn--success');
                    btn.classList.add('btn--primary');
                }, 2000);
            } catch (err) {
                alert('@lang("Failed to copy command. Please copy manually.")');
            }
        }
        
        // Copy cron command function (full)
        function copyCronCommandFull() {
            const commandInput = document.getElementById('cronCommandFull');
            commandInput.select();
            commandInput.setSelectionRange(0, 99999);
            
            try {
                document.execCommand('copy');
                const btn = document.getElementById('copyCronCommandFull');
                const originalHtml = btn.innerHTML;
                btn.innerHTML = '<i class="las la-check"></i> @lang("Copied!")';
                btn.classList.add('btn--success');
                btn.classList.remove('btn--primary');
                
                setTimeout(function() {
                    btn.innerHTML = originalHtml;
                    btn.classList.remove('btn--success');
                    btn.classList.add('btn--primary');
                }, 2000);
            } catch (err) {
                alert('@lang("Failed to copy command. Please copy manually.")');
            }
        }
        
        // Make functions global
        window.copyCronCommand = copyCronCommand;
        window.copyCronCommandFull = copyCronCommandFull;
        
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
    #cronCommand {
        font-family: 'Courier New', monospace;
        font-size: 13px;
    }
</style>
@endpush
