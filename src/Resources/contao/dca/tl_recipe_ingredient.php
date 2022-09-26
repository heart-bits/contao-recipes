<?php

use Contao\Config;
use Contao\System;
use Heartbits\ContaoRecipes\EventListener\DataContainer\RecipeCallbackListener;

System::loadLanguageFile('default');

$GLOBALS['TL_DCA']['tl_recipe_ingredient'] = [
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
                'label' => &$GLOBALS['TL_LANG']['tl_recipe_ingredient']['edit'],
                'href' => 'act=edit',
                'icon' => 'edit.svg'
            ],
            'copy' => [
                'label' => &$GLOBALS['TL_LANG']['tl_recipe_ingredient']['copy'],
                'href' => 'act=paste&amp;mode=copy',
                'icon' => 'copy.svg',
                'attributes' => 'onclick="Backend.getScrollOffset()"'
            ],
            'cut' => [
                'href' => 'act=paste&amp;mode=cut',
                'icon' => 'cut.svg'
            ],
            'delete' => [
                'label' => &$GLOBALS['TL_LANG']['tl_recipe_ingredient']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.svg',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
            ],
            'show' => [
                'label' => &$GLOBALS['TL_LANG']['tl_recipe_ingredient']['show'],
                'href' => 'act=show',
                'icon' => 'show.svg',
                'attributes' => 'style="margin-right:3px"'
            ],
        ]
    ],

    // Palettes
    'palettes' => [
        '__selector__' => ['title'],
        'default' => '{ingredient_legend},title,alias;{image_legend},singleSRC;',
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
                'tl_class' => 'w50'
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
            'sql' => "varchar(255) NOT NULL default ''"
        ],

        'singleSRC' => [
            'exclude' => true,
            'inputType' => 'fileTree',
            'eval' => [
                'filesOnly' => true,
                'fieldType' => 'radio',
                'tl_class' => 'clr',
                'extensions' => Config::get('validImageTypes')
            ],
            'sql' => "binary(16) NULL"
        ],
    ]
];
