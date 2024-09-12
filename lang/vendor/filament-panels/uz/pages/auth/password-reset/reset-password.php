<?php

return [

    'title' => 'Parolingizni tiklash',

    'heading' => 'Parolingizni tiklash',

    'form' => [

        'email' => [
            'label' => 'Elektron pochta manzili',
        ],

        'password' => [
            'label' => 'Parol',
            'validation_attribute' => 'parol',
        ],

        'password_confirmation' => [
            'label' => 'Parolni tasdiqlang',
        ],

        'actions' => [

            'reset' => [
                'label' => 'Parolni tiklash',
            ],

        ],

    ],

    'notifications' => [

        'throttled' => [
            'title' => 'Parolni tiklashga juda ko\'p urinishlar',
            'body' => ':seconds soniyada yana bir bor urinib ko\'ring.',
        ],

    ],

];
