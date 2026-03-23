<?php
declare(strict_types=1);

return [
    'site' => [
        'name' => 'Camping del Monasterio',
        'year' => '2026',
        'base_url' => 'http://localhost/camping'
    ],

    'mail' => [
        'from_email' => 'no-reply@campingdelmonasterio.com',
        'from_name'  => 'Camping del Monasterio',
        'reply_to'   => 'reservas@campingdelmonasterio.com',

        // SMTP
        'host'       => 'smtp.tudominio.com',
        'port'       => 587,
        'username'   => 'no-reply@campingdelmonasterio.com',
        'password'   => 'CAMBIAR_PASSWORD_SMTP',
        'encryption' => 'tls',

        'admin_emails' => [
            'fernando.m.gambino@gmail.com',
            'reservas@campingdelmonasterio.com',
        ],
    ],

    'whatsapp' => [
        'admin_number' => '543816150488',
    ],

    'payment' => [
        'mercadopago_checkout_url' => 'https://www.mercadopago.com.ar/',
        'transferencia_alias'      => 'camping.del.monasterio',
        'transferencia_cbu'        => '0000003100000000000000',
        'transferencia_titular'    => 'Camping del Monasterio',
    ],

    'packages' => [
        'scout' => [
            'id' => 'scout',
            'title' => 'Campamento Scout',
            'days' => 5,
            'nights' => 4,
            'pricing_mode' => 'per_person',
            'adult_price' => 150000,
            'youth_price' => 130000,
        ],
        'trekking' => [
            'id' => 'trekking',
            'title' => 'Aventura Trekking',
            'days' => 2,
            'nights' => 1,
            'pricing_mode' => 'per_person',
            'adult_price' => 150000,
            'youth_price' => 130000,
        ],
        'escolar' => [
            'id' => 'escolar',
            'title' => 'Campamento Escolar',
            'days' => 3,
            'nights' => 2,
            'pricing_mode' => 'per_person',
            'adult_price' => 150000,
            'youth_price' => 130000,
        ],
        'finde' => [
            'id' => 'finde',
            'title' => 'Fines de Semana de Descanso',
            'days' => 1,
            'nights' => 0,
            'pricing_mode' => 'fixed',
            'package_price' => 90000,
        ],
        'familiar' => [
            'id' => 'familiar',
            'title' => 'Pack Familiar',
            'days' => 2,
            'nights' => 1,
            'pricing_mode' => 'fixed',
            'package_price' => 160000,
        ],
    ],
];