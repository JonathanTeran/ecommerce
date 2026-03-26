@extends('emails.layout')

@section('content')
    <h2 style="color: #1e293b; font-size: 22px; margin-bottom: 20px;">
        ¡Hola {{ $userName }}!
    </h2>

    <p style="color: #475569; font-size: 16px; line-height: 1.6; margin-bottom: 20px;">
        Notamos que dejaste algunos productos en tu carrito. ¡No te preocupes, los guardamos para ti!
    </p>

    {{-- Products Table --}}
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 24px;">
        <thead>
            <tr style="background-color: #f1f5f9;">
                <th style="padding: 10px; text-align: left; font-size: 13px; color: #64748b; border-bottom: 1px solid #e2e8f0;">Producto</th>
                <th style="padding: 10px; text-align: center; font-size: 13px; color: #64748b; border-bottom: 1px solid #e2e8f0;">Cant.</th>
                <th style="padding: 10px; text-align: right; font-size: 13px; color: #64748b; border-bottom: 1px solid #e2e8f0;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($cart->items as $item)
                <tr>
                    <td style="padding: 10px; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #334155;">
                        {{ $item->product?->name ?? $item->name ?? 'Producto' }}
                    </td>
                    <td style="padding: 10px; text-align: center; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #334155;">
                        {{ $item->quantity }}
                    </td>
                    <td style="padding: 10px; text-align: right; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #334155;">
                        ${{ number_format($item->subtotal, 2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Total --}}
    <div style="background-color: #f8fafc; border-radius: 8px; padding: 16px; margin-bottom: 24px;">
        <table style="width: 100%;">
            <tr>
                <td style="font-size: 14px; color: #64748b;">Subtotal:</td>
                <td style="text-align: right; font-size: 14px; color: #334155; font-weight: 600;">${{ number_format($cart->subtotal, 2) }}</td>
            </tr>
        </table>
    </div>

    {{-- CTA Button --}}
    <div style="text-align: center; margin: 32px 0;">
        <a href="{{ url('/checkout') }}"
           style="display: inline-block; padding: 14px 32px; background-color: #1e40af; color: #ffffff; text-decoration: none; border-radius: 8px; font-size: 16px; font-weight: 600;">
            Completar mi Compra
        </a>
    </div>

    <p style="color: #94a3b8; font-size: 13px; text-align: center; margin-top: 24px;">
        Los precios y la disponibilidad pueden variar. Si necesitas ayuda, no dudes en contactarnos.
    </p>
@endsection
