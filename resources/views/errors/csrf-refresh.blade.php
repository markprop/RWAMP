<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="refresh" content="1">
    <title>Refreshing security token…</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            text-align: center;
            margin: 0;
            padding: 0;
            background: #f9fafb;
            color: #111827;
        }
        .wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            background: #ffffff;
            padding: 2.5rem 2rem;
            border-radius: 0.75rem;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
            max-width: 24rem;
            width: 100%;
        }
        .spinner {
            width: 3rem;
            height: 3rem;
            border-radius: 999px;
            border: 4px solid #d1d5db;
            border-top-color: #2563eb;
            margin: 0 auto 1rem auto;
            animation: spin 0.8s linear infinite;
        }
        h1 {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        p {
            font-size: 0.875rem;
            color: #4b5563;
            margin: 0;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <div class="spinner"></div>
            <h1>Refreshing your session…</h1>
            <p>We’re updating your security token. This page will reload automatically.</p>
        </div>
    </div>
</body>
</html>

