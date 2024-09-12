<?php

return [

    'title' => 'Email pochtangizni tasdiqlang',

    'heading' => 'Email pochtangizni tasdiqlang',

    'actions' => [

        'resend_notification' => [
            'label' => 'Qayta yuborish',
        ],

    ],

    'messages' => [
        'notification_not_received' => 'Biz yuborgan xatni olmadingizmi?',
        'notification_sent' => 'Biz :email manziliga e-pochta manzilingizni qanday tekshirish bo‘yicha ko‘rsatmalarni o‘z ichiga olgan xat yubordik.',
    ],

    'notifications' => [

        'notification_resent' => [
            'title' => 'E-pochtani qayta yubordik.',
        ],

        'notification_resend_throttled' => [
            'title' => 'Juda koʻp qayta yuborish urinishlari',
            'body' => 'Iltimos, :seconds soniyadan keyin qayta urinib ko\'ring.',
        ],

    ],

];
