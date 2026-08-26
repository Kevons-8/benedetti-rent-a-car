<?php

$LUGARES_RESERVA = [
    'aeropuerto' => [
        'nombre' => 'Aeropuerto Ernesto Cortissoz',
        'costo' => 30000
    ],
    'oficina' => [
        'nombre' => 'Oficina Benedetti Rent a Car',
        'costo' => 0
    ],
    'otro' => [
        'nombre' => 'Otro lugar',
        'costo' => 20000
    ]
];

function obtenerLugaresReserva(): array
{
    global $LUGARES_RESERVA;
    return $LUGARES_RESERVA;
}

function obtenerCostoLugarReserva(string $clave): float
{
    global $LUGARES_RESERVA;

    if (isset($LUGARES_RESERVA[$clave]['costo'])) {
        return (float)$LUGARES_RESERVA[$clave]['costo'];
    }

    return 0;
}

function obtenerNombreLugarReserva(string $clave, string $otroTexto = ''): string
{
    global $LUGARES_RESERVA;

    if ($clave === 'otro') {
        return trim($otroTexto) !== '' ? trim($otroTexto) : 'Otro lugar';
    }

    if (isset($LUGARES_RESERVA[$clave]['nombre'])) {
        return $LUGARES_RESERVA[$clave]['nombre'];
    }

    return '';
}
