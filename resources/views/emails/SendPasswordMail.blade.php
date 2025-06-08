<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <title>Your OTP Code</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            font-family: 'poppins', sans-serif;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            height: auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            background-color: #007bff;
            color: #ffffff;
            padding: 10px 20px;
            font-size: 24px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            padding: 20px;
            font-size: 18px;
            color: #333333;
        }
        .otp {
            display: block;
            font-size: 36px;
            font-weight: bold;
            margin: 20px 0;
            text-align: center;
            color: #007bff;
        }
        .footer {
            padding: 10px 20px;
            text-align: center;
            color: #777777;
            font-size: 14px;
            border-top: 1px solid #eaeaea;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            Your OTP Code
        </div>
        <div class="content">
            <p>Hi there!</p>
            <p>You have requested an OTP (One-Time Password) for your account access. Please enter the following OTP:</p>
            <span class="otp">{{ $password }}</span>
            <p>This code is valid for the next 5 minutes and should not be shared with anyone.</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }}  schoolsmartsuite. All rights reserved.
        </div>
    </div>
</body>
</html>
