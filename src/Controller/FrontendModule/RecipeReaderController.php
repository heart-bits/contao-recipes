<?php

namespace Heartbits\ContaoRecipes\Controller\FrontendModule;

use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\ServiceAnnotation\FrontendModule;
use Contao\BackendTemplate;
use Contao\Config;
use Contao\Environment;
use Contao\FilesModel;
use Contao\Input;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\System;
use Contao\StringUtil;
use Contao\Template;
use Heartbits\ContaoRecipes\Models\CategoryModel;
use Heartbits\ContaoRecipes\Models\RecipeModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @FrontendModule(RecipeReaderController::TYPE, category="recipes")
 */
class RecipeReaderController extends AbstractFrontendModuleController
{
    public const TYPE = 'recipe_reader';

    protected function getResponse(Template $template, ModuleModel $model, Request $request): Response
    {
        $alias = Input::get('auto_item');

        $objRecipe = RecipeModel::findOneByAlias($alias);
        if ($objRecipe) {
            $t = 'tl_recipe';
            $ct = 'tl_content';
            $container = System::getContainer();
            $rootDir = $container->getParameter('kernel.project_dir');
            foreach ($objRecipe->row() as $key => $value) {
                if ($key === 'categories') {
                    if ($value) {
                        $categories = [];
                        $arrCategories = StringUtil::deserialize($value);
                        foreach ($arrCategories as $category) {
                            $objCategory = CategoryModel::findByIdOrAlias($category);
                            $categories[] = [
                                'title' => $objCategory->title,
                                'alias' => $objCategory->alias
                            ];
                        }
                        $template->$key = $categories;
                    }
                } elseif ($key === 'singleSRC') {
                    if ($value !== '') {
                        $objFile = FilesModel::findByUuid($value);
                        $path = $objFile->path;
                        if ($objFile !== null || is_file(System::getContainer()->getParameter('kernel.project_dir') . '/' . $path)) {
                            $picture = $container
                                ->get('contao.image.picture_factory')
                                ->create($rootDir . '/' . $path, StringUtil::deserialize($model->imgSize));
                            $data = [
                                'picture' => [
                                    'img' => $picture->getImg($rootDir),
                                    'sources' => $picture->getSources($rootDir),
                                ]
                            ];
                            $template->$key = $data;
                        }
                    }
                } else {
                    $template->$key = $value;
                }
            }
        } else {
            throw new PageNotFoundException('Page not found: ' . Environment::get('uri'));
        }

        return $template->getResponse();
    }
}
