# Manual de Operacion — Amephia Ecommerce SaaS

## 1. Arquitectura del Sistema

### 1.1 Multi-Tenancy

El sistema opera como un **SaaS multi-tenant**: una sola instalacion sirve a multiples tiendas (tenants), cada una con sus propios datos, usuarios, productos, pedidos y configuraciones aislados entre si.

**Componentes clave:**
- **Tenant**: Representa una tienda/negocio. Cada tenant tiene su propio dominio, logo, tema y configuraciones.
- **Plan**: Define los modulos y limites disponibles (productos, usuarios).
- **Suscripcion**: Vincula un tenant con un plan. Solo una suscripcion activa por tenant.

### 1.2 Roles del Sistema

| Rol | Panel | Ruta | Descripcion |
|-----|-------|------|-------------|
| **Super Admin** | Super Admin | `/super-admin` | Administra todos los tenants, planes y suscripciones |
| **Admin (Tenant)** | Admin | `/admin` | Administra su tienda: productos, pedidos, inventario, configuracion |
| **Cliente** | Storefront | `/` | Navega la tienda, compra, cotiza, gestiona su cuenta |

### 1.3 Modulos por Plan

| Modulo | Basico ($50) | Profesional ($100) | Enterprise ($150) |
|--------|:---:|:---:|:---:|
| Productos, Categorias, Marcas | Si | Si | Si |
| Pedidos, Carrito, Storefront | Si | Si | Si |
| Cotizaciones | Si | Si | Si |
| Inventario (Kardex, Almacenes) | No | Si | Si |
| Cupones de Descuento | No | Si | Si |
| Resenas de Clientes | No | Si | Si |
| Banners Promocionales | No | Si | Si |
| Reportes y Dashboard | No | Si | Si |
| Facturacion Electronica SRI | No | No | Si |
| Pasarelas de Pago Avanzadas | No | No | Si |
| API REST | No | No | Si |

**Limites:**

| | Basico | Profesional | Enterprise |
|--|--------|-------------|------------|
| Productos | 500 | Ilimitado | Ilimitado |
| Usuarios | 1 | 3 | Ilimitado |

---

## 2. Instalacion y Configuracion Inicial

### 2.1 Requisitos

- PHP >= 8.3
- Composer
- Node.js >= 18 y NPM
- MySQL 8+ o SQLite (desarrollo)
- Extensiones PHP: BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML, GD/Imagick

### 2.2 Instalacion

```bash
# 1. Clonar repositorio
git clone <url-del-repositorio> ecommerce
cd ecommerce

# 2. Instalar dependencias PHP
composer install

# 3. Instalar dependencias frontend
npm install

# 4. Configurar entorno
cp .env.example .env
php artisan key:generate

# 5. Configurar base de datos en .env
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=ecommerce
# DB_USERNAME=root
# DB_PASSWORD=

# 6. Ejecutar migraciones y seeders
php artisan migrate --seed

# 7. Compilar assets frontend
npm run build

# 8. (Opcional) Iniciar servidor de desarrollo
php artisan serve
```

### 2.3 Credenciales por Defecto

Despues de ejecutar `php artisan migrate --seed`:

| Usuario | Email | Password | Rol |
|---------|-------|----------|-----|
| Super Admin | superadmin@amephia.com | password | super_admin |
| Admin Demo | admin@compupiezas.com | password | admin (tenant CompuPiezas) |

### 2.4 Desarrollo Local — Resolucion de Tenant

Para desarrollo local (localhost), configure el tenant por defecto en `.env`:

```env
DEFAULT_TENANT_SLUG=compupiezas
```

Esto permite que el storefront en `localhost` resuelva automaticamente al tenant "CompuPiezas" sin necesidad de configurar un dominio personalizado.

**En produccion**, cada tenant debe tener su propio dominio asignado (ej: `tienda.midominio.com`).

---

## 3. Panel Super Admin (`/super-admin`)

Acceso: `superadmin@amephia.com` / `password`

### 3.1 Dashboard

Muestra metricas globales del SaaS:
- **Tenants Activos**: Numero de tiendas activas
- **Suscripciones Activas**: Suscripciones vigentes
- **MRR (Ingreso Mensual Recurrente)**: Suma de los precios de planes activos
- **Planes Disponibles**: Planes habilitados

