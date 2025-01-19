<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} - Maintenance Mode</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: #f8fafc;
            color: #1a202c;
            height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            text-align: center;
            padding: 2rem;
            max-width: 600px;
        }
        .logo {
            margin-bottom: 2rem;
        }
        h1 {
            font-size: 2.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        p {
            font-size: 1.125rem;
            line-height: 1.75;
            margin-bottom: 2rem;
            color: #4a5568;
        }
        .maintenance-icon {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            color: #4299e1;
        }
        @if(isset($retryAfter))
        .retry-text {
            font-size: 0.875rem;
            color: #718096;
        }
        @endif
    </style>
</head>
<body>
    <div class="container">
        <div class="maintenance-icon">🛠️</div>
        <h1>We'll Be Right Back</h1>
        <p>
            We're currently performing some maintenance on our site.
            We apologize for any inconvenience and should be back online shortly.
        </p>
        @if(isset($retryAfter))
            <p class="retry-text">
                Please try again in {{ $retryAfter }} seconds.
            </p>
        @endif
    </div>
</body>
</html>
