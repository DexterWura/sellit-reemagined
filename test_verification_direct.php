<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Complete Domain Verification Test</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
            background: #f8f9fa;
        }
        .container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            font-size: 2.2em;
            margin-bottom: 10px;
        }
        .content {
            padding: 30px;
        }
        .step {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            background: white;
        }
        .step h3 {
            color: #495057;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .step-number {
            background: #007bff;
            color: white;
            padding: 4px 10px;
            border-radius: 50%;
            font-size: 0.9em;
            font-weight: bold;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            font-weight: 500;
            margin-bottom: 5px;
            color: #495057;
        }
        input, select, button {
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 14px;
            width: 100%;
        }
        button {
            background: #007bff;
            color: white;
            cursor: pointer;
            border: none;
            font-weight: 500;
        }
        button:hover { background: #0056b3; }
        button:disabled { background: #6c757d; cursor: not-allowed; }
        .verification-result {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 15px;
            margin: 15px 0;
        }
        .success { border-color: #28a745; background-color: #d4edda; }
        .error { border-color: #dc3545; background-color: #f8d7da; }
        .info { border-color: #17a2b8; background-color: #d1ecf1; }
        .warning { border-color: #ffc107; background-color: #fff3cd; }
        .download-link {
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.1em;
        }
        .download-link:hover { text-decoration: underline; }
        .dns-instructions {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            font-family: monospace;
            margin: 10px 0;
        }
        .verification-status {
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
            font-weight: bold;
        }
        .verified { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .failed { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .pending { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
        .hidden { display: none; }
        .progress-bar {
            background: #e9ecef;
            height: 6px;
            border-radius: 3px;
            overflow: hidden;
            margin: 20px 0;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #28a745, #20c997);
            transition: width 0.3s ease;
            width: 0%;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 Complete Domain Verification Test</h1>
            <p>Test the full verification flow: Generate → Configure → Verify</p>
        </div>

        <div class="progress-bar">
            <div class="progress-fill" id="progressFill"></div>
        </div>

        <div class="content">
            <div class="step" id="step1">
                <h3><span class="step-number">1</span> Enter Domain & Select Method</h3>
                <div class="form-group">
                    <label for="domain">Domain to Verify:</label>
                    <input type="text" id="domain" placeholder="e.g., google.com" value="google.com">
                </div>
                <div class="form-group">
                    <label for="method">Verification Method:</label>
                    <select id="method">
                        <option value="txt_file">TXT File Upload</option>
                        <option value="dns_record">DNS TXT Record</option>
                    </select>
                </div>
                <button id="generateBtn" onclick="generateVerification()">Generate Verification Data</button>
            </div>

            <div class="step hidden" id="step2">
                <h3><span class="step-number">2</span> Configure Your Domain</h3>
                <div id="configurationContent"></div>
            </div>

            <div class="step hidden" id="step3">
                <h3><span class="step-number">3</span> Verify Configuration</h3>
                <p>Click the button below to verify that your domain is properly configured.</p>
                <button id="verifyBtn" onclick="verifyDomain()">Verify Domain Ownership</button>
                <div id="verificationStatus"></div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        var verificationData = null;
        var csrfToken = '';

        // Get CSRF token on page load
        function getCsrfToken() {
            // Try meta tag first
            var tokenMeta = document.querySelector('meta[name="csrf-token"]');
            if (tokenMeta && tokenMeta.getAttribute('content')) {
                csrfToken = tokenMeta.getAttribute('content');
                console.log('CSRF token from meta tag:', csrfToken.substring(0, 10) + '...');
                return csrfToken;
            }

            // Try cookies
            var cookies = document.cookie.split(';');
            for (var i = 0; i < cookies.length; i++) {
                var cookie = cookies[i].trim();
                if (cookie.startsWith('XSRF-TOKEN=')) {
                    csrfToken = decodeURIComponent(cookie.substring(12));
                    console.log('CSRF token from cookie:', csrfToken.substring(0, 10) + '...');
                    return csrfToken;
                }
            }

            // Try to find in DOM
            var tokenInput = document.querySelector('input[name="_token"]');
            if (tokenInput) {
                csrfToken = tokenInput.value;
                console.log('CSRF token from input:', csrfToken.substring(0, 10) + '...');
                return csrfToken;
            }

            console.warn('No CSRF token found');
            return '';
        }

        // Initialize CSRF token
        csrfToken = getCsrfToken();

        function fetchCsrfToken(callback) {
            if (csrfToken) {
                callback();
                return;
            }

            console.log('Fetching fresh CSRF token...');
            $.ajax({
                url: window.location.origin,
                method: 'GET',
                success: function(response) {
                    // Look for token in response
                    var tokenMatch = response.match(/name="csrf-token" content="([^"]+)"/);
                    if (tokenMatch) {
                        csrfToken = tokenMatch[1];
                        console.log('Fetched CSRF token:', csrfToken.substring(0, 10) + '...');
                    }
                    callback();
                },
                error: function() {
                    console.warn('Could not fetch CSRF token');
                    csrfToken = 'fallback_token_' + Date.now();
                    callback();
                }
            });
        }

        function updateProgress(step) {
            var progress = (step / 3) * 100;
            document.getElementById('progressFill').style.width = progress + '%';
        }

        function showStep(stepNumber) {
            document.getElementById('step1').classList.add('hidden');
            document.getElementById('step2').classList.add('hidden');
            document.getElementById('step3').classList.add('hidden');

            document.getElementById('step' + stepNumber).classList.remove('hidden');
            updateProgress(stepNumber);
        }

        function generateVerification() {
            var domain = document.getElementById('domain').value.trim();
            var method = document.getElementById('method').value;

            if (!domain) {
                alert('Please enter a domain');
                return;
            }

            document.getElementById('generateBtn').disabled = true;
            document.getElementById('generateBtn').textContent = 'Generating...';

            console.log('Generating verification for:', {domain: domain, method: method});

            // Ensure we have a CSRF token
            fetchCsrfToken(function() {
                $.ajax({
                url: 'user/verification/generate',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                data: {
                    _token: csrfToken,
                    domain: domain,
                    method: method
                },
                success: function(response) {
                    console.log('Generation success:', response);

                    if (response.success) {
                        verificationData = {
                            domain: response.domain || domain,
                            method: response.method || method,
                            token: response.token,
                            filename: response.filename,
                            dns_name: response.dns_name,
                            expected_url: response.expected_url
                        };

                        showConfigurationStep();
                    } else {
                        alert('Generation failed: ' + (response.message || 'Unknown error'));
                        document.getElementById('generateBtn').disabled = false;
                        document.getElementById('generateBtn').textContent = 'Generate Verification Data';
                    }
                },
                error: function(xhr) {
                    console.error('Generation error:', xhr);
                    alert('Generation failed. Status: ' + xhr.status + '\nCheck console for details.');
                    document.getElementById('generateBtn').disabled = false;
                    document.getElementById('generateBtn').textContent = 'Generate Verification Data';
                }
            });
            }); // Close fetchCsrfToken callback
        }

        function showConfigurationStep() {
            var content = document.getElementById('configurationContent');

            if (verificationData.method === 'txt_file') {
                content.innerHTML = `
                    <div class="verification-result success">
                        <h4>📄 TXT File Method</h4>
                        <p>Download the verification file and upload it to your website root directory.</p>

                        <div style="margin: 15px 0;">
                            <strong>File Details:</strong><br>
                            Name: ${verificationData.filename}<br>
                            Content: ${verificationData.token}<br>
                            Upload to: ${verificationData.expected_url}
                        </div>

                        <p><a href="user/verification/download?token=${encodeURIComponent(verificationData.token)}&filename=${encodeURIComponent(verificationData.filename)}&domain=${encodeURIComponent(verificationData.domain)}" class="download-link" target="_blank">📥 Download Verification File</a></p>

                        <div style="margin-top: 15px; padding: 10px; background: #e7f3ff; border-radius: 4px;">
                            <strong>Instructions:</strong>
                            <ol>
                                <li>Click "Download Verification File" above</li>
                                <li>Upload the downloaded file to your website root</li>
                                <li>Ensure it's accessible at: <code>${verificationData.expected_url}</code></li>
                                <li>Click "Continue to Verification" below</li>
                            </ol>
                        </div>

                        <button onclick="showVerificationStep()" style="margin-top: 15px;">Continue to Verification</button>
                    </div>
                `;
            } else {
                content.innerHTML = `
                    <div class="verification-result success">
                        <h4>🌐 DNS Record Method</h4>
                        <p>Add the following TXT record to your domain's DNS settings.</p>

                        <div class="dns-instructions">
                            <strong>Type:</strong> TXT<br>
                            <strong>Name/Host:</strong> ${verificationData.dns_name}<br>
                            <strong>Value/Content:</strong> ${verificationData.token}
                        </div>

                        <div style="margin-top: 15px; padding: 10px; background: #e7f3ff; border-radius: 4px;">
                            <strong>Instructions:</strong>
                            <ol>
                                <li>Go to your domain registrar's DNS settings</li>
                                <li>Add a new TXT record with the details above</li>
                                <li>Wait 5-30 minutes for DNS propagation</li>
                                <li>Click "Continue to Verification" below</li>
                            </ol>
                        </div>

                        <button onclick="showVerificationStep()" style="margin-top: 15px;">Continue to Verification</button>
                    </div>
                `;
            }

            showStep(2);
        }

        function showVerificationStep() {
            showStep(3);
        }

        function verifyDomain() {
            if (!verificationData) {
                alert('No verification data. Please start over.');
                return;
            }

            document.getElementById('verifyBtn').disabled = true;
            document.getElementById('verifyBtn').textContent = 'Verifying...';

            document.getElementById('verificationStatus').innerHTML = '<div class="verification-status pending">🔍 Checking domain configuration...</div>';

            console.log('Verifying domain:', verificationData);

            // Ensure we have a CSRF token
            fetchCsrfToken(function() {
                $.ajax({
                url: 'user/verification/verify',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                data: {
                    _token: csrfToken,
                    domain: verificationData.domain,
                    method: verificationData.method,
                    token: verificationData.token,
                    filename: verificationData.filename,
                    dns_name: verificationData.dns_name
                },
                success: function(response) {
                    console.log('Verification response:', response);

                    document.getElementById('verifyBtn').disabled = false;
                    document.getElementById('verifyBtn').textContent = 'Verify Domain Ownership';

                    if (response.success) {
                        document.getElementById('verificationStatus').innerHTML = `
                            <div class="verification-status verified">
                                ✅ Domain verification successful!<br>
                                Your domain ownership has been confirmed.
                            </div>
                        `;
                    } else {
                        document.getElementById('verificationStatus').innerHTML = `
                            <div class="verification-status failed">
                                ❌ Domain verification failed<br>
                                ${response.message || 'Please check your configuration and try again.'}
                            </div>
                        `;
                    }
                },
                error: function(xhr) {
                    console.error('Verification error:', xhr);
                    document.getElementById('verifyBtn').disabled = false;
                    document.getElementById('verifyBtn').textContent = 'Verify Domain Ownership';

                    document.getElementById('verificationStatus').innerHTML = `
                        <div class="verification-status failed">
                            ❌ Verification error<br>
                            Status: ${xhr.status}<br>
                            Check console for details.
                        </div>
                    `;
                }
            });
            }); // Close fetchCsrfToken callback
        }

        // Initialize
        updateProgress(1);
    </script>
</body>
</html>