### 3.2 Gestion de Tenants

**Menu: Gestion SaaS > Tenants**

#### Crear Nuevo Tenant

Al crear un tenant se provisionan automaticamente:
1. El tenant con su configuracion base
2. Un registro de `GeneralSetting` (nombre del sitio, tasa de impuesto 15%)
3. Una suscripcion activa con el plan seleccionado
4. Un usuario administrador con email verificado

**Formulario de creacion:**
- **Nombre**: Nombre de la tienda
- **Slug**: Identificador unico (se genera automaticamente del nombre)
- **Dominio**: Dominio web de la tienda (ej: `tienda.ejemplo.com`)
- **Color del Tema**: Indigo, Blue, Emerald, Amber, Red, Slate
- **Activo**: Si/No
- **Fin del periodo de prueba**: Fecha opcional
- **Plan**: Seleccionar plan (Basico, Profesional, Enterprise)
- **Administrador Inicial**: Nombre, email y password del primer admin

#### Editar Tenant

En la vista de edicion se pueden ver:
- **Pestana Usuarios**: Lista de usuarios del tenant con sus roles
- **Pestana Suscripciones**: Historial de suscripciones, crear nuevas, editar estado

#### Desactivar Tenant

Desmarcar "Activo" para deshabilitar el acceso al storefront (muestra error 503).

### 3.3 Gestion de Planes

**Menu: Gestion SaaS > Planes**

Configurar los planes disponibles:
- Nombre, tipo, precio mensual
- Limites de productos y usuarios (null = ilimitado)
- Modulos habilitados (seleccion multiple)
- Estado activo/inactivo

### 3.4 Gestion de Suscripciones

**Menu: Gestion SaaS > Suscripciones**

Ver y gestionar todas las suscripciones:
- Tenant, plan, estado, fechas de inicio/fin
- Estados: Activa, Periodo de Prueba, Vencida, Cancelada

---

## 4. Panel Admin de Tenant (`/admin`)

Acceso: Email y password del administrador del tenant.

El panel muestra solo los modulos habilitados segun el plan del tenant.

### 4.1 Dashboard

Widgets con metricas de la tienda:
- Ingresos del mes, pedidos del mes, ticket promedio
- Grafico de ingresos, pedidos en el tiempo
- Productos mas vendidos
- Pedidos por estado
- Nuevos registros de usuarios
- Productos mejor y peor valorados
- Ultimos pedidos

### 4.2 Productos

**Menu: Gestion de Tienda > Productos**

#### Crear Producto
- **Basico**: Nombre, slug, precio, precio comparativo, costo, SKU
- **Descripcion**: Descripcion corta y completa (editor enriquecido)
- **Categorizacion**: Categoria, marca
- **Imagenes**: Imagen principal + galeria (arrastrar y soltar)
- **Inventario**: Cantidad en stock, umbral de stock bajo
- **Variantes**: Tallas, colores, etc. con precio y SKU propio
- **Especificaciones**: Pares clave-valor
- **SEO**: Meta titulo, meta descripcion
- **Estado**: Activo, Destacado, Nuevo

#### Acciones
- **Ajustar Stock**: Incrementar/decrementar stock directamente
- **Imprimir Codigo de Barras**: Genera codigo imprimible
- **Ver Movimientos de Inventario**: Historial del Kardex

### 4.3 Categorias

**Menu: Gestion de Tienda > Categorias**

- Jerarquia padre-hijo
- Icono, posicion de ordenamiento
- Prefijo de SKU (se usa al generar SKUs de productos)
- SEO: meta titulo, meta descripcion
- Estados: Activa, Destacada

### 4.4 Marcas

**Menu: Gestion de Tienda > Marcas**

- Logo de marca (carga de imagen)
- Sitio web, posicion de ordenamiento
- SEO y estados

### 4.5 Pedidos

**Menu: Gestion de Tienda > Pedidos**

Badge en el menu muestra la cantidad de pedidos pendientes.

#### Flujo de Estados

```
Pendiente → Confirmado → Procesando → Enviado → Entregado
                ↓              ↓          ↓
            En Espera    En Espera   En Espera
                ↓              ↓          ↓
            Cancelado    Cancelado   Cancelado
                                        ↓
                                    Reembolsado
```

