@extends('errors::minimal')

@section('title', __('Server Error'))
@section('code', '500')
@section('message', __('¡Guau! Algo salió mal.'))
@section('description', 'Nuestros servidores tuvieron un pequeño problema técnico. Nuestro equipo de soporte (y perritos
    de servicio) ya está trabajando en ello.')
