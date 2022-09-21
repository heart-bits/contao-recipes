<?php

use Heartbits\ContaoRecipes\EventListener\DataContainer\RecipeCallbackListener;

$GLOBALS['TL_DCA']['tl_recipe_unit'] = [
    // Config
    'config' => [
        'dataContainer' => 'Table',
        'enableVersioning' => true,
        'sql' => [
            'keys' => [
                'id' => 'primary'
            ]
        ],
        'backlink' => 'do=recipe',
    ],
    // List
    'list' => [
        'sorting' => [
            'mode' => 1,
            'fields' => ['title'],
            'flag' => 1,
            'panelLayout' => 'search,limit'
        ],
        'label' => [
            'fields' => ['title'],
            'format' => '%s',
        ],
        'global_operations' => [
            'all' => [
                'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"'
            ]
        ],
        'operations' => [
            'edit' => [
                'label' => &$GLOBALS['TL_LANG']['tl_recipe_category']['edit'],
                'href' => 'act=edit',
                'icon' => 'edit.svg'
            ],
            'copy' => [
                'label' => &$GLOBALS['TL_LANG']['tl_recipe_category']['copy'],
                'href' => 'act=copy',
                'icon' => 'copy.svg',
                'attributes' => 'onclick="Backend.getScrollOffset()"'
            ],
            'delete' => [
                'label' => &$GLOBALS['TL_LANG']['tl_recipe_category']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.svg',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
            ],
            'show' => [
                'label' => &$GLOBALS['TL_LANG']['tl_recipe_category']['show'],
                'href' => 'act=show',
                'icon' => 'show.svg',
                'attributes' => 'style="margin-right:3px"'
            ],
        ]
    ],

    // Palettes
    'palettes' => [
        '__selector__' => ['title'],
        'default' => '{unit_legend},title,alias,shortcode;{expert_legend:hide},invisible;',
    ],

    // Fields
    'fields' => [
        'id' => [
            'sql' => "int(10) unsigned NOT NULL auto_increment"
        ],

        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ],

        'title' => [
            'inputType' => 'text',
            'exclude' => true,
            'sorting' => true,
            'search' => true,
            'eval' => [
                'unique' => true,
                'mandatory' => true,
                'maxlength' => 255,
                'tl_class' => 'w50 clr'
            ],
            'sql' => "varchar(255) NOT NULL default ''"
        ],

        'alias' => [
            'inputType' => 'text',
            'exclude' => true,
            'eval' => [
                'rgxp' => 'alias',
                'maxlength' => 255,
                'tl_class' => 'w50',
                'doNotCopy' => true
            ],
            'save_callback' => [
                [RecipeCallbackListener::class, 'onSaveCallback']
            ],
            'sql' => "varchar(255) NOT NULL default ''"
        ],

        'shortcode' => [
            'inputType' => 'text',
            'exclude' => true,
            'eval' => [
                'unique' => true,
                'mandatory' => true,
                'maxlength' => 255,
                'tl_class' => 'w50 clr',
                'doNotCopy' => true
            ],
            'sql' => "varchar(255) NOT NULL default ''"
        ],
    ]
];
