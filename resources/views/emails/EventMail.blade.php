<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Notification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            padding: 20px;
            height: auto
        }
        .email-wrapper {
            max-width: 600px;
            margin: auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background-color: #3e8e41;
            color: #ffffff;
            padding: 20px;
            text-align: center;
        }
        .content {
            padding: 20px;
        }
        .footer {
            background-color: #f1f1f1;
            text-align: center;
            padding: 15px;
            font-size: 12px;
            color: #777777;
        }
        h1 {
            font-size: 24px;
            margin: 0;
        }
        p {
            line-height: 1.5;
            margin: 10px 0;
        }
        .button {
            display: inline-block;
            background-color: #3e8e41;
            color: #ffffff;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="email-wrapper">
            <div class="header">
                <h1>Event Notification</h1>
            </div>
            <div class="content">
                <p><strong>Description:</strong> {{ $description }}</p>
                <a href="#" class="button">View Event Details</a>
            </div>
            <div class="footer">
                <p>&copy; {{ date('Y') }} smart school suite. All Rights Reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
