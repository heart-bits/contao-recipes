<?php

namespace Heartbits\ContaoRecipes\Controller\ContentElement;

use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\BackendTemplate;
use Contao\ContentModel;
use Contao\FilesModel;
use Contao\StringUtil;
use Contao\System;
use Contao\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RecipeStepController extends AbstractContentElementController
{
    public const TYPE = 'recipe_step';

    protected function getResponse(Template $template, ContentModel $model, Request $request): Response
    {
        if (System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest(System::getContainer()->get('request_stack')->getCurrentRequest())) {
            $template = new BackendTemplate('be_wildcard');
            if ($model->headline) $template->title = StringUtil::deserialize($model->headline)['value'];
            if ($model->text) $template->wildcard = $model->text;
        } else {
            if ($model->singleSRC !== '') {
                $container = System::getContainer();
                $rootDir = $container->getParameter('kernel.project_dir');
                $objFile = FilesModel::findByUuid($model->singleSRC);
                $path = $objFile->path;
                if ($objFile !== null || is_file($container->getParameter('kernel.project_dir') . '/' . $path)) {
                    $picture = $container
                        ->get('contao.image.picture_factory')
                        ->create($rootDir . '/' . $path, StringUtil::deserialize($model->size));
                    $data = [
                        'picture' => [
                            'img' => $picture->getImg($rootDir),
                            'sources' => $picture->getSources($rootDir),
                        ]
                    ];
                    $template->singleSRC = $data;
                }
            }
        }

        return $template->getResponse();
    }
}
