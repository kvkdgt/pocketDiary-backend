<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Password Reset OTP</title>
<style>
    /* Reset styles */
    body, html {
        margin: 0;
        padding: 0;
        font-family: Arial, sans-serif;
    }
    
    /* Container */
    .container {
        max-width: 600px;
        margin: 20px auto;
        padding: 20px;
        border: 1px solid #ccc;
        border-radius: 5px;
        text-align: center;
    }
    
    /* Header */
    .header {
        text-align: center;
        margin-bottom: 20px;
    }
    
    /* Content */
    .content {
        margin-bottom: 20px;
    }
    
    /* OTP Section */
    .otp {
        text-align: center;
        font-size: 24px;
        font-weight: bold;
        color: #333;
        margin-bottom: 20px;
    }
    
    /* Footer */
    .footer {
        text-align: center;
        color: #666;
    }
</style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Password Reset OTP</h2>
        </div>
        <div class="content">
            <p>Hello,</p>
            <p>You have requested to reset your password. Please use the following One-Time Password (OTP) to proceed:</p>
            <div class="otp">
                <p>{{$body}}</p>
            </div>
            <p>If you did not request this change, please ignore this email.</p>
        </div>
        <div class="footer">
            <p>This email was sent automatically. Please do not reply to it.</p>
        </div>
    </div>
</body>
</html>
