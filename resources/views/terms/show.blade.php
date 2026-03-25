<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Privacy Agreement</title>
    <style>
        :root {
            --primary: #0b5e2c;
            --primary-dark: #084721;
            --bg-color: #f1f5f9;
            --text-main: #334155;
            --card-bg: #ffffff;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
        }

        .terms-container {
            background: var(--card-bg);
            max-width: 700px;
            width: 100%;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .terms-header {
            background-color: var(--primary);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .terms-header h2 {
            margin: 0 0 10px 0;
            font-size: 24px;
        }

        .terms-content {
            padding: 40px;
            line-height: 1.6;
            font-size: 15px;
        }

        .terms-scroll-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 25px;
            height: 250px;
            overflow-y: auto;
            margin-bottom: 30px;
        }

        .terms-scroll-box h3 {
            margin-top: 0;
            color: #0f172a;
        }

        .action-area {
            text-align: center;
            border-top: 1px solid #e2e8f0;
            padding-top: 25px;
        }

        button {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 14px 35px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s, transform 0.1s;
        }

        button:hover {
            background-color: var(--primary-dark);
        }

        button:active {
            transform: scale(0.98);
        }
    </style>
</head>

<body>

    <div class="terms-container">
        <div class="terms-header">
            <h2>Data Privacy Agreement</h2>
            <p style="margin: 0; opacity: 0.9;">Republic Act No. 10173</p>
        </div>

        <div class="terms-content">
            <p>Before you access this website, please read and accept our data privacy rules.</p>

            <div class="terms-scroll-box">
                <h3>Data Privacy Act of 2012</h3>
                <p>By using this system, you agree that your personal data and activity logs will be collected and
                    processed safely.</p>

                <p>This information is used only for official tasks within the regional office and field offices. We
                    will keep your data secure and we will not share it with outside parties without your permission,
                    unless the law requires it.</p>

                <p><strong>Your Responsibilities:</strong></p>
                <ul style="margin-top: 0; padding-left: 20px;">
                    <li>Keep your account details safe.</li>
                    <li>Do not share your password with anyone.</li>
                    <li>Log out of the system when you are done using it.</li>
                </ul>
            </div>

            <div class="action-area">
                <form action="{{ route('terms.agree') }}" method="POST">
                    @csrf
                    <button type="submit">I Agree and Continue</button>
                </form>
            </div>
        </div>
    </div>

</body>

</html>