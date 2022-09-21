<?php

use Contao\Config;
use Heartbits\ContaoRecipes\EventListener\DataContainer\RecipeCallbackListener;

$GLOBALS['TL_DCA']['tl_recipe'] = [
    // Config
    'config' => [
        'dataContainer' => 'Table',
        'ctable' => [
            'tl_content'
        ],
        'switchToEdit' => true,
        'enableVersioning' => true,
        'sql' => [
            'keys' => [
                'id' => 'primary'
            ]
        ],
    ],
    // List
    'list' => [
        'sorting' => [
            'mode' => 1,
            'fields' => ['title'],
            'flag' => 1,
            'panelLayout' => 'search,limit;filter'
        ],
        'label' => [
            'fields' => ['title'],
            'format' => '%s',
        ],
        'global_operations' =>
            [
                'units' => [
                    'label' => &$GLOBALS['TL_LANG']['tl_recipe']['unit_legend'],
                    'href' => 'table=tl_recipe_unit',
                    'icon' => 'bundles/heartbitscontaorecipes/img/units.svg',
                    'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="c"',
                ],
                'ingredients' => [
                    'label' => &$GLOBALS['TL_LANG']['tl_recipe']['ingredient_legend'],
                    'href' => 'table=tl_recipe_ingredient',
                    'icon' => 'bundles/heartbitscontaorecipes/img/ingredients.svg',
                    'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="c"',
                ],
                'categories' => [
                    'label' => &$GLOBALS['TL_LANG']['tl_recipe']['category_legend'],
                    'href' => 'table=tl_recipe_category',
                    'icon' => 'bundles/heartbitscontaorecipes/img/categories.svg',
                    'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="c"',
                ],
                'all' => [
                    'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
                    'href' => 'act=select',
                    'class' => 'header_edit_all',
                    'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"'
                ]
            ],
        'operations' =>
            [
                'edit' => [
                    'label' => &$GLOBALS['TL_LANG']['tl_recipe']['edit'],
                    'href' => 'table=tl_contacts',
                    'icon' => 'bundles/heartbitscontaorecipes/user.svg',
                ],
                'editheader' => [
                    'label' => &$GLOBALS['TL_LANG']['tl_recipe']['editheader'],
                    'href' => 'act=edit',
                    'icon' => 'header.svg'
                ],
                'copy' => [
                    'label' => &$GLOBALS['TL_LANG']['tl_recipe']['copy'],
                    'href' => 'act=copy',
                    'icon' => 'copy.svg'
                ],
                'delete' => [
                    'label' => &$GLOBALS['TL_LANG']['tl_recipe']['delete'],
                    'href' => 'act=delete',
                    'icon' => 'delete.svg',
                    'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
                ],
                'show' => [
                    'label' => &$GLOBALS['TL_LANG']['tl_recipe']['show'],
                    'href' => 'act=show',
                    'icon' => 'show.svg',
                ],
                'feature' => [
                    'label' => &$GLOBALS['TL_LANG']['tl_recipe']['feature'],
                    'icon' => 'featured.svg',
                    'attributes' => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                    'haste_ajax_operation' => [
                        'field' => 'published',
                        'options' => [
                            [
                                'value' => '',
                                'icon' => 'featured_.svg'
                            ],
                            [
                                'value' => '1',
                                'icon' => 'featured.svg'
                            ]
                        ]
                    ]
                ],
                'toggle' => [
                    'label' => &$GLOBALS['TL_LANG']['tl_recipe']['toggle'],
                    'icon' => 'visible.svg',
                    'attributes' => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                    'haste_ajax_operation' => [
                        'field' => 'published',
                        'options' => [
                            [
                                'value' => '',
                                'icon' => 'invisible.svg'
                            ],
                            [
                                'value' => '1',
                                'icon' => 'visible.svg'
                            ]
                        ]
                    ]
                ],

            ]
    ],

    // Palettes
    'palettes' => [
        '__selector__' => ['title'],
        'default' => '{recipe_legend},title,alias;{image_legend},singleSRC;{expert_legend:hide},invisible;',
    ],

    // Fields
    'fields' =>
        [
            'id' => [
                'sql' => "int(10) unsigned NOT NULL auto_increment"
            ],

            'tstamp' => [
                'sql' => "int(10) unsigned NOT NULL default '0'"
            ],

            'title' => [
                'inputType' => 'text',
                'exclude' => true,
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

            'featured' => [
                'exclude' => true,
                'filter' => true,
                'inputType' => 'checkbox',
                'sql' => "char(1) NOT NULL default ''"
            ],

            'published' => [
                'exclude' => true,
                'filter' => true,
                'inputType' => 'checkbox',
                'sql' => "char(1) NOT NULL default ''"
            ],
        ]
];