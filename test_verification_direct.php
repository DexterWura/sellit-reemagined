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
        }
    </style>
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
        $('#verificationForm').on('submit', function(e) {
            e.preventDefault();

            var domain = $('#testDomain').val();
            var method = $('#testMethod').val();

            $('#results').html('<div class="test-section info">Testing verification generation...</div>');

            // First test if route is accessible
            $.ajax({
                url: 'user/verification/generate',
                method: 'GET', // Try GET first to see if route exists
                success: function(response) {
                    console.log('Route accessible (GET):', response);
                },
                error: function(xhr) {
                    console.log('Route GET test:', xhr.status, xhr.responseText);
                }
            });

            // Now try POST
            $.ajax({
                url: 'user/verification/generate',
                method: 'POST',
                data: {
                    _token: 'test_token', // Use test token since we don't have real CSRF
                    domain: domain,
                    method: method
                },
                success: function(response) {
                    console.log('Success response:', response);

                    var html = '<div class="test-section success">';
                    html += '<h4>✅ Verification Generated Successfully</h4>';
                    html += '<div class="result">';
                    html += 'Domain: ' + response.domain + '\n';
                    html += 'Method: ' + response.method + '\n';
                    html += 'Token: ' + response.token + '\n';

                    if (response.filename) {
                        html += 'Filename: ' + response.filename + '\n';
                        html += 'Expected URL: ' + response.expected_url + '\n';
                        html += 'Content: ' + response.content + '\n';
                    }

                    if (response.dns_name) {
                        html += 'DNS Name: ' + response.dns_name + '\n';
                        html += 'DNS Value: ' + response.dns_value + '\n';
                    }

                    html += '</div>';

                    // Test download link
                    if (response.filename) {
                        html += '<p><a href="user/verification/download?token=' + encodeURIComponent(response.token) + '&filename=' + encodeURIComponent(response.filename) + '&domain=' + encodeURIComponent(response.domain) + '" target="_blank" class="download-link">Download TXT File</a></p>';
                    }

                    html += '</div>';
                    $('#results').html(html);
                },
                error: function(xhr) {
                    console.error('Error response:', xhr);

                    var errorMsg = xhr.responseJSON?.message || 'Unknown error';
                    var html = '<div class="test-section error">';
                    html += '<h4>❌ Verification Generation Failed</h4>';
                    html += '<div class="result">';
                    html += 'Status: ' + xhr.status + '\n';
                    html += 'Error: ' + errorMsg + '\n';
                    html += 'Response: ' + xhr.responseText + '\n';
                    html += '</div>';
                    html += '</div>';

                    $('#results').html(html);
                }
            });
        });

        // Auto-run test on page load
        $(document).ready(function() {
            $('#verificationForm').trigger('submit');
        });
    </script>
</body>
</html>
