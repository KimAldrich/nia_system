<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 | Server Error</title>
    <style>
        :root {
            --bg: #f8fafc;
            --card: #ffffff;
            --text: #0f172a;
            --muted: #64748b;
            --accent: #7c3aed;
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
            background: linear-gradient(180deg, #f5f3ff 0%, var(--bg) 100%);
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
            background: #ede9fe;
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
        <div class="code">500</div>
        <h1>Something broke on our side</h1>
        <p>We ran into an unexpected server error while loading this page. Please try again in a moment.</p>
        <a href="{{ url()->previous() !== url()->current() ? url()->previous() : url('/') }}">Go back</a>
    </div>
</body>
</html>
