<?php

return [

    'column_toggle' => [

        'heading' => 'Ustunlar',

    ],

    'columns' => [

        'text' => [
            'more_list_items' => 'va yana :count ta',
        ],

    ],

    'fields' => [

        'bulk_select_page' => [
            'label' => 'Hamma yozuvlarni tanlash/yo\'qotish',
        ],

        'bulk_select_record' => [
            'label' => ':key yozuvini tanlash/yo\'qotish uchun.',
        ],

        'bulk_select_group' => [
            'label' => ':title guruhini tanlash/yo\'qotish uchun.',
        ],

        'search' => [
            'label' => 'Qidiruv',
            'placeholder' => 'Qidirish uchun matn kiriting',
            'indicator' => 'Qidiruv',
        ],

    ],

    'summary' => [

        'heading' => 'Yig\'indisi',

        'subheadings' => [
            'all' => 'Hamma :label',
            'group' => ':group yig\'indisi',
            'page' => 'Ushbu sahifa',
        ],

        'summarizers' => [

            'average' => [
                'label' => 'O\'rtacha',
            ],

            'count' => [
                'label' => 'Soni',
            ],

            'sum' => [
                'label' => 'Jami',
            ],

        ],

    ],

    'actions' => [

        'disable_reordering' => [
            'label' => 'Yozuvlarni qayta tartiblashni tugatish',
        ],

        'enable_reordering' => [
            'label' => 'Yozuvlarni qayta tartiblash',
        ],

        'filter' => [
            'label' => 'Filtr',
        ],

        'group' => [
            'label' => 'Guruh',
        ],

        'open_bulk_actions' => [
            'label' => 'Kengaytirilgan amallar',
        ],

        'toggle_columns' => [
            'label' => 'Ustunlarni ko\'rsatish/yashirish',
        ],

    ],

    'empty' => [

        'heading' => ':model mavjud emas',

        'description' => ':model yaratishni boshlang.',

    ],

    'filters' => [

        'actions' => [

            'remove' => [
                'label' => 'Filtri olib tashlash',
            ],

            'remove_all' => [
                'label' => 'Barcha filtrlarni olib tashlash',
                'tooltip' => 'Barcha filtrlarni olib tashlash',
            ],

            'reset' => [
                'label' => 'Tiklash',
            ],

        ],

        'heading' => 'Filtrlar',

        'indicator' => 'Faqat faol filtrlar',

        'multi_select' => [
            'placeholder' => 'Hamma',
        ],

        'select' => [
            'placeholder' => 'Hamma',
        ],

        'trashed' => [

            'label' => 'O\'chirilgan yozuvlar',

            'only_trashed' => 'Faqat o\'chirilgan yozuvlar',

            'with_trashed' => 'O\'chirilgan yozuvlar bilan',

            'without_trashed' => 'O\'chirilgan yozuvlarsiz',

        ],

    ],

    'grouping' => [

        'fields' => [

            'group' => [
                'label' => 'Guruhlash',
                'placeholder' => 'Guruhlash',
            ],

            'direction' => [

                'label' => 'Guruhlash tartibi',

                'options' => [
                    'asc' => 'O\'sish tartibida',
                    'desc' => 'Kamayish tartibida',
                ],

            ],

        ],

    ],

    'reorder_indicator' => 'Yozuvlarni tartibga solish uchun tortib oling va ko\'chiring.',

    'selection_indicator' => [

        'selected_count' => '1 yozuv tanlandi|:count yozuv tanlandi',

        'actions' => [

            'select_all' => [
                'label' => 'Hamma :count ni tanlash',
            ],

            'deselect_all' => [
                'label' => 'Hamma tanlovni bekor qilish',
            ],

        ],

    ],

    'sorting' => [

        'fields' => [

            'column' => [
                'label' => 'Saralash tartibi',
            ],

            'direction' => [

                'label' => 'Saralash yo\'nalishi',

                'options' => [
                    'asc' => 'O\'sish tartibida',
                    'desc' => 'Kamayish tartibida',
                ],

            ],

        ],

    ],

];
