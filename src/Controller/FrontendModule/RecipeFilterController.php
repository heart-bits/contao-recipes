<?php

namespace Heartbits\ContaoRecipes\Controller\FrontendModule;

use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\ServiceAnnotation\FrontendModule;
use Contao\BackendTemplate;
use Contao\Input;
use Contao\ModuleModel;
use Contao\System;
use Contao\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @FrontendModule(RecipeFilterController::TYPE, category="recipes")
 */
class RecipeFilterController extends AbstractFrontendModuleController
{
    public const TYPE = 'recipe_filter';

    protected function getResponse(Template $template, ModuleModel $model, Request $request): Response
    {
        if (System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest(System::getContainer()->get('request_stack')->getCurrentRequest())) {
            $template = new BackendTemplate('be_wildcard');
            $template->title = $GLOBALS['TL_LANG']['FMD'][RecipeReaderController::TYPE][0];
        } else {

        }

        return $template->getResponse();
    }
}
