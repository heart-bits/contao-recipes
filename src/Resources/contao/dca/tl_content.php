<?php

use Heartbits\ContaoRecipes\Controller\ContentElement\RecipeImageController;
use Heartbits\ContaoRecipes\Controller\ContentElement\RecipeListController;
use Heartbits\ContaoRecipes\Controller\ContentElement\RecipeReaderController;
use Heartbits\ContaoRecipes\Controller\ContentElement\RecipeStepController;
use Heartbits\ContaoRecipes\EventListener\DataContainer\ContentCallbackListener;

$GLOBALS['TL_DCA']['tl_content']['fields']['type']['options_callback'] = [[ContentCallbackListener::class, 'onLoadTypeCallback']];
$GLOBALS['TL_DCA']['tl_content']['fields']['type']['sql']['default'] = '';
$GLOBALS['TL_DCA']['tl_content']['fields']['type']['eval']['mandatory'] = true;
$GLOBALS['TL_DCA']['tl_content']['fields']['type']['eval']['includeBlankOption'] = true;
$GLOBALS['TL_DCA']['tl_content']['palettes'][RecipeImageController::TYPE] = '{type_legend},type;{image_legend},singleSRC,size;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID;{invisible_legend:hide},invisible,start,stop';
$GLOBALS['TL_DCA']['tl_content']['palettes'][RecipeStepController::TYPE] = '{type_legend},type,headline;{text_legend},text;{image_legend},addImage;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID;{invisible_legend:hide},invisible,start,stop';
$GLOBALS['TL_DCA']['tl_content']['palettes'][RecipeListController::TYPE] = '{title_legend},name,type;{source_legend},imgSize;{redirect_legend},jumpTo;{config_legend},numberOfItems,skipFirst,recipe_featured,recipe_order,perPage;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';
$GLOBALS['TL_DCA']['tl_content']['palettes'][RecipeReaderController::TYPE] = '{title_legend},name,type;{source_legend},imgSize;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';

$GLOBALS['TL_DCA']['tl_content']['fields']['recipe_featured'] = [
    'exclude' => true,
    'inputType' => 'select',
    'options' => [
        'all_recipes',
        'featured_recipes',
        'unfeatured_recipes',
        'featured_recipes_first'
    ],
    'reference' => &$GLOBALS['TL_LANG']['tl_content'],
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
    'reference' => &$GLOBALS['TL_LANG']['tl_content'],
    'eval' => [
        'tl_class' => 'w50'
    ],
    'sql' => "varchar(32) COLLATE ascii_bin NOT NULL default 'order_date_desc'"
);
