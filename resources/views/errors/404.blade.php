<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 | Page Not Found</title>
    <style>
        :root {
            --bg: #f8fafc;
            --card: #ffffff;
            --text: #0f172a;
            --muted: #64748b;
            --accent: #2563eb;
            --border: #e2e8f0;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(180deg, #eff6ff 0%, var(--bg) 100%);
            color: var(--text);
        }

        .error-card {
            width: min(100%, 560px);
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 36px;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.08);
        }

        .code {
            display: inline-flex;
            padding: 6px 12px;
            border-radius: 999px;
            background: #dbeafe;
            color: var(--accent);
            font-weight: 700;
            font-size: 14px;
            margin-bottom: 16px;
        }

        h1 { margin: 0 0 12px; font-size: 32px; }
        p { margin: 0 0 24px; color: var(--muted); line-height: 1.6; }
        a {
            display: inline-block;
            padding: 12px 18px;
            border-radius: 10px;
            background: #0f172a;
            color: #ffffff;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="code">404</div>
        <h1>Page not found</h1>
        <p>The page you were trying to reach could not be found. The link may be outdated, or the page may have been moved.</p>
        <a href="{{ url('/') }}">Return home</a>
    </div>
</body>
</html>
