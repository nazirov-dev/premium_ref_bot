<?php

return [

    'title' => 'Tizimga kirish',

    'heading' => 'Tizimga kirish',

    'actions' => [

        'register' => [
            'before' => 'yoki',
            'label' => 'ro\'hatdan o\'tish',
        ],

        'request_password_reset' => [
            'label' => 'Parolingizni unutdingizmi?',
        ],

    ],

    'form' => [

        'email' => [
            'label' => 'Email address',
        ],

        'password' => [
            'label' => 'Password',
        ],

        'remember' => [
            'label' => 'Remember me',
        ],

        'actions' => [

            'authenticate' => [
                'label' => 'Sign in',
            ],

        ],

    ],

    'messages' => [

        'failed' => 'These credentials do not match our records.',

    ],

    'notifications' => [

        'throttled' => [
            'title' => 'Too many login attempts',
            'body' => 'Please try again in :seconds seconds.',
        ],

    ],

];
