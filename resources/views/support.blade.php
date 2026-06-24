@extends('layouts.app')

@section('title', 'Soporte')
@section('page-title', 'Centro de soporte')
@section('page-subtitle', 'Asistencia clínica y técnica para el personal de salud')

@section('content')
<div class="grid gap-6 lg:grid-cols-3">
    <x-ui.card title="Contacto de soporte" class="lg:col-span-2">
        <p class="mb-6 text-base-content/65">
            Equipo disponible para incidencias de inventario, trazabilidad, permisos y fármacos controlados.
        </p>

        <form class="space-y-4">
            <div class="grid gap-4 md:grid-cols-2">
                <x-ui.field label="Nombre" for="support_name">
                    <x-ui.input id="support_name" type="text" placeholder="Su nombre" />
                </x-ui.field>
                <x-ui.field label="Correo institucional" for="support_email">
                    <x-ui.input id="support_email" type="email" placeholder="nombre@residencia.cl" />
                </x-ui.field>
            </div>

            <x-ui.field label="Tipo de consulta" for="support_type">
                <x-ui.select id="support_type">
                    <option>Inventario / Kardex</option>
                    <option>Trazabilidad por residente</option>
                    <option>Fármacos restringidos</option>
                    <option>Accesos y permisos</option>
                    <option>Incidencia técnica</option>
                </x-ui.select>
            </x-ui.field>

            <x-ui.field label="Mensaje" for="support_message">
                <x-ui.textarea id="support_message" rows="5"
                          placeholder="Describa su consulta con el mayor detalle posible"></x-ui.textarea>
            </x-ui.field>

            <button type="button" class="btn btn-primary">Enviar solicitud</button>
        </form>
    </x-ui.card>

    <div class="space-y-4">
        <x-ui.card title="Canales directos">
            <ul class="space-y-2 text-sm">
                <li><span class="font-medium">Teléfono:</span> +56 2 2345 6789</li>
                <li><span class="font-medium">Correo:</span> soporte@acalis-pharma.cl</li>
                <li><span class="font-medium">Horario:</span> Lun–Dom 24/7</li>
            </ul>
        </x-ui.card>

        <div class="vx-welcome-banner !p-5">
            <div>
                <h3 class="font-semibold">Guías rápidas</h3>
                <ul class="mt-3 space-y-2 text-sm text-white/90">
                    <li>• Registrar salida por merma</li>
                    <li>• Administración a residente</li>
                    <li>• Traslado entre bodegas</li>
                    <li>• Reportes de valorización</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
