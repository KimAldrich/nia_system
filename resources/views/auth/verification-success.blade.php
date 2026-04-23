<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verified</title>
    <style>
        :root {
            --primary: #0b5e2c;
            --text-dark: #1e293b;
            --text-gray: #64748b;
            --bg-soft: #f8fafc;
            --border: #dbe3ee;
            --success-bg: #ecfdf5;
            --success-text: #166534;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-image: url("{{ asset('images/2020-nia-logo.png') }}");
            background-size: auto;
            background-position: center;
            background-attachment: fixed;
            background-repeat: no-repeat;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
        }

        .card {
            position: relative;
            z-index: 1;
            width: min(92vw, 460px);
            background: rgba(255, 255, 255, 0.96);
            border-radius: 18px;
            padding: 34px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            text-align: center;
        }

        h1 {
            margin: 0 0 12px;
            color: var(--primary);
            font-size: 28px;
        }

        p {
            margin: 0 0 16px;
            color: var(--text-gray);
            line-height: 1.6;
            font-size: 14px;
        }

        .status {
            background: var(--success-bg);
            color: var(--success-text);
            border: 1px solid #bbf7d0;
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: 18px;
            font-size: 13px;
        }

        .link-button {
            display: inline-block;
            width: 100%;
            box-sizing: border-box;
            text-align: center;
            text-decoration: none;
            margin-top: 4px;
            padding: 14px;
            border-radius: 10px;
            background: #fff;
            color: var(--text-dark);
            border: 1px solid var(--border);
            font-size: 15px;
            font-weight: 700;
        }
    </style>
</head>

<body>
    <div class="overlay"></div>

    <div class="card">
        <h1>Email Verified</h1>
        <div class="status">Your email address has been verified successfully.</div>
        <p>You will be redirected to the terms page shortly.</p>
        <a href="{{ $redirectUrl }}" class="link-button">Continue Now</a>
    </div>

    <script>
        window.setTimeout(() => {
            window.location.href = @json($redirectUrl);
        }, 1500);
    </script>
</body>

</html>
