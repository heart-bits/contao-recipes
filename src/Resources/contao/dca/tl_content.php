<?php

use Heartbits\ContaoRecipes\Controller\ContentElement\RecipeImageController;
use Heartbits\ContaoRecipes\Controller\ContentElement\RecipeStepController;
use Heartbits\ContaoRecipes\EventListener\DataContainer\ContentCallbackListener;

$GLOBALS['TL_DCA']['tl_content']['fields']['type']['options_callback'] = [[ContentCallbackListener::class, 'onLoadTypeCallback']];
$GLOBALS['TL_DCA']['tl_content']['fields']['type']['sql']['default'] = '';
$GLOBALS['TL_DCA']['tl_content']['fields']['type']['eval']['mandatory'] = true;
$GLOBALS['TL_DCA']['tl_content']['fields']['type']['eval']['includeBlankOption'] = true;
$GLOBALS['TL_DCA']['tl_content']['palettes'][RecipeImageController::TYPE] = '{type_legend},type;{image_legend},singleSRC,size;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID;{invisible_legend:hide},invisible,start,stop';
$GLOBALS['TL_DCA']['tl_content']['palettes'][RecipeStepController::TYPE] = '{type_legend},type,headline;{text_legend},text;{image_legend},addImage;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID;{invisible_legend:hide},invisible,start,stop';
