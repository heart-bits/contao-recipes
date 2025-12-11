<?php

use Contao\Config;
use Contao\BackendUser;
use Contao\ContentModel;
use Contao\DataContainer;
use Contao\DC_Table;
use Heartbits\ContaoRecipes\Models\CategoryModel;
use Heartbits\ContaoRecipes\Models\IngredientModel;
use Heartbits\ContaoRecipes\Models\RecipeModel;
use Heartbits\ContaoRecipes\Models\UnitModel;

$GLOBALS['TL_DCA'][RecipeModel::getTable()] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'ctable' => [
            ContentModel::getTable()
        ],
        'switchToEdit' => true,
        'enableVersioning' => true,
        'sql' => [
            'keys' => [
                'id' => 'primary'
            ]
        ],
    ],
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
        'global_operations' => [
            'units' => [
                'label' => &$GLOBALS['TL_LANG'][RecipeModel::getTable()]['unit_legend'],
                'href' => 'table='.UnitModel::getTable(),
                'icon' => 'bundles/heartbitscontaorecipes/img/units.svg',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="c"',
                'primary' => true
            ],
            'ingredients' => [
                'label' => &$GLOBALS['TL_LANG'][RecipeModel::getTable()]['ingredient_legend'],
                'href' => 'table='.IngredientModel::getTable(),
                'icon' => 'bundles/heartbitscontaorecipes/img/ingredients.svg',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="c"',
                'primary' => true
            ],
            'categories' => [
                'label' => &$GLOBALS['TL_LANG'][RecipeModel::getTable()]['category_legend'],
                'href' => 'table='.CategoryModel::getTable(),
                'icon' => 'bundles/heartbitscontaorecipes/img/categories.svg',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="c"',
                'primary' => true
            ],
            'all'
        ],
        'operations' => [
            'edit',
            'children',
            'copy',
            'delete',
            'show',
            'feature' => [
                'href' => 'act=toggle&amp;field=featured',
                'icon' => 'featured.svg',
                'showInHeader' => true,
                'primary' => true,
            ],
            'toggle' => [
                'href' => 'act=toggle&amp;field=published',
                'icon' => 'visible.svg',
                'showInHeader' => true,
                'primary' => true,
            ],
        ]
    ],
    'palettes' => [
        'default' => '{recipe_legend},title,alias,subheadline,recipe_author,recipe_date,time,rating,teaser;{ingredients_legend},ingredients,portions;{image_legend},singleSRC;{nutritional_legend},calories,protein,fat,carbohydrates;{categories_legend},categories;{expert_legend:hide},published,featured;',
    ],
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
            'search' => true,
            'eval' => [
                'unique' => true,
                'mandatory' => true,
                'maxlength' => 255,
                'tl_class' => 'w50 clr'
            ],
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'subheadline' => [
            'inputType' => 'text',
            'exclude' => true,
            'eval' => [
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
        'author' => [
            'default' => BackendUser::getInstance()->id,
            'exclude' => true,
            'search' => true,
            'filter' => true,
            'sorting' => true,
            'flag' => DataContainer::SORT_ASC,
            'inputType' => 'select',
            'foreignKey' => 'tl_user.name',
            'eval' => [
                'doNotCopy' => true,
                'chosen' => true,
                'mandatory' => true,
                'includeBlankOption' => true,
                'tl_class' => 'w50'
            ],
            'sql' => "int(10) unsigned NOT NULL default 0",
            'relation' => [
                'type' => 'hasOne',
                'load' => 'lazy'
            ]
        ],
        'date' => [
            'default' => time(),
            'exclude' => true,
            'filter' => true,
            'sorting' => true,
            'flag' => DataContainer::SORT_MONTH_DESC,
            'inputType' => 'text',
            'eval' => [
                'rgxp' => 'date',
                'mandatory' => true,
                'doNotCopy' => true,
                'datepicker' => true,
                'tl_class' => 'w50 wizard'
            ],
            'sql' => "int(10) unsigned NOT NULL default 0"
        ],
        'categories' => [
            'inputType' => 'checkbox',
            'exclude' => true,
            'eval' => [
                'tl_class' => 'clr',
                'doNotCopy' => true,
                'multiple' => true
            ],
            'sql' => "blob"
        ],
        'teaser' => [
            'exclude' => true,
            'inputType' => 'textarea',
            'eval' => [
                'rte' => 'tinyMCE',
                'helpwizard' => true,
                'tl_class' => 'clr'
            ],
            'explanation' => 'insertTags',
            'sql' => "mediumtext NULL"
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
        'ingredients' => [
            'inputType' => 'inputIngredient',
            'eval' => [
                'tl_class' => 'clr',
            ],
            'sql' => "blob NULL"
        ],
        'portions' => [
            'inputType' => 'text',
            'exclude' => true,
            'eval' => [
                'mandatory' => true,
                'maxlength' => 3,
                'tl_class' => 'w50'
            ],
            'sql' => "int(3) NOT NULL default 1"
        ],
        'time' => [
            'inputType' => 'text',
            'exclude' => true,
            'eval' => [
                'mandatory' => true,
                'maxlength' => 3,
                'tl_class' => 'w50',
                'rgxp' => 'natural'
            ],
            'sql' => "int(3) NOT NULL default 0"
        ],
        'rating' => [
            'inputType' => 'inputRating',
            'exclude' => true,
            'eval' => [
                'tl_class' => 'w50 clr',
                'rgxp' => 'natural',
                'doNotCopy' => true
            ],
            'sql' => "int(1) NOT NULL default 0"
        ],
        'calories' => [
            'inputType' => 'text',
            'exclude' => true,
            'eval' => [
                'maxlength' => 10,
                'tl_class' => 'w50',
                'doNotCopy' => true
            ],
            'sql' => "int(10) NOT NULL default 0"
        ],
        'protein' => [
            'inputType' => 'text',
            'exclude' => true,
            'eval' => [
                'maxlength' => 10,
                'tl_class' => 'w50',
                'doNotCopy' => true
            ],
            'sql' => "int(10) NOT NULL default 0"
        ],
        'fat' => [
            'inputType' => 'text',
            'exclude' => true,
            'eval' => [
                'maxlength' => 10,
                'tl_class' => 'w50',
                'doNotCopy' => true
            ],
            'sql' => "int(10) NOT NULL default 0"
        ],
        'carbohydrates' => [
            'inputType' => 'text',
            'exclude' => true,
            'eval' => [
                'maxlength' => 10,
                'tl_class' => 'w50',
                'doNotCopy' => true
            ],
            'sql' => "int(10) NOT NULL default 0"
        ],
        'featured' => [
            'exclude' => true,
            'filter' => true,
            'toggle' => true,
            'inputType' => 'checkbox',
            'eval' => [
                'tl_class' => 'w50',
                'doNotCopy' => true
            ],
            'sql' => "char(1) NOT NULL default ''"
        ],
        'published' => [
            'exclude' => true,
            'filter' => true,
            'toggle' => true,
            'inputType' => 'checkbox',
            'eval' => [
                'tl_class' => 'w50',
                'doNotCopy' => true
            ],
            'sql' => "char(1) NOT NULL default ''"
        ],
    ]
];
