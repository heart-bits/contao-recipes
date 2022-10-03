<?php

use Heartbits\ContaoRecipes\Controller\FrontendModule\RecipeListController;
use Heartbits\ContaoRecipes\Controller\FrontendModule\RecipeReaderController;

$GLOBALS['TL_DCA']['tl_module']['palettes'][RecipeListController::TYPE] = '{title_legend},name,type;{source_legend},imgSize;{redirect_legend},jumpTo;{config_legend},numberOfItems,skipFirst,recipe_featured,recipe_order,perPage;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes'][RecipeReaderController::TYPE] = '{title_legend},name,type;{source_legend},imgSize;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';

$GLOBALS['TL_DCA']['tl_module']['fields']['recipe_featured'] = [
    'exclude' => true,
    'inputType' => 'select',
    'options' => [
        'all_recipes',
        'featured_recipes',
        'unfeatured_recipes',
        'featured_recipes_first'
    ],
    'reference' => &$GLOBALS['TL_LANG']['tl_module'],
    'eval' => [
        'tl_class' => 'w50 clr'
    ],
    'sql' => "varchar(32) COLLATE ascii_bin NOT NULL default 'all_recipes'"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['recipe_order'] = array
(
    'exclude' => true,
    'inputType' => 'select',
    'options' => [
        'order_date_asc',
        'order_date_desc',
        'order_title_asc',
        'order_title_desc',
        'order_random'
    ],
    'reference' => &$GLOBALS['TL_LANG']['tl_module'],
    'eval' => [
        'tl_class' => 'w50'
    ],
    'sql' => "varchar(32) COLLATE ascii_bin NOT NULL default 'order_date_desc'"
);
