<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email</title>
    <style>
        :root {
            --primary: #0b5e2c;
            --primary-hover: #084721;
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
        }

        h1 {
            margin: 0 0 10px;
            color: var(--primary);
            font-size: 28px;
        }

        p {
            margin: 0 0 16px;
            color: var(--text-gray);
            line-height: 1.6;
            font-size: 14px;
        }

        .email-box {
            background: var(--bg-soft);
            border: 1px solid var(--border);
            color: var(--text-dark);
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: 18px;
            font-weight: 600;
            word-break: break-word;
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

        button,
        .link-button {
            width: 100%;
            padding: 14px;
            border-radius: 10px;
            border: none;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
        }

        button {
            background: var(--primary);
            color: #fff;
        }

        button:hover {
            background: var(--primary-hover);
        }

        .link-button {
            display: inline-block;
            box-sizing: border-box;
            text-align: center;
            text-decoration: none;
            margin-top: 12px;
            background: #fff;
            color: var(--text-dark);
            border: 1px solid var(--border);
        }
    </style>
</head>

<body>
    <div class="overlay"></div>

    <div class="card">
        <h1>Verify Your Email</h1>
        <p>
            Before continuing, please check your inbox and click the verification link that we sent to your email
            address.
        </p>

        <div class="email-box">{{ auth()->user()->email }}</div>

        @if (session('status'))
            <div class="status">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit">Resend Verification Email</button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="link-button">Back to Login</button>
        </form>
    </div>
</body>

</html>
