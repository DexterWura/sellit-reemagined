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

            $('#results').html('<div class="test-section info">Testing verification generation...</div>');

            console.log('Starting verification test with:', {domain: domain, method: method});

            // Simple test without complex CSRF handling
            $.ajax({
                url: 'user/verification/generate',
                method: 'POST',
                data: {
                    _token: 'test', // Simple token for testing
                    domain: domain,
                    method: method
                },
                success: function(response) {
                    console.log('Success:', response);

                    var html = '<div class="test-section success">';
                    html += '<h4>✅ API Response Received</h4>';
                    html += '<div class="result">';
                    html += 'Raw Response:\n' + JSON.stringify(response, null, 2);
                    html += '</div></div>';

                    $('#results').html(html);
                },
                error: function(xhr, status, error) {
                    console.error('Error:', xhr.status, xhr.responseText);

                    var html = '<div class="test-section error">';
                    html += '<h4>❌ API Error</h4>';
                    html += '<div class="result">';
                    html += 'Status: ' + xhr.status + '\n';
                    html += 'Response: ' + xhr.responseText.substring(0, 200);
                    html += '</div></div>';

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
