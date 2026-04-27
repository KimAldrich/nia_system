<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('images/2020-nia-logo.png') }}">
    <title>Data Privacy Agreement</title>
    <style>
        :root {
            --primary: #0b5e2c;
            --primary-dark: #084721;
            --accent: #0f172a;
            --bg-color: #eff4fb;
            --text-main: #334155;
            --text-muted: #64748b;
            --card-bg: #ffffff;
            --border-color: #dbe5f0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--bg-color);
            color: var(--text-main);
            min-height: 100vh;
            margin: 0;
            padding: 12px;
            box-sizing: border-box;
        }

        .terms-page {
            max-width: 1240px;
            margin: 0 auto;
        }

        .terms-container {
            background: transparent;
            border-radius: 0;
            box-shadow: none;
            overflow: visible;
            border: none;
        }

        .terms-header {
            background: var(--primary);
            color: white;
            padding: 16px 20px;
            border-radius: 14px 14px 0 0;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            padding: 5px 10px;
            margin-bottom: 10px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.16);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .terms-header h2 {
            margin: 0 0 6px;
            font-size: 22px;
            line-height: 1.15;
        }

        .terms-header p {
            margin: 0;
            max-width: 860px;
            font-size: 12px;
            line-height: 1.4;
            color: rgba(255, 255, 255, 0.9);
        }

        .terms-content {
            padding: 14px 18px 16px;
            line-height: 1.5;
            font-size: 14px;
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-top: none;
            border-radius: 0 0 14px 14px;
        }

        .terms-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.15fr) minmax(0, 1fr);
            gap: 14px;
            margin-bottom: 14px;
            align-items: start;
        }

        .terms-panel {
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 14px;
            padding: 14px 16px;
            height: 100%;
        }

        .terms-panel h3 {
            margin: 0 0 8px;
            color: var(--accent);
            font-size: 16px;
        }

        .terms-panel p,
        .terms-panel li {
            color: #475569;
            font-size: 12px;
            line-height: 1.45;
        }

        .terms-panel ul {
            margin: 8px 0 0;
            padding-left: 16px;
        }

        .terms-scroll-box {
            background: transparent;
            border: none;
            border-radius: 0;
            padding: 0;
            margin: 0;
        }

        .terms-scroll-box h3 {
            margin-top: 0;
            color: var(--accent);
            font-size: 16px;
            margin-bottom: 8px;
        }

        .terms-scroll-box h4 {
            margin: 10px 0 4px;
            color: var(--primary);
            font-size: 13px;
        }

        .terms-scroll-box p,
        .terms-scroll-box li {
            color: #475569;
            font-size: 12px;
            line-height: 1.45;
        }

        .terms-scroll-box ul {
            margin: 6px 0 0;
            padding-left: 16px;
        }

        .action-area {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            border-top: 1px solid var(--border-color);
            padding-top: 12px;
        }

        .action-copy {
            max-width: 480px;
        }

        .action-copy strong {
            display: block;
            color: var(--accent);
            font-size: 13px;
            margin-bottom: 2px;
        }

        .action-copy span {
            color: var(--text-muted);
            font-size: 11px;
        }

        button {
            background: var(--primary);
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s, transform 0.15s, box-shadow 0.2s;
            box-shadow: 0 10px 18px rgba(11, 94, 44, 0.14);
        }

        button:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        button:active {
            transform: scale(0.98);
        }

        @media (max-width: 768px) {
            body {
                padding: 10px;
            }

            .terms-header,
            .terms-content {
                padding: 16px;
            }

            .terms-header {
                border-radius: 12px 12px 0 0;
            }

            .terms-content {
                border-radius: 0 0 12px 12px;
            }

            .terms-header h2 {
                font-size: 20px;
            }

            .action-area {
                grid-template-columns: 1fr;
                display: grid;
            }

            .terms-grid {
                grid-template-columns: 1fr;
            }

            .terms-panel {
                padding: 16px;
            }

            button {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="terms-page">
        <div class="terms-container">
            <div class="terms-header">
                <div class="eyebrow">Privacy Compliance</div>
                <h2>Data Privacy Agreement</h2>
                <p>Before continuing to the portal, please review how personal information and activity records are handled under Republic Act No. 10173.</p>
            </div>

            <div class="terms-content">
                <div class="terms-grid">
                    <div class="terms-panel">
                        <div class="terms-scroll-box">
                            <h3>NIA Pangasinan IMO System Use and Privacy Notice</h3>
                            <p>By accessing this system, you acknowledge that it is an official internal portal used to support the operations, records, and coordination activities of NIA Pangasinan IMO and its authorized teams.</p>

                            <h4>Authorized Use</h4>
                            <p>This portal is provided only for approved government work. Files, dashboards, user records, and activity inside the system must be accessed only in connection with legitimate office responsibilities and assigned functions.</p>

                            <h4>Privacy and Record Handling</h4>
                            <p>User information, account details, submitted records, and system activity logs may be collected and processed for administration, security, monitoring, and service improvement. Information is handled in accordance with applicable office policies and the Data Privacy Act of 2012.</p>
                        </div>
                    </div>

                    <div class="terms-panel">
                        <div class="terms-scroll-box">
                            <h4>Security and Accountability</h4>
                            <p>For the protection of project information and official records, access to the system may be logged and reviewed. Any misuse, unauthorized access, sharing of credentials, or improper handling of documents may result in restriction of access and corresponding administrative action.</p>

                            <h4>User Responsibilities</h4>
                            <ul>
                                <li>Keep your account credentials secure at all times.</li>
                                <li>Do not share your password or allow another person to use your account.</li>
                                <li>Access only the modules, files, and information related to your authorized role.</li>
                                <li>Protect downloaded or viewed documents from unauthorized disclosure.</li>
                                <li>Log out properly after each session, especially when using a shared device or workstation.</li>
                            </ul>

                            <h4>Acceptance</h4>
                            <p>By selecting <strong>I Agree and Continue</strong>, you confirm that you understand these conditions and agree to use the NIA Pangasinan IMO system responsibly, securely, and only for official purposes.</p>
                        </div>
                    </div>
                </div>
<br><br>
                <div class="action-area">
                    <div class="action-copy">
                        <strong>Confirmation Required</strong>
                        <span>Continue only if you understand the conditions for using the NIA Pangasinan IMO system and its records.</span>
                    </div>
                    <form action="{{ route('terms.agree') }}" method="POST">
                        @csrf
                        <button type="submit">I Agree and Continue</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
