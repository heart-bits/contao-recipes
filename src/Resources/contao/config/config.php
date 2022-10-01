<?php

use Contao\System;
use Heartbits\ContaoRecipes\Models\CategoryModel;
use Heartbits\ContaoRecipes\Models\IngredientModel;
use Heartbits\ContaoRecipes\Models\RecipeModel;
use Heartbits\ContaoRecipes\Models\UnitModel;
use Heartbits\ContaoRecipes\Widgets\InputIngredients;
use Heartbits\ContaoRecipes\Widgets\InputRating;
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
$GLOBALS['BE_FFL']['inputRating'] = InputRating::class;

// Models
$GLOBALS['TL_MODELS']['tl_recipe'] = RecipeModel::class;
$GLOBALS['TL_MODELS']['tl_recipe_ingredient'] = IngredientModel::class;
$GLOBALS['TL_MODELS']['tl_recipe_category'] = CategoryModel::class;
$GLOBALS['TL_MODELS']['tl_recipe_unit'] = UnitModel::class;
