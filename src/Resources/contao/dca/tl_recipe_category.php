<?php

use Contao\Config;
use Contao\DC_Table;
use Contao\System;
use Heartbits\ContaoRecipes\EventListener\DataContainer\RecipeCallbackListener;

System::loadLanguageFile('default');

$GLOBALS['TL_DCA']['tl_recipe_category'] = [
    // Config
    'config' => [
        'dataContainer' => DC_Table::class,
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
            'toggle' => [
                'label' => &$GLOBALS['TL_LANG']['tl_recipe']['toggle'],
                'href' => 'act=toggle&amp;field=published',
                'icon' => 'visible.svg',
                'showInHeader' => true,
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
        'default' => '{category_legend},title,alias,singleSRC;{expert_legend:hide},published;',
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

        'published' => [
            'exclude' => true,
            'filter' => true,
            'toggle' => true,
            'inputType' => 'checkbox',
            'sql' => "char(1) NOT NULL default 1"
        ],
    ]
];
