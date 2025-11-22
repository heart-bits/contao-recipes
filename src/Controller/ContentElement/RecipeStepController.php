<?php

namespace Heartbits\ContaoRecipes\Controller\ContentElement;

use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\BackendTemplate;
use Contao\ContentModel;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\FilesModel;
use Contao\StringUtil;
use Contao\System;
use Contao\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class RecipeStepController extends AbstractContentElementController
{
    public const string TYPE = 'recipe_step';

    public function __construct(
        private readonly ScopeMatcher $scopeMatcher,
        private readonly TranslatorInterface $translator
    )
    {
    }

    protected function getResponse(Template $template, ContentModel $model, Request $request): Response
    {
        if ($this->scopeMatcher->isBackendRequest($request)) {
            $template = new BackendTemplate('be_wildcard');
            if ($model->headline) $template->title = StringUtil::deserialize($model->headline)['value'];
            if ($model->text) $template->wildcard = $model->text;

            return $template->getResponse();
        }

        $template->singleSRC = $model->singleSRC;
        $template->text = $model->text;

        return $template->getResponse();
    }
}
