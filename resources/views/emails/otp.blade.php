<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>OTP Verification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            padding: 20px;
        }
        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            max-width: 600px;
            margin: auto;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .otp {
            font-size: 32px;
            font-weight: bold;
            color: #1a73e8;
            letter-spacing: 8px;
            margin: 20px 0;
            text-align: center;
        }
        .footer {
            font-size: 12px;
            color: #888;
            margin-top: 30px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Your Verification Code</h2>
        <p>Hello,</p>
        <p>Thank you for using our application. Your one-time password (OTP) is:</p>

        <div class="otp">{{ $otp }}</div>

        <p>Please enter this code to complete the verification process. The code will expire in 10 minutes.</p>

        <div class="footer">
            If you did not request this code, you can safely ignore this email.
        </div>
    </div>
</body>
</html>
