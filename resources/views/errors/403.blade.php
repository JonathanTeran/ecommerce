@extends('errors::minimal')

@section('title', __('Forbidden'))
@section('code', '403')
@section('message', __('Acceso Restringido 🐶'))
@section('description', 'Lo sentimos, pero no tienes permiso para acceder a esta área. Esta zona es solo para personal
    autorizado.')