#### Acciones del Admin
1. **Cambiar estado**: Seleccionar nuevo estado + notas opcionales
2. **Agregar numero de seguimiento**: Para envios con Servientrega
3. **Notas de administrador**: Notas internas no visibles al cliente
4. **Facturacion SRI** (Enterprise): Generar factura electronica, verificar autorizacion

#### Emails Automaticos
- **Pedido Confirmado**: Al crear el pedido
- **Pedido Enviado**: Al cambiar a "Enviado"
- **Pedido Entregado**: Al cambiar a "Entregado"
- **Pedido Cancelado**: Al cancelar

### 4.6 Cotizaciones

**Menu: Gestion de Tienda > Cotizaciones**

Las cotizaciones las crean los clientes desde el storefront. El admin solo gestiona.

#### Acciones del Admin
- **Aprobar**: Marca como aprobada y envia email al cliente
- **Rechazar**: Requiere motivo, envia email al cliente
- **Convertir a Pedido**: Crea un pedido a partir de la cotizacion aprobada (descuenta stock)
- **Descargar PDF**: Genera PDF profesional de la cotizacion
- **Notas de Admin**: Agregar notas internas

### 4.7 Inventario (Plan Profesional+)

#### Ubicaciones de Almacen
- Crear multiples almacenes (nombre, codigo, direccion)
- Marcar uno como almacen por defecto

#### Kardex (Movimientos de Inventario)
Vista de solo lectura con el historial completo de movimientos:
- Tipos: Saldo inicial, Compra, Venta, Devolucion, Ajuste (+/-), Transferencia (entrada/salida)
- Filtros por producto, tipo, fecha, almacen
- Muestra balance, costo unitario, usuario que registro

#### Transferencias de Stock
- Crear transferencia entre almacenes
- Seleccionar productos y cantidades
- Flujo: Borrador → En Transito → Completada (o Cancelada)
- Al completar, se registran movimientos en el Kardex automaticamente

#### Alertas de Stock
- Se generan automaticamente cuando el stock baja del umbral
- Tipos: Stock bajo, Agotado, Sobrestock
- Acciones: Reconocer alerta, Marcar como resuelta

### 4.8 Cupones de Descuento (Plan Profesional+)

**Menu: Marketing > Cupones**

- Codigo unico del cupon
- Tipo: Monto fijo, Porcentaje, Envio gratis
- Valor del descuento
- Limites de uso (global y por usuario)
- Monto minimo de pedido, descuento maximo
- Fechas de inicio y expiracion
- Aplicable a categorias/productos especificos
- Productos excluidos
- Solo para primera compra (opcional)

### 4.9 Banners Promocionales (Plan Profesional+)

**Menu: Marketing > Banners**

- Imagen desktop y movil
- Boton CTA con texto y URL
- Posicion: Inicio superior, Inicio medio
- Programacion con fechas de inicio/fin
- Orden de aparicion

### 4.10 Resenas (Plan Profesional+)

**Menu: Gestion de Tienda > Resenas**

Moderacion de resenas de clientes:
- Filtro por defecto: muestra pendientes
- Acciones: Aprobar, Rechazar
- Solo clientes con compra verificada (pedido entregado) pueden dejar resena
- Calificacion 1-5 estrellas, titulo, comentario

### 4.11 Metodos de Pago

**Menu: Gestion de Tienda > Metodos de Pago**

- Nombre, slug, descripcion
- Instrucciones (editor enriquecido) — se muestra al cliente al seleccionar
- Recargo porcentual (ej: 5% para tarjeta de credito)
- Requiere comprobante de pago (si/no)
- Estado activo/inactivo

### 4.12 Usuarios

**Menu: Gestion de Usuarios > Usuarios**

Lista de usuarios del tenant:
- Nombre, email, telefono, tipo de identificacion
- Estado activo, verificacion de email
- Ultimo acceso

### 4.13 Configuracion

#### Ajustes Generales
**Menu: Configuracion > Configuracion General**

- **General**: Nombre del sitio, idioma, tasa de impuesto (IVA), color del tema
- **Logos**: Logo y favicon del sitio
- **Email**: Configuracion SMTP personalizada (Host, Puerto, Encriptacion, Usuario, Password, Nombre y email del remitente)

#### Facturacion SRI (Plan Enterprise)
**Menu: Configuracion > Facturacion SRI**

