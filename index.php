<?php
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validonix API Documentation</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* width */
        ::-webkit-scrollbar {
            width: 10px;
        }

        /* Track */
        ::-webkit-scrollbar-track {
            box-shadow: inset 0 0 5px grey;
            border-radius: 2px;
        }

        /* Handle */
        ::-webkit-scrollbar-thumb {
            background: grey;
            border-radius: 5px;
        }

        .loading-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--background);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            transition: opacity 0.5s ease;
        }

        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid var(--primary);
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        .loading-text {
            margin-top: 20px;
            color: var(--text);
            font-size: 1.2rem;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        :root {
            --primary: #5865f2;
            --secondary: #9146ff;
            --background: #e0e0e0;
            --text: #1a1a2d;
            --border: #d4d4d4;
        }

        body {
            background: var(--background);
            color: var(--text);
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 2rem;
        }

        .docs-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .header-card {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
        }

        .docs-card {
            background: #f4f4f4;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 24px rgba(88, 101, 242, 0.08);
        }

        .endpoint-method {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 6px;
            background: rgba(255, 255, 255, 0.1);
            font-family: monospace;
        }

        code {
            background: var(--background);
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-family: 'Courier New', Courier, monospace;
        }

        .response-example {
            background: #1a1a2d;
            color: #f8f9ff;
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
            overflow-x: auto;
        }

        .param-table {
            width: 100%;
            border-collapse: collapse;
            margin: 1rem 0;
        }

        .param-table th,
        .param-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border);
            text-align: left;
        }

        .navbar {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            padding: 1rem 2rem;
            box-shadow: 0 4px 20px rgba(88, 101, 242, 0.15);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .navbar-brand {
            color: white !important;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .navbar-brand img {
            height: 32px;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            padding: 0.5rem 1rem !important;
            border-radius: 8px;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white !important;
        }

        .nav-link.active {
            background: rgba(255, 255, 255, 0.2);
            position: relative;
        }

        .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 50%;
            transform: translateX(-50%);
            width: 60%;
            height: 2px;
            background: white;
            border-radius: 2px;
        }

        .nav-pills {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .nav-item {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            background: rgba(88, 101, 242, 0.1);
            color: var(--primary);
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .nav-item:hover {
            background: var(--primary);
            color: white;
        }
    </style>
</head>

<body>
    <div class="loading-screen">
        <div class="loading-spinner"></div>
        <div class="loading-text">Validonix API is Loading...</div>
    </div>

    <body>
        <div class="docs-container">
            <div class="header-card">
                <h1><i class="fas fa-book-open"></i> API Documentation</h1>
                <p>Version 1.2.1 | Updated February 2025</p>
            </div>

            <div class="nav-pills">
                <a href="#authentication" class="nav-item"><i class="fas fa-lock"></i> Authentication</a>
                <a href="#token-check" class="nav-item"><i class="fas fa-key"></i> Token Check</a>
                <a href="#curl-requests" class="nav-item"><i class="fas fa-terminal"></i> cURL Requests</a>
                <a href="#responses" class="nav-item"><i class="fas fa-code"></i> Responses</a>
                <a href="#errors" class="nav-item"><i class="fas fa-exclamation-triangle"></i> Errors</a>
            </div>

            <div class="docs-card" id="authentication">
                <h2><i class="fas fa-lock"></i> Authentication</h2>
                <div class="endpoint-method">GET</div>
                <code>/api/tokencheck.php?token=YOUR_TOKEN</code>

                <div class="alert" style="background: #fff3cd; padding: 1rem; border-radius: 8px; margin: 1rem 0;">
                    <i class="fas fa-exclamation-circle"></i>
                    <strong>Security Notice:</strong> Tokens are sensitive credentials. Never expose them publicly.
                </div>

                <h3><i class="fas fa-table"></i> Parameters</h3>
                <table class="param-table">
                    <thead>
                        <tr>
                            <th>Parameter</th>
                            <th>Required</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>token</code></td>
                            <td>Yes</td>
                            <td>Discord user/bot token</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="docs-card" id="curl-requests">
                <h2><i class="fas fa-terminal"></i> cURL Request Example</h2>
                <p>You can use the following cURL command to send a request to the API:</p>
                <div class="response-example">
                    <pre>curl -X GET "http://localhost/api/tokencheck.php?token=YOUR_TOKEN" -H "Accept: application/json"</pre>
                </div>
            </div>

            <div class="docs-card" id="responses">
                <h2><i class="fas fa-code"></i> Response Format</h2>

                <h3>Success Example</h3>
                <div class="response-example">
                    <pre>{
    "status": "success",
    "code": 200,
    "data": {
        "authentication": {
            "valid": true,
            "token": { /* ... */ }
        },
        "user": { /* ... */ }
    }
}</pre>
                </div>

                <h3>Error Example</h3>
                <div class="response-example">
                    <pre>{
    "status": "error",
    "code": 401,
    "error": {
        "message": "Invalid token",
        "resolution": "Check token validity"
    }
}</pre>
                </div>
            </div>

            <div class="docs-card" id="errors">
                <h2><i class="fas fa-exclamation-triangle"></i> Error Codes</h2>
                <table class="param-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Meaning</th>
                            <th>Resolution</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>400</code></td>
                            <td>Bad Request</td>
                            <td>Check parameters</td>
                        </tr>
                        <tr>
                            <td><code>401</code></td>
                            <td>Unauthorized</td>
                            <td>Validate token</td>
                        </tr>
                        <tr>
                            <td><code>500</code></td>
                            <td>Server Error</td>
                            <td>Contact support</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <script>
                window.addEventListener('load', function () {
                    setTimeout(function () {
                        document.querySelector('.loading-screen').style.opacity = '0';
                        setTimeout(function () {
                            document.querySelector('.loading-screen').style.display = 'none';
                        }, 500);
                    }, 2000);
                });
            </script>

            <div class="docs-card" style="text-align: center; color: #666;">
                <p>© 2025 Validonix API. All rights reserved.<br>
                    <small>Discord is a trademark of Discord Inc. Not affiliated with Discord.</small>
                </p>
            </div>
        </div>
    </body>

</html>