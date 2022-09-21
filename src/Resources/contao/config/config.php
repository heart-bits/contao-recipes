<?php

use Contao\System;
use Symfony\Component\HttpFoundation\Request;

// Backend css
if (System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest(System::getContainer()->get('request_stack')->getCurrentRequest() ?? Request::create(''))) {
    $GLOBALS['TL_CSS'][] = 'bundles/heartbitscontaorecipes/css/backend.css|static';
}

// Backend modules
$GLOBALS['BE_MOD']['recipes']['recipe'] = [
    'tables' => ['tl_recipe', 'tl_recipe_ingredient', 'tl_recipe_category', 'tl_recipe_unit']
];

// Content elements
$GLOBALS['TL_CTE']['recipes'] = [
    'recipe_step' => 'Heartbits\ContaoRecipes\Controller\ContentElements\RecipeStep'
];

// Frontend modules
$GLOBALS['TL_FMD']['recipes'] = [
    'recipe_list' => 'Heartbits\ContaoRecipes\Controller\FrontendModules\RecipeList',
    'recipe_reader' => 'Heartbits\ContaoRecipes\Controller\FrontendModules\RecipeReader'
];

// Models
$GLOBALS['TL_MODELS']['tl_recipe'] = 'Heartbits\ContaoRecipes\Models\Recipe';
$GLOBALS['TL_MODELS']['tl_recipe_ingredient'] = 'Heartbits\ContaoRecipes\Models\Ingredient';
$GLOBALS['TL_MODELS']['tl_recipe_category'] = 'Heartbits\ContaoRecipes\Models\Category';
$GLOBALS['TL_MODELS']['tl_recipe_unit'] = 'Heartbits\ContaoRecipes\Models\Unit';
