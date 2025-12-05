<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Domain Ownership Verification Tool</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <script>

        tailwind.config = {

            theme: {

                extend: {

                    fontFamily: {

                        sans: ['Inter', 'sans-serif'],

                    },

                }

            }

        }

    </script>

    <style>

        body {

            font-family: 'Inter', sans-serif;

            background-color: #f7f9fb;

        }

    </style>

</head>

<body class="p-4 sm:p-8 min-h-screen flex items-center justify-center">

    <div class="w-full max-w-2xl bg-white shadow-xl rounded-xl p-6 sm:p-10 border border-gray-100">

        <h1 class="text-3xl font-bold text-gray-800 mb-2">Domain Verification</h1>

        <p class="text-gray-500 mb-6">Generate and verify tokens for domain ownership proof. Please read the note on verification below.</p>

        <!-- Domain Input -->

        <div class="mb-6">

            <label for="domainInput" class="block text-sm font-medium text-gray-700 mb-1">Domain Name (e.g., zimadsense.com)</label>

            <input type="text" id="domainInput" placeholder="yourdomain.com"

                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 transition duration-150 ease-in-out"

                   oninput="generateVerificationToken()">

        </div>

        <!-- Verification Method Selection -->

        <div class="mb-8">

            <label class="block text-sm font-medium text-gray-700 mb-2">Verification Method</label>

            <div class="flex space-x-4">

                <label class="inline-flex items-center p-3 border border-indigo-200 bg-indigo-50 rounded-lg cursor-pointer">

                    <input type="radio" name="method" value="txt-file" class="form-radio h-4 w-4 text-indigo-600" checked

                           onchange="generateVerificationToken()">

                    <span class="ml-2 text-sm font-medium text-gray-700">TXT File Upload</span>

                </label>

                <label class="inline-flex items-center p-3 border border-gray-200 hover:border-gray-300 rounded-lg cursor-pointer">

                    <input type="radio" name="method" value="dns-record" class="form-radio h-4 w-4 text-indigo-600"

                           onchange="generateVerificationToken()">

                    <span class="ml-2 text-sm font-medium text-gray-700">DNS TXT Record</span>

                </label>

            </div>

        </div>

        <!-- Token Generation Output and Actions -->

        <div id="tokenOutput" class="space-y-4 mb-8 p-4 bg-gray-50 border border-dashed border-gray-200 rounded-lg transition-all duration-300 hidden">

            <h3 class="text-lg font-semibold text-gray-700" id="outputTitle">Verification Details</h3>

            <!-- TXT File Instructions -->

            <div id="txtFileDetails" class="space-y-3 hidden">

                <p class="text-sm text-gray-600">

                    Create a file named <code class="font-mono bg-white p-1 rounded">zimadsense.txt</code> and upload it to the root of your domain (<span id="txtFileUrlDisplay" class="font-medium text-indigo-600"></span>).

                    The content of the file must be the unique token below.

                </p>

                <div class="flex items-center space-x-3">

                    <button id="downloadBtn" class="px-4 py-2 text-sm font-semibold rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 transition duration-150 shadow-md">

                        Download zimadsense.txt

                    </button>

                    <button onclick="copyToClipboard(document.getElementById('verificationToken').innerText)" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">

                        Copy Token Only

                    </button>

                </div>

            </div>

            <!-- DNS Record Instructions -->

            <div id="dnsRecordDetails" class="space-y-3 hidden">

                <p class="text-sm text-gray-600">

                    Add a new DNS TXT record with the following details in your domain's DNS manager:

                </p>

                <div>

                    <label class="block text-xs font-medium text-gray-500 mb-0.5">Record Name/Host:</label>

                    <code id="dnsRecordHost" class="font-mono text-sm bg-white p-2 rounded block border border-gray-200 select-all">@</code>

                </div>

                <div>

                    <label class="block text-xs font-medium text-gray-500 mb-0.5">Value (The unique token):</label>

                    <code id="dnsRecordValue" class="font-mono text-sm bg-white p-2 rounded block border border-gray-200 select-all"></code>

                </div>

            </div>

            <!-- Verification Token Display (Shared) -->

            <div>

                <label class="block text-xs font-medium text-gray-500 mb-0.5">Verification Token (Key Part):</label>

                <code id="verificationToken" class="font-mono text-sm text-red-700 font-bold bg-white p-2 rounded block border border-red-300 select-all"></code>

            </div>

        </div>

        <!-- Verification Action & Status -->

        <div class="flex justify-between items-center pt-4 border-t border-gray-100">

            <button id="verifyBtn"

                    class="px-6 py-3 font-bold rounded-lg text-white bg-green-500 hover:bg-green-600 transition duration-150 shadow-lg shadow-green-200 disabled:opacity-50"

                    disabled>

                Verify Domain Now

            </button>

            <p id="verificationStatus" class="text-sm font-medium"></p>

        </div>

        <!-- IMPORTANT NOTE -->

        <div class="mt-8 p-4 bg-yellow-50 border-l-4 border-yellow-400 rounded-lg text-sm text-yellow-800">

            <h4 class="font-bold">Important Security Note:</h4>

            <p class="mt-1">

                Real-world domain verification, especially checking external files/DNS records, **requires a backend server** to bypass browser security restrictions (CORS and DNS query limitations).

            </p>

            <p class="mt-1">

                The "Verify" button below will **attempt** the fetch for the TXT file, but will likely fail with a CORS error in your browser console, which is expected for arbitrary domains. Use a server-side component for production verification.

            </p>

        </div>

    </div>

    <script>

        // Global variables to store the state

        let currentToken = '';

        let currentDomain = '';

        /**

         * Generates a unique verification token prefixed for ZimAdsense and updates the UI.

         */

        function generateVerificationToken() {

            const domainInput = document.getElementById('domainInput');

            const domain = domainInput.value.trim().replace(/^https?:\/\//, '').split('/')[0];

            const method = document.querySelector('input[name="method"]:checked').value;



            const tokenOutput = document.getElementById('tokenOutput');

            const verifyBtn = document.getElementById('verifyBtn');

            const status = document.getElementById('verificationStatus');

            currentDomain = domain;

            status.innerHTML = '';

            // 1. Validate Input

            if (!domain || !domain.includes('.')) {

                tokenOutput.classList.add('hidden');

                verifyBtn.disabled = true;

                return;

            }

            // 2. Generate or retrieve token

            // Use a persistent token for the current session based on the domain

            const sessionKey = `verification_token_${domain}`;

            let uniquePart = sessionStorage.getItem(sessionKey);

            if (!uniquePart) {

                uniquePart = crypto.randomUUID().replace(/-/g, ''); // Generate a unique identifier

                sessionStorage.setItem(sessionKey, uniquePart);

            }

            // The full token that must be verified

            currentToken = `zimadsense-verification=${uniquePart}`;

            // 3. Update UI visibility

            tokenOutput.classList.remove('hidden');

            verifyBtn.disabled = false;

            const txtFileDetails = document.getElementById('txtFileDetails');

            const dnsRecordDetails = document.getElementById('dnsRecordDetails');

            txtFileDetails.classList.add('hidden');

            dnsRecordDetails.classList.add('hidden');

            // 4. Populate shared token

            document.getElementById('verificationToken').innerText = currentToken;

            // 5. Populate method-specific details

            if (method === 'txt-file') {

                txtFileDetails.classList.remove('hidden');

                document.getElementById('outputTitle').innerText = 'Verification File (TXT File)';

                document.getElementById('txtFileUrlDisplay').innerText = `http://${domain}/zimadsense.txt`;

                // Re-attach listener for the download button

                const downloadBtn = document.getElementById('downloadBtn');

                downloadBtn.onclick = () => downloadFile('zimadsense.txt', currentToken);

            } else if (method === 'dns-record') {

                dnsRecordDetails.classList.remove('hidden');

                document.getElementById('outputTitle').innerText = 'Verification Record (DNS TXT)';

                document.getElementById('dnsRecordHost').innerText = `_zimadsense_verification.${domain}.`;

                document.getElementById('dnsRecordValue').innerText = currentToken;

            }

        }

        /**

         * Initiates the download of the verification file.

         * @param {string} filename - The name of the file to download.

         * @param {string} content - The content of the file.

         */

        function downloadFile(filename, content) {

            const blob = new Blob([content], { type: 'text/plain;charset=utf-8' });

            const url = URL.createObjectURL(blob);

            const a = document.createElement('a');

            a.href = url;

            a.download = filename;

            document.body.appendChild(a);

            a.click();

            document.body.removeChild(a);

            URL.revokeObjectURL(url);

        }

        /**

         * Copies text content to the user's clipboard.

         * @param {string} text - The text to copy.

         */

        function copyToClipboard(text) {

            // Using document.execCommand('copy') for better iframe compatibility

            const tempInput = document.createElement('textarea');

            tempInput.value = text;

            document.body.appendChild(tempInput);

            tempInput.select();



            let success = false;

            try {

                success = document.execCommand('copy');

                showStatus(success ? 'Token copied to clipboard!' : 'Failed to copy token.', success ? 'text-blue-600' : 'text-red-600');

            } catch (err) {

                showStatus('Failed to copy token.', 'text-red-600');

            }

            document.body.removeChild(tempInput);

        }

        /**

         * Displays status messages.

         * @param {string} message - The status message.

         * @param {string} colorClass - Tailwind class for color.

         */

        function showStatus(message, colorClass) {

            const statusElement = document.getElementById('verificationStatus');

            statusElement.innerHTML = message;

            statusElement.className = `text-sm font-medium ${colorClass} transition-opacity duration-300`;

        }

        /**

         * Attempts to verify the domain.

         */

        async function verifyDomain() {

            const method = document.querySelector('input[name="method"]:checked').value;

            const verifyBtn = document.getElementById('verifyBtn');

            const domain = currentDomain;

            if (!domain || !currentToken) return;

            verifyBtn.disabled = true;

            showStatus('Attempting verification...', 'text-gray-500');

            if (method === 'dns-record') {

                // Cannot perform real DNS lookup in client-side JS.

                showStatus('DNS verification requires a backend server to query DNS records.', 'text-red-500');

                verifyBtn.disabled = false;

                return;

            }

            // --- TXT File Verification Attempt ---

            const protocol = 'http'; // Use http to avoid mixed content errors if target doesn't support https

            const fileUrl = `${protocol}://${domain}/zimadsense.txt`;

            try {

                // The browser will likely block this due to CORS, but we attempt the fetch anyway.

                const response = await fetch(fileUrl, {

                    method: 'GET',

                    mode: 'cors', // Request mode set to 'cors'

                    cache: 'no-store'

                });

                if (response.ok) {

                    const content = await response.text();



                    if (content.trim() === currentToken) {

                        showStatus('SUCCESS! Domain verified via TXT file.', 'text-green-600');

                    } else {

                        showStatus('FAILURE: TXT file found, but content does not match the token.', 'text-orange-500');

                    }

                } else {

                     // This path is usually hit if the file is genuinely missing (404) AND no CORS error occurred,

                     // but more commonly, the request is blocked before this point by CORS.

                     showStatus(`FAILURE: File not found (HTTP Status ${response.status}).`, 'text-red-500');

                }

            } catch (error) {

                // This is the most common path for external domains due to CORS.

                console.error("Verification Error:", error);

                showStatus('VERIFICATION BLOCKED (CORS Error). See console for details. Verification must be done by a server-side proxy.', 'text-red-700');

            } finally {

                verifyBtn.disabled = false;

            }

        }

        // Initialize event listeners and state

        document.addEventListener('DOMContentLoaded', () => {

            document.getElementById('verifyBtn').addEventListener('click', verifyDomain);

            generateVerificationToken(); // Initial call to set up the state

        });

    </script>

</body>

</html>
