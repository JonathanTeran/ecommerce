<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <style>
        body { margin: 0; padding: 0; background-color: #f3f4f6; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; }
        .wrapper { width: 100%; background-color: #f3f4f6; padding: 40px 0; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .header { background-color: #1e40af; padding: 30px 40px; text-align: center; }
        .header img { max-height: 50px; width: auto; }
        .header h1 { color: #ffffff; font-size: 20px; font-weight: 700; margin: 0; }
        .body { padding: 40px; }
        .body h2 { font-size: 22px; color: #111827; margin: 0 0 16px 0; }
        .body p { font-size: 15px; color: #4b5563; line-height: 1.6; margin: 0 0 16px 0; }
        .highlight-box { background-color: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 20px; margin: 20px 0; }
        .highlight-box .label { font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 4px 0; }
        .highlight-box .value { font-size: 20px; color: #1e40af; font-weight: 700; margin: 0; }
        .items-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .items-table th { text-align: left; padding: 10px 12px; background-color: #f9fafb; border-bottom: 2px solid #e5e7eb; font-size: 12px; color: #6b7280; text-transform: uppercase; }
        .items-table td { padding: 12px; border-bottom: 1px solid #f3f4f6; font-size: 14px; color: #374151; }
        .items-table .text-right { text-align: right; }
        .totals { margin: 20px 0; }
        .totals-row { display: flex; justify-content: space-between; padding: 8px 0; font-size: 14px; color: #6b7280; }
        .totals-row.total { border-top: 2px solid #1e40af; padding-top: 12px; margin-top: 8px; font-size: 18px; color: #1e40af; font-weight: 700; }
        .btn { display: inline-block; background-color: #1e40af; color: #ffffff; padding: 14px 28px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 15px; }
        .btn:hover { background-color: #1d4ed8; }
        .alert-danger { background-color: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 20px; margin: 20px 0; }
        .alert-danger p { color: #991b1b; margin: 0; }
        .alert-success { background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 20px; margin: 20px 0; }
        .alert-success p { color: #166534; margin: 0; }
        .footer { padding: 24px 40px; text-align: center; border-top: 1px solid #e5e7eb; }
        .footer p { font-size: 12px; color: #9ca3af; margin: 0; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                @if($settings?->site_logo)
                    <img src="{{ url('storage/' . $settings->site_logo) }}" alt="Logo">
                @else
                    <h1>{{ $settings?->site_name ?? config('app.name') }}</h1>
                @endif
            </div>

            <div class="body">
                @yield('content')
            </div>

            <div class="footer">
                <p>{{ $settings?->site_name ?? config('app.name') }} &mdash; {{ now()->format('Y') }}</p>
                <p>Este email fue enviado automaticamente. Por favor no responda a este correo.</p>
            </div>
        </div>
    </div>
</body>
</html>
