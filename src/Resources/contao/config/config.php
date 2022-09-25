<?php

use Contao\System;
use Heartbits\ContaoRecipes\Widgets\InputIngredients;
use Heartbits\ContaoRecipes\Controller\ContentElement\RecipeStepController;
use Heartbits\ContaoRecipes\Controller\FrontendModule\RecipeListController;
use Heartbits\ContaoRecipes\Controller\FrontendModule\RecipeReaderController;
use Symfony\Component\HttpFoundation\Request;

// Backend css
if (System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest(System::getContainer()->get('request_stack')->getCurrentRequest() ?? Request::create(''))) {
    $GLOBALS['TL_CSS'][] = 'bundles/heartbitscontaorecipes/css/backend.css|static';
}

// Backend modules
$GLOBALS['BE_MOD']['recipes']['recipe'] = [
    'tables' => ['tl_recipe', 'tl_recipe_ingredient', 'tl_recipe_category', 'tl_recipe_unit', 'tl_content']
];

// Backend form fields
$GLOBALS['BE_FFL']['inputIngredient'] = InputIngredients::class;

// Models
$GLOBALS['TL_MODELS']['tl_recipe'] = 'Heartbits\ContaoRecipes\Models\RecipeModel';
$GLOBALS['TL_MODELS']['tl_recipe_ingredient'] = 'Heartbits\ContaoRecipes\Models\IngredientModel';
$GLOBALS['TL_MODELS']['tl_recipe_category'] = 'Heartbits\ContaoRecipes\Models\CategoryModel';
$GLOBALS['TL_MODELS']['tl_recipe_unit'] = 'Heartbits\ContaoRecipes\Models\UnitModel';