Configuracion de facturacion electronica para Ecuador:
- **Datos del Contribuyente**: RUC (13 digitos), razon social, nombre comercial, direccion del establecimiento
- **Codigos de Emision**: Codigo de establecimiento (3 digitos), punto de emision (3 digitos), numero de secuencia
- **Firma Electronica**: Subir archivo .p12, ingresar password
- **Ambiente**: Pruebas (1) o Produccion (2)
- **Tipo de Contribuyente**: Normal, Especial, RISE, etc.

---

## 5. Storefront (Tienda del Cliente)

### 5.1 Navegacion

- **Inicio** (`/`): Productos destacados, tendencia, categorias con productos, marcas
- **Tienda** (`/shop`): Catalogo completo con filtros (categoria, marca, precio, busqueda, ordenamiento)
- **Categorias** (`/categories`): Vista de todas las categorias
- **Marcas** (`/brands`): Directorio de marcas
- **Producto** (`/products/{slug}`): Detalle con imagenes, variantes, resenas, productos relacionados

### 5.2 Carrito de Compras

El carrito funciona via API (Alpine.js en frontend):
- Agregar/quitar productos con cantidades
- Seleccionar variantes
- Aplicar/remover cupones de descuento
- Persistencia por sesion (invitados) o por usuario (autenticados)
- Merge automatico: al iniciar sesion, el carrito de invitado se fusiona con el del usuario

### 5.3 Checkout (`/checkout`)

Flujo completo:
1. **Direccion de Envio**: Nombre, cedula/RUC, email, direccion, ciudad, provincia, codigo postal, telefono
2. **Direccion de Facturacion**: Opcion de usar la misma o ingresar otra
3. **Metodo de Pago**: Seleccionar entre los metodos configurados por el tenant
4. **Comprobante de Pago**: Si el metodo lo requiere, subir imagen/PDF
5. **Aceptar Terminos Legales**: Terminos de servicio, politica de privacidad
6. **Resumen del Pedido**: Items, subtotal, impuesto, descuento, recargo, total
7. **Confirmar Pedido**: Crea el pedido y redirige a pagina de confirmacion

### 5.4 Cotizaciones

Los clientes pueden solicitar cotizacion sin realizar pago:
- **Solicitar** (`/quotation`): Formulario con datos del cliente y direccion
- **Confirmacion** (`/quotation/confirmation/{id}`): Numero de cotizacion y validez
- **Mis Cotizaciones** (`/my-quotations`): Lista con estado y acciones
- **Detalle** (`/my-quotations/{id}`): Items, totales, estado, notas del admin
- **Descargar PDF** (`/my-quotations/{id}/pdf`): PDF profesional (si fue aprobada)

### 5.5 Cuenta del Cliente

Acceso despues de iniciar sesion:
- **Pedidos** (`/account/orders`): Historial de pedidos con estado
- **Detalle de Pedido** (`/account/orders/{id}`): Items, tracking, estado
- **Perfil** (`/account/profile`): Editar nombre, telefono, numero de identificacion
- **Direcciones** (`/account/addresses`): Agregar, editar, eliminar direcciones
- **Wishlist** (`/account/wishlist`): Productos guardados
- **Cambiar Password**: Desde el perfil

### 5.6 Autenticacion

- **Iniciar Sesion**: Modal en el navbar (email + password)
- **Registrarse**: Modal (nombre, email, password, aceptar terminos)
- **Recuperar Password**: Envio de link de restablecimiento por email

---

## 6. Flujos Principales

### 6.1 Alta de Nuevo Tenant

1. Super Admin accede a `/super-admin`
2. **Gestion SaaS > Tenants > Crear**
3. Llena: nombre de la tienda, slug, dominio, plan, datos del admin
4. El sistema automaticamente crea:
   - Tenant con configuracion base
   - Ajustes generales (nombre del sitio, IVA 15%)
   - Suscripcion activa con el plan elegido
   - Usuario administrador con email verificado
5. El admin del tenant puede acceder a `/admin` con sus credenciales

### 6.2 Primera Configuracion del Tenant

1. Admin accede a `/admin`
2. **Configuracion > Configuracion General**:
   - Subir logo y favicon
   - Ajustar tasa de impuesto si es diferente al 15%
   - Configurar SMTP para emails salientes
