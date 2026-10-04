<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify your DirectStay email</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f6f8fc;
            font-family: -apple-system, BlinkMacSystemFont, 'Google Sans', 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #202124;
            -webkit-font-smoothing: antialiased;
        }
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f6f8fc;
            padding: 40px 0 60px 0;
        }
        .main-card {
            max-width: 520px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            border: 1px solid #e0e3e7;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }
        .brand-header {
            padding: 32px 36px 20px 36px;
            border-bottom: 1px solid #f1f3f4;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .logo-mark {
            display: inline-block;
            background: linear-gradient(135deg, #2563eb, #4f46e5);
            color: #ffffff;
            font-weight: 900;
            font-size: 14px;
            padding: 6px 12px;
            border-radius: 10px;
            letter-spacing: -0.5px;
        }
        .brand-text {
            font-size: 18px;
            font-weight: 800;
            color: #1e293b;
            letter-spacing: -0.5px;
            margin-left: 10px;
            vertical-align: middle;
        }
        .brand-text span {
            color: #2563eb;
        }
        .content-body {
            padding: 32px 36px 36px 36px;
        }
        .headline {
            font-size: 22px;
            font-weight: 700;
            color: #1a1f36;
            margin: 0 0 12px 0;
            line-height: 1.3;
        }
        .subhead {
            font-size: 14px;
            color: #4b5563;
            line-height: 1.6;
            margin: 0 0 24px 0;
        }
        .code-container {
            background: #f8fafc;
            border: 2px dashed #cbd5e1;
            border-radius: 14px;
            padding: 22px;
            text-align: center;
            margin: 28px 0;
        }
        .code-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #64748b;
            margin-bottom: 8px;
        }
        .otp-digits {
            font-family: 'SF Mono', Monaco, Inconsolata, 'Roboto Mono', Consolas, monospace;
            font-size: 38px;
            font-weight: 800;
            letter-spacing: 8px;
            color: #1d4ed8;
            padding: 4px 0;
            display: inline-block;
        }
        .expiry-badge {
            display: inline-block;
            background-color: #fef3c7;
            color: #92400e;
            font-size: 12px;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 9999px;
            margin-top: 10px;
        }
        .security-notice {
            background-color: #f1f5f9;
            border-radius: 10px;
            padding: 14px 16px;
            font-size: 12px;
            color: #475569;
            line-height: 1.5;
            margin-top: 24px;
        }
        .security-notice strong {
            color: #0f172a;
        }
        .footer-note {
            padding: 20px 36px 28px 36px;
            background-color: #f8fafc;
            border-top: 1px solid #f1f5f9;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="main-card">
            <!-- Gmail / Google Style Header -->
            <div class="brand-header">
                <table style="width: 100%;">
                    <tr>
                        <td style="text-align: left; vertical-align: middle;">
                            <span class="logo-mark">DS</span>
                            <span class="brand-text">Direct<span>Stay</span></span>
                        </td>
                        <td style="text-align: right; vertical-align: middle;">
                            <span style="font-size: 11px; font-weight: 700; color: #10b981; background: #ecfdf5; padding: 4px 10px; border-radius: 999px; border: 1px solid #a7f3d0;">
                                &bull; Security Verification
                            </span>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Body -->
            <div class="content-body">
                <h1 class="headline">Verify your email address</h1>
                <p class="subhead">
                    Hi <strong>{{ $user->name }}</strong>,<br>
                    Welcome to DirectStay! To activate your guest account and secure your Deca Ortigas staycation reservations, please enter this 6-digit verification code:
                </p>

                <!-- OTP Code Display Card -->
                <div class="code-container">
                    <div class="code-label">Verification Code</div>
                    <div class="otp-digits">{{ $code }}</div>
                    <div>
                        <span class="expiry-badge">&#x23F1; Valid for 15 minutes</span>
                    </div>
                </div>

                <p style="font-size: 13px; color: #64748b; line-height: 1.5; margin: 0 0 16px 0;">
                    Enter this code in your DirectStay browser tab to complete your registration.
                </p>

                <div class="security-notice">
                    <strong>Security Notice:</strong> DirectStay will never ask for your password or verification code via phone call or SMS. If you did not sign up for DirectStay, someone may have entered your email by mistake &mdash; you can safely ignore this email.
                </div>
            </div>

            <!-- Footer -->
            <div class="footer-note">
                &copy; {{ date('Y') }} <strong>DirectStay Ortigas</strong> &bull; Urban Deca Homes Ortigas, Pasig City.<br>
                Automated security notification sent to <span style="color: #64748b;">{{ $user->email }}</span>.
            </div>
        </div>
    </div>
</body>
</html>
