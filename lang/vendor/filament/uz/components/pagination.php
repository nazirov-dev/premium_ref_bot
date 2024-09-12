<?php

return [

    'label' => 'Sahifalar navigatsiyasi',

    'overview' => '{1} 1 natija ko‘rsatilmoqda|[2,*] :first dan :last gacha :total natijalar ko‘rsatilmoqda',

    'fields' => [

        'records_per_page' => [

            'label' => 'Har bir sahifaga',

            'options' => [
                'all' => 'Hammasi',
            ],

        ],

    ],

    'actions' => [

        'go_to_page' => [
            'label' => ':page sahifasiga o‘tish',
        ],

        'next' => [
            'label' => 'Keyingi',
        ],

        'previous' => [
            'label' => 'Oldingi',
        ],

    ],

];