3. **Metodos de Pago > Crear**:
   - Al menos un metodo de pago (ej: Transferencia Bancaria)
   - Configurar instrucciones para el cliente
4. **Categorias > Crear**:
   - Definir estructura de categorias
5. **Productos > Crear**:
   - Cargar catalogo de productos con imagenes y precios
6. Verificar el storefront accediendo al dominio configurado

### 6.3 Ciclo de Venta

```
Cliente navega storefront
        ↓
Agrega productos al carrito
        ↓
Procede al checkout
        ↓
Ingresa datos de envio/facturacion
        ↓
Selecciona metodo de pago
        ↓
Confirma pedido → Email de confirmacion
        ↓
Admin ve pedido pendiente (badge en menu)
        ↓
Admin confirma pedido
        ↓
Admin procesa pedido
        ↓
Admin envia pedido → Email al cliente
        ↓
Admin marca como entregado → Email al cliente
```

### 6.4 Ciclo de Cotizacion

```
Cliente navega storefront
        ↓
Agrega productos al carrito
        ↓
Solicita cotizacion (en vez de comprar)
        ↓
Ingresa datos de contacto → Email de cotizacion recibida
        ↓
Admin revisa cotizacion en /admin
        ↓
Admin aprueba → Email al cliente
   o rechaza (con motivo) → Email al cliente
        ↓
Si aprobada: Admin convierte a pedido
        ↓
Se crea pedido y se descuenta stock
        ↓
Continua flujo normal de pedido
```

---

## 7. Configuracion de Produccion

### 7.1 Variables de Entorno Importantes

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tudominio.com

# Base de datos
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=ecommerce
DB_USERNAME=usuario
DB_PASSWORD=secreto

# Cola de trabajos (para emails asincrono)
QUEUE_CONNECTION=database

# Email por defecto (fallback si el tenant no configura SMTP)
MAIL_MAILER=smtp
MAIL_HOST=smtp.ejemplo.com
MAIL_PORT=587
MAIL_USERNAME=tu@email.com
MAIL_PASSWORD=secreto

# Tenant por defecto (solo para desarrollo)
# DEFAULT_TENANT_SLUG=compupiezas
```

### 7.2 Dominios por Tenant

Cada tenant necesita un dominio configurado en:
1. **DNS**: Apuntar el dominio al servidor
2. **Super Admin**: Asignar el dominio en la configuracion del tenant
3. **Servidor Web**: Configurar el virtual host o proxy reverso

### 7.3 Cola de Trabajos

Para emails asincronos y verificacion de facturas SRI:

```bash
php artisan queue:work --daemon
```

O configurar un supervisor para mantener el worker activo.

---

## 8. Soporte Tecnico

### 8.1 Comandos Utiles

```bash
# Ejecutar migraciones pendientes
php artisan migrate

# Limpiar cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Recompilar assets
npm run build

# Ejecutar tests
php artisan test --compact

# Ejecutar un seeder especifico
php artisan db:seed --class=PlanSeeder
```

### 8.2 Estructura de Archivos Clave

```
app/
├── Enums/              # OrderStatus, PaymentStatus, Module, PlanType, etc.
├── Filament/
│   ├── Resources/      # 14 recursos del panel Admin (tenant)
│   ├── SuperAdmin/     # Recursos del panel Super Admin
│   ├── Buyer/          # Recursos del portal de compradores
│   ├── Pages/          # Paginas de configuracion
│   ├── Widgets/        # Widgets del dashboard
│   └── Concerns/       # RequiresModule trait
├── Http/
│   ├── Controllers/    # Controladores del storefront
│   ├── Middleware/      # ResolveTenant, ResolveStorefrontTenant
│   └── Requests/       # Form Requests de validacion
├── Models/
│   ├── Concerns/       # BelongsToTenant trait
│   └── ...             # 25+ modelos Eloquent
├── Services/           # SriService, InventoryService, QuotationService, etc.
├── Mail/               # 8 clases de email
└── Jobs/               # CheckSriAuthorizationStatus

config/
└── tenancy.php         # Configuracion de tenant por defecto

database/
├── migrations/         # Migraciones de base de datos
├── factories/          # Factories para tests
└── seeders/            # SuperAdmin, Plan, DemoTenant seeders
```
