<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: sans-serif; background: #f8fafc; padding: 40px 20px;">
    <div style="max-width: 500px; margin: 0 auto; background: white; border-radius: 12px; padding: 32px; border: 1px solid #e2e8f0;">
        <h1 style="font-size: 20px; font-weight: bold; color: #1e293b; margin-bottom: 16px;">{{ __('Verify Your Email') }}</h1>
        <p style="color: #475569; line-height: 1.6;">
            {{ __('Hello') }} {{ $registration->owner_name }},
        </p>
        <p style="color: #475569; line-height: 1.6;">
            {{ __('Thank you for registering your store') }} <strong>{{ $registration->store_name }}</strong>.
            {{ __('Please click the button below to verify your email address.') }}
        </p>
        <div style="text-align: center; margin: 32px 0;">
            <a href="{{ $verifyUrl }}" style="display: inline-block; padding: 12px 32px; background: #4f46e5; color: white; text-decoration: none; border-radius: 8px; font-weight: bold;">
                {{ __('Verify Email') }}
            </a>
        </div>
        <p style="color: #94a3b8; font-size: 13px;">
            {{ __('If you did not create this registration, no further action is required.') }}
        </p>
    </div>
</body>
</html>
