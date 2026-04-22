<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guest Terms & Conditions</title>
    <style>
        :root {
            --primary: #0b5e2c;
            --primary-dark: #084721;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border-color: #dbe5f0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #eef4fb;
            min-height: 100vh;
            margin: 0;
            padding: 12px;
            box-sizing: border-box;
        }

        .terms-page {
            max-width: 1240px;
            margin: 0 auto;
        }

        .terms-box {
            background: transparent;
            padding: 0;
            width: 100%;
            overflow: visible;
            border: none;
            box-shadow: none;
        }

        .terms-hero {
            padding: 16px 20px;
            background: var(--primary);
            color: white;
            border-radius: 14px 14px 0 0;
        }

        .eyebrow {
            display: inline-flex;
            padding: 5px 10px;
            border-radius: 999px;
            background: rgba(255,255,255,0.16);
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 8px;
        }

        h1 {
            margin: 0 0 4px;
            color: white;
            font-size: 22px;
            line-height: 1.15;
            border-bottom: none;
            padding-bottom: 0;
        }

        .hero-copy {
            margin: 0;
            color: rgba(255,255,255,0.9);
            font-size: 12px;
            line-height: 1.4;
            max-width: 760px;
        }

        .terms-body {
            padding: 14px 18px 16px;
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-top: none;
            border-radius: 0 0 14px 14px;
        }

        .summary-note {
            display: none;
        }

        .terms-content {
            background: #ffffff;
            padding: 14px 16px;
            border-radius: 14px;
            color: #475569;
            font-size: 12px;
            line-height: 1.45;
            margin-bottom: 12px;
            border: 1px solid var(--border-color);
            column-count: 2;
            column-gap: 18px;
        }

        .terms-content p {
            margin: 0 0 10px;
            break-inside: avoid;
        }

        .terms-content p:last-child {
            margin-bottom: 0;
        }

        .btn-agree {
            display: block;
            width: 100%;
            padding: 10px 14px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 13px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.2s;
            box-shadow: 0 10px 18px rgba(11, 94, 44, 0.14);
        }

        .btn-agree:hover {
            transform: translateY(-1px);
            background: var(--primary-dark);
        }

        .btn-decline {
            display: block;
            text-align: center;
            margin-top: 8px;
            color: #64748b;
            text-decoration: none;
            font-size: 12px;
        }

        @media (max-width: 640px) {
            body {
                padding: 10px;
            }

            .terms-hero,
            .terms-body {
                padding: 16px;
            }

            .terms-hero {
                border-radius: 12px 12px 0 0;
            }

            .terms-body {
                border-radius: 0 0 12px 12px;
            }

            h1 {
                font-size: 20px;
            }

            .terms-content {
                column-count: 1;
            }
        }
    </style>
</head>
<body>
    <div class="terms-page">
        <div class="terms-box">
            <div class="terms-hero">
                <div class="eyebrow">Guest Portal</div>
                <h1>Guest Access Agreement</h1>
                <p class="hero-copy">Please review the basic access conditions for visitors before proceeding to public project documents and reference materials.</p>
            </div>

            <div class="terms-body">
                <div class="terms-content">
                    <p><strong>1. Purpose of Guest Access</strong><br>Guest access is provided to allow viewing of selected documents, project information, and reference materials made available by NIA Pangasinan IMO for limited public or external access.</p>
                    <p><strong>2. Read-Only Use</strong><br>Guest users are not permitted to edit records, upload files, delete content, or make changes to any part of the system. Access is strictly limited to viewing only the materials exposed through the guest portal.</p>
                    <p><strong>3. Proper Handling of Information</strong><br>Documents and information obtained through guest access must be used responsibly. Materials that are not intended for redistribution, alteration, or unauthorized publication should not be copied or circulated without proper permission.</p>
                    <p><strong>4. Monitoring and Security</strong><br>Guest sessions may be logged and monitored to protect the system and its records. Attempts to bypass restrictions, access unauthorized pages, or misuse the portal may result in immediate blocking of guest access.</p>
                    <p><strong>5. Acceptance</strong><br>By selecting <strong>I Agree - Proceed to Documents</strong>, you confirm that you understand these access conditions and agree to use the NIA Pangasinan IMO guest portal in a responsible and lawful manner.</p>
                </div>

                <form action="{{ route('guest.accept') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-agree">I Agree - Proceed to Documents</button>
                </form>
                <form action="{{ route('guest.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-decline" style="background:none; border:none; width:100%; cursor:pointer;">Decline & Return to Login</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
