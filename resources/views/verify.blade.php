<!DOCTYPE html>
<html>
<head>
    <title>Verify Your Email</title>
</head>
<body>
    <h2>Hello {{ $user->name }},</h2>
    <p>Thank you for signing up! Please click the link below to verify your email:</p>
    <a href="{{ route('verify.email', $user->email_verification_token) }}" style="display:inline-block;padding:10px 20px;color:#fff;background-color:#007bff;text-decoration:none;border-radius:5px;">
        Verify Email
    </a>
    <p>If you did not register, ignore this email.</p>
</body>
</html>
