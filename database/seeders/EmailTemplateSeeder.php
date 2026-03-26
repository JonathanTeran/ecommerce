<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Bienvenida',
                'slug' => 'welcome',
                'subject' => 'Bienvenido a {store_name}',
                'category' => 'transactional',
                'variables' => ['{customer_name}', '{store_name}'],
                'is_active' => true,
                'body_html' => <<<'HTML'
                <h2>Hola {customer_name}, te damos la bienvenida!</h2>
                <p>Gracias por registrarte en <strong>{store_name}</strong>. Estamos encantados de tenerte con nosotros.</p>
                <p>Ahora puedes explorar nuestro catalogo, agregar productos a tu carrito y disfrutar de una experiencia de compra unica.</p>
                <p>Si tienes alguna pregunta, no dudes en contactarnos.</p>
                <p>Saludos cordiales,<br>El equipo de {store_name}</p>
                HTML,
            ],
            [
                'name' => 'Orden Confirmada',
                'slug' => 'order_confirmed',
                'subject' => 'Tu orden #{order_number} ha sido confirmada',
                'category' => 'transactional',
                'variables' => ['{customer_name}', '{order_number}', '{order_total}'],
                'is_active' => true,
                'body_html' => <<<'HTML'
                <h2>Orden confirmada</h2>
                <p>Hola {customer_name},</p>
                <p>Tu orden <strong>#{order_number}</strong> ha sido confirmada exitosamente.</p>
                <p>Total de la orden: <strong>{order_total}</strong></p>
                <p>Estamos preparando tu pedido y te notificaremos cuando sea enviado.</p>
                <p>Gracias por tu compra!</p>
                HTML,
            ],
            [
                'name' => 'Orden Enviada',
                'slug' => 'order_shipped',
                'subject' => 'Tu orden #{order_number} ha sido enviada',
                'category' => 'transactional',
                'variables' => ['{customer_name}', '{order_number}', '{tracking_number}'],
                'is_active' => true,
                'body_html' => <<<'HTML'
                <h2>Tu pedido esta en camino!</h2>
                <p>Hola {customer_name},</p>
                <p>Tu orden <strong>#{order_number}</strong> ha sido enviada.</p>
                <p>Numero de rastreo: <strong>{tracking_number}</strong></p>
                <p>Puedes seguir el estado de tu envio con el numero de rastreo proporcionado.</p>
                <p>Gracias por tu compra!</p>
                HTML,
            ],
            [
                'name' => 'Restablecer Contrasena',
                'slug' => 'password_reset',
                'subject' => 'Restablecer tu contrasena',
                'category' => 'system',
                'variables' => ['{customer_name}', '{reset_link}'],
                'is_active' => true,
                'body_html' => <<<'HTML'
                <h2>Solicitud de restablecimiento de contrasena</h2>
                <p>Hola {customer_name},</p>
                <p>Hemos recibido una solicitud para restablecer tu contrasena.</p>
                <p>Haz clic en el siguiente enlace para crear una nueva contrasena:</p>
                <p><a href="{reset_link}" style="display:inline-block;padding:12px 24px;background-color:#4F46E5;color:#ffffff;text-decoration:none;border-radius:6px;">Restablecer Contrasena</a></p>
                <p>Si no solicitaste este cambio, puedes ignorar este correo de forma segura.</p>
                <p>Este enlace expirara en 60 minutos.</p>
                HTML,
            ],
            [
                'name' => 'Carrito Abandonado',
                'slug' => 'abandoned_cart',
                'subject' => 'Olvidaste algo en tu carrito',
                'category' => 'marketing',
                'variables' => ['{customer_name}', '{cart_items}', '{cart_total}'],
                'is_active' => true,
                'body_html' => <<<'HTML'
                <h2>Tienes productos esperandote!</h2>
                <p>Hola {customer_name},</p>
                <p>Notamos que dejaste algunos productos en tu carrito:</p>
                {cart_items}
                <p>Total del carrito: <strong>{cart_total}</strong></p>
                <p>Completa tu compra antes de que se agoten!</p>
                <p><a href="#" style="display:inline-block;padding:12px 24px;background-color:#059669;color:#ffffff;text-decoration:none;border-radius:6px;">Completar mi compra</a></p>
                HTML,
            ],
            [
                'name' => 'Tenant Aprobado',
                'slug' => 'tenant_approved',
                'subject' => 'Tu tienda {tenant_name} ha sido aprobada',
                'category' => 'system',
                'variables' => ['{tenant_name}', '{admin_email}', '{login_url}'],
                'is_active' => true,
                'body_html' => <<<'HTML'
                <h2>Tu tienda ha sido aprobada!</h2>
                <p>Felicidades! Tu tienda <strong>{tenant_name}</strong> ha sido aprobada y esta lista para funcionar.</p>
                <p>Puedes acceder al panel de administracion con las siguientes credenciales:</p>
                <ul>
                    <li><strong>Email:</strong> {admin_email}</li>
                    <li><strong>URL de acceso:</strong> <a href="{login_url}">{login_url}</a></li>
                </ul>
                <p>Te recomendamos configurar tu tienda lo antes posible: agregar productos, personalizar el diseno y configurar los metodos de pago.</p>
                <p>Si necesitas ayuda, nuestro equipo de soporte esta disponible para asistirte.</p>
                HTML,
            ],
        ];

        foreach ($templates as $template) {
            EmailTemplate::updateOrCreate(
                ['slug' => $template['slug']],
                $template
            );
        }
    }
}
