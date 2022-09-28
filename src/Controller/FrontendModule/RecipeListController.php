<?php

namespace Heartbits\ContaoRecipes\Controller\FrontendModule;

use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\ServiceAnnotation\FrontendModule;
use Contao\ContentModel;
use Contao\FilesModel;
use Contao\ModuleModel;
use Contao\StringUtil;
use Contao\System;
use Contao\Template;
use Heartbits\ContaoRecipes\Models\CategoryModel;
use Heartbits\ContaoRecipes\Models\RecipeModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @FrontendModule(RecipeListController::TYPE, category="recipes")
 */
class RecipeListController extends AbstractFrontendModuleController
{
    public const TYPE = 'recipe_list';

    protected function getResponse(Template $template, ModuleModel $model, Request $request): Response
    {
        if (System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest(System::getContainer()->get('request_stack')->getCurrentRequest())) {
            $template = new BackendTemplate('be_wildcard');
            $template->title = $GLOBALS['TL_LANG']['FMD'][RecipeListController::TYPE][0];
        } else {
            $objRecipes = RecipeModel::findPublished(false, 0, []);
            $arrRecipes = [];
            if ($objRecipes) {
                $t = 'tl_recipe';
                $ct = 'tl_content';
                $container = System::getContainer();
                $rootDir = $container->getParameter('kernel.project_dir');
                $i = 0;
                while ($objRecipes->next()) {
                    foreach ($objRecipes->row() as $key => $value) {
                        switch ($key) {
                            case 'id':
                                $arrRecipes[$i][$key] = $value;
                                $objContent = ContentModel::findBy(["$ct.ptable='$t'", "$ct.pid='$value'", "$ct.invisible=''"], null);
                                (!$objContent) ? $arrRecipes[$i]['hasContent'] = false : $arrRecipes[$i]['hasContent'] = true;
                                break;
                            case 'categories':
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
                                    $arrRecipes[$i][$key] = $categories;
                                }
                                break;
                            case 'singleSRC':
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
                                        $arrRecipes[$i][$key] = $data;
                                    }
                                }
                                break;
                            default:
                                $arrRecipes[$i][$key] = $value;
                                break;
                        }
                    }
                    $i++;
                }
            }

            if (empty($arrRecipes)) {
                System::loadLanguageFile('tl_recipe');
                $template->message = $GLOBALS['TL_LANG']['tl_recipe']['noEntriesFound'];
            } else {
                $template->recipes = $arrRecipes;
            }
        }

        return $template->getResponse();
    }
}
