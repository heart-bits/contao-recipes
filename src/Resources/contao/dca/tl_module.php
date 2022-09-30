<?php

use Heartbits\ContaoRecipes\Controller\FrontendModule\RecipeListController;
use Heartbits\ContaoRecipes\Controller\FrontendModule\RecipeReaderController;

$GLOBALS['TL_DCA']['tl_module']['palettes'][RecipeListController::TYPE] = '{title_legend},name,type;{source_legend},imgSize;{redirect_legend},jumpTo;{template_legend:hide},customTpl,navigationTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes'][RecipeReaderController::TYPE] = '{title_legend},name,type;{source_legend},imgSize;{template_legend:hide},customTpl,navigationTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';