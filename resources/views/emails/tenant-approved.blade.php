<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"></head>
<body style="margin: 0; padding: 0; background: #f4f4f5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
    <div style="max-width: 600px; margin: 40px auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
        <div style="background: linear-gradient(135deg, #4f46e5, #6366f1); padding: 40px 32px; text-align: center;">
            <h1 style="color: #fff; font-size: 24px; margin: 0;">Tu Tienda esta Lista</h1>
            <p style="color: rgba(255,255,255,0.8); margin: 8px 0 0; font-size: 14px;">Ya puedes empezar a vender</p>
        </div>

        <div style="padding: 32px;">
            <p style="color: #374151; font-size: 16px; line-height: 1.6;">
                Hola, tu tienda <strong>{{ $tenant->name }}</strong> ha sido aprobada y esta lista para configurar.
            </p>

            <div style="background: #f9fafb; border-radius: 8px; padding: 20px; margin: 24px 0;">
                <p style="margin: 0 0 8px; font-size: 14px; color: #6b7280;">Acceso al Panel de Administracion:</p>
                <p style="margin: 0 0 4px; font-size: 14px;"><strong>Email:</strong> {{ $adminEmail }}</p>
                <p style="margin: 0; font-size: 14px;"><strong>URL:</strong> <a href="{{ $loginUrl }}" style="color: #4f46e5;">{{ $loginUrl }}</a></p>
            </div>

            <div style="text-align: center; margin: 32px 0;">
                <a href="{{ $loginUrl }}" style="display: inline-block; background: #4f46e5; color: #fff; padding: 14px 32px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 15px;">
                    Configurar Mi Tienda
                </a>
            </div>

            <div style="border-top: 1px solid #e5e7eb; padding-top: 20px; margin-top: 24px;">
                <p style="color: #9ca3af; font-size: 13px; text-align: center; margin: 0;">
                    Si no solicitaste esta cuenta, puedes ignorar este correo.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
