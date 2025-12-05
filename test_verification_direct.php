<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Direct Verification Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .test-section {
            border: 1px solid #ddd;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .success { border-color: #28a745; background-color: #d4edda; }
        .error { border-color: #dc3545; background-color: #f8d7da; }
        .info { border-color: #17a2b8; background-color: #d1ecf1; }
        input, select, button {
            padding: 8px;
            margin: 5px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }
        button { background: #007bff; color: white; cursor: pointer; }
        button:hover { background: #0056b3; }
        .result {
            background: #f8f9fa;
            padding: 10px;
            margin: 10px 0;
            border-radius: 3px;
            font-family: monospace;
            white-space: pre-wrap;
            font-size: 12px;
        }
        .download-link {
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }
        .download-link:hover {
            text-decoration: underline;
        }
    </style>

    <!-- Try to get CSRF token from Laravel -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <h1>Direct Verification Test</h1>
    <p>This page tests the verification API directly without the listing form.</p>

    <div class="test-section">
        <h3>Test Verification Generation</h3>
        <form id="verificationForm">
            <input type="text" id="testDomain" placeholder="Enter domain (e.g., google.com)" value="google.com" required>
            <select id="testMethod">
                <option value="txt_file">TXT File</option>
                <option value="dns_record">DNS Record</option>
            </select>
            <button type="submit">Generate Verification</button>
        </form>
    </div>

    <div id="results"></div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Get CSRF token
        var csrfToken = '';

        function getCsrfToken(callback) {
            // Try meta tag first (Laravel Blade)
            var tokenMeta = document.querySelector('meta[name="csrf-token"]');
            if (tokenMeta && tokenMeta.getAttribute('content')) {
                csrfToken = tokenMeta.getAttribute('content');
                console.log('CSRF token from meta tag:', csrfToken.substring(0, 10) + '...');
                callback();
                return;
            }

            // Try to get from cookie (Laravel default)
            var cookies = document.cookie.split(';');
            for (var i = 0; i < cookies.length; i++) {
                var cookie = cookies[i].trim();
                if (cookie.startsWith('XSRF-TOKEN=')) {
                    csrfToken = decodeURIComponent(cookie.substring(12));
                    console.log('CSRF token from cookie:', csrfToken.substring(0, 10) + '...');
                    callback();
                    return;
                }
            }

            // Try to find in DOM
            var tokenInput = document.querySelector('input[name="_token"]');
            if (tokenInput) {
                csrfToken = tokenInput.value;
                console.log('CSRF token from input:', csrfToken.substring(0, 10) + '...');
                callback();
                return;
            }

            // Fallback - try to get a fresh token by making a request
            console.log('No CSRF token found, trying to get one...');
            $.ajax({
                url: window.location.origin, // Homepage
                method: 'GET',
                success: function(response) {
                    // Look for token in response
                    var tokenMatch = response.match(/name="csrf-token" content="([^"]+)"/);
                    if (tokenMatch) {
                        csrfToken = tokenMatch[1];
                        console.log('CSRF token from homepage:', csrfToken.substring(0, 10) + '...');
                    } else {
                        console.warn('Could not extract CSRF token from homepage');
                    }
                    callback();
                },
                error: function() {
                    console.warn('Could not get CSRF token, proceeding without it');
                    csrfToken = '';
                    callback();
                }
            });
        }

        $('#verificationForm').on('submit', function(e) {
            e.preventDefault();

            var domain = $('#testDomain').val();
            var method = $('#testMethod').val();

            $('#results').html('<div class="test-section info">Getting CSRF token and testing verification...</div>');

            getCsrfToken(function() {
                $('#results').append('<div class="result">CSRF Token: ' + (csrfToken ? '✅ Found (' + csrfToken.substring(0, 10) + '...)' : '❌ Not found') + '</div>');

                // Show what we're sending
                $('#results').append('<div class="result">Sending data:\n' +
                    '- Domain: ' + domain + '\n' +
                    '- Method: ' + method + '\n' +
                    '- CSRF Token: ' + (csrfToken ? 'Present' : 'Missing') + '\n' +
                    '- URL: user/verification/generate</div>');

                // Now try POST with proper CSRF token
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
                        console.log('Success response:', response);

                        var html = '<div class="test-section success">';
                        html += '<h4>✅ Verification Generated Successfully</h4>';
                        html += '<div class="result">';
                        html += 'Domain: ' + (response.domain || 'N/A') + '\n';
                        html += 'Method: ' + (response.method || 'N/A') + '\n';
                        html += 'Token: ' + (response.token ? response.token.substring(0, 20) + '...' : 'N/A') + '\n';

                        if (response.filename) {
                            html += '📄 Filename: ' + response.filename + '\n';
                            html += '🔗 Expected URL: ' + response.expected_url + '\n';
                            html += '📝 Content: ' + (response.content ? response.content.substring(0, 30) + '...' : 'N/A') + '\n';
                        } else {
                            html += '❌ No filename in response\n';
                        }

                        if (response.dns_name) {
                            html += '🌐 DNS Name: ' + response.dns_name + '\n';
                            html += '🔢 DNS Value: ' + (response.dns_value ? response.dns_value.substring(0, 30) + '...' : 'N/A') + '\n';
                        }

                        html += '</div>';

                        // Test download link
                        if (response.filename && response.token) {
                            html += '<p><strong>Download Test:</strong> <a href="user/verification/download?token=' + encodeURIComponent(response.token) + '&filename=' + encodeURIComponent(response.filename) + '&domain=' + encodeURIComponent(response.domain || domain) + '" target="_blank" style="color: #007bff;">📥 Download TXT File</a></p>';
                        } else {
                            html += '<p style="color: red;">❌ Cannot create download link - missing filename or token</p>';
                        }

                        html += '</div>';
                        $('#results').html(html);
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', {
                            status: xhr.status,
                            statusText: xhr.statusText,
                            responseText: xhr.responseText?.substring(0, 500),
                            readyState: xhr.readyState
                        });

                        var errorMsg = 'Unknown error';
                        var details = '';

                        if (xhr.status === 419) {
                            errorMsg = 'CSRF Token Error (419)';
                            details = 'The CSRF token is invalid or missing. Try refreshing the page.';
                        } else if (xhr.status === 422) {
                            errorMsg = 'Validation Error (422)';
                            try {
                                var jsonResponse = JSON.parse(xhr.responseText);
                                details = jsonResponse.message || 'Invalid input data';
                            } catch (e) {
                                details = 'Invalid input data';
                            }
                        } else if (xhr.status === 500) {
                            errorMsg = 'Server Error (500)';
                            details = 'Internal server error - check Laravel logs';
                        } else if (xhr.responseJSON) {
                            errorMsg = xhr.responseJSON.message || 'API Error';
                            details = JSON.stringify(xhr.responseJSON, null, 2);
                        } else {
                            details = xhr.responseText?.substring(0, 200) || 'No response details';
                        }

                        var html = '<div class="test-section error">';
                        html += '<h4>❌ Verification Generation Failed</h4>';
                        html += '<div class="result">';
                        html += 'HTTP Status: ' + xhr.status + ' ' + xhr.statusText + '\n';
                        html += 'Error Type: ' + errorMsg + '\n';
                        html += 'Details: ' + details + '\n';
                        html += 'URL: user/verification/generate\n';
                        html += 'CSRF Token: ' + (csrfToken ? 'Present' : 'Missing') + '\n';
                        html += '</div>';
                        html += '<p><strong>Troubleshooting:</strong></p>';
                        html += '<ul>';
                        if (xhr.status === 419) {
                            html += '<li>Try refreshing the page to get a new CSRF token</li>';
                        }
                        html += '<li>Check browser console for JavaScript errors</li>';
                        html += '<li>Check Laravel logs for server errors</li>';
                        html += '<li>Ensure you are logged in</li>';
                        html += '</ul>';
                        html += '</div>';

                        $('#results').html(html);
                    }
            });
        });

        // Show initial status
        $(document).ready(function() {
            $('#results').html('<div class="test-section info">Ready to test. Fill in the domain and click "Generate Verification".</div>');
        });
    </script>
</body>
</html>
