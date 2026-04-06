<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guest Terms & Conditions</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f8fafc; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .terms-box { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); max-width: 600px; width: 90%; }
        h1 { margin-top: 0; color: #0f172a; font-size: 24px; border-bottom: 2px solid #e2e8f0; padding-bottom: 15px; }
        .terms-content { height: 250px; overflow-y: auto; background: #f1f5f9; padding: 20px; border-radius: 8px; color: #475569; font-size: 14px; line-height: 1.6; margin-bottom: 25px; border: 1px solid #e2e8f0; }
        .btn-agree { display: block; width: 100%; padding: 12px; background: #0b5e2c; color: white; border: none; border-radius: 6px; font-size: 16px; font-weight: bold; cursor: pointer; transition: 0.2s; }
        .btn-agree:hover { background: #084721; }
        .btn-decline { display: block; text-align: center; margin-top: 15px; color: #64748b; text-decoration: none; font-size: 14px; }
    </style>
</head>
<body>
    <div class="terms-box">
        <h1>Guest Access Agreement</h1>
        <div class="terms-content">
            <p><strong>1. Read-Only Access</strong><br>As a guest user, you are granted strict read-only access to public records and documents. You cannot edit, upload, or delete any files.</p>
            <p><strong>2. Confidentiality</strong><br>Some documents may contain sensitive project information. You agree not to distribute or publish these documents without official consent.</p>
            <p><strong>3. System Monitoring</strong><br>Guest access is logged and monitored for security purposes. Any attempt to bypass the read-only restrictions will result in immediate IP bans.</p>
            <p><strong>4. Acceptance</strong><br>By clicking "I Agree", you acknowledge these terms and agree to use the portal responsibly.</p>
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
</body>
</html>