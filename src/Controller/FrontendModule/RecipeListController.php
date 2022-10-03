<?php

namespace Heartbits\ContaoRecipes\Controller\FrontendModule;

use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\ServiceAnnotation\FrontendModule;
use Contao\Model\Collection;
use Contao\ContentModel;
use Contao\Config;
use Contao\Environment;
use Contao\FilesModel;
use Contao\Input;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\Pagination;
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
            $limit = null;
            $offset = (int)$model->skipFirst;

            // Maximum number of items
            if ($model->numberOfItems > 0) {
                $limit = $model->numberOfItems;
            }

            // Handle featured recipes
            if ($model->recipe_featured == 'featured_recipes') {
                $blnFeatured = true;
            } elseif ($model->recipe_featured == 'unfeatured_recipes') {
                $blnFeatured = false;
            } else {
                $blnFeatured = null;
            }

            // Get the total number of items
            $intTotal = $this->countItems($blnFeatured);
            $total = $intTotal - $offset;

            // Split the results
            if ($model->perPage > 0 && (!isset($limit) || $model->numberOfItems > $model->perPage)) {
                // Adjust the overall limit
                if (isset($limit)) {
                    $total = min($limit, $total);
                }

                // Get the current page
                $id = 'page_n' . $model->id;
                $page = Input::get($id) ?? 1;

                // Do not index or cache the page if the page number is outside the range
                if ($page < 1 || $page > max(ceil($total / $model->perPage), 1)) {
                    throw new PageNotFoundException('Page not found: ' . Environment::get('uri'));
                }

                // Set limit and offset
                $limit = $model->perPage;
                $offset += (max($page, 1) - 1) * $model->perPage;
                $skip = (int)$model->skipFirst;

                // Overall limit
                if ($offset + $limit > $total + $skip) {
                    $limit = $total + $skip - $offset;
                }

                // Add the pagination menu
                $objPagination = new Pagination($total, $model->perPage, Config::get('maxPaginationLinks'), $id);
                $template->pagination = $objPagination->generate("\n  ");
            }

            $objJumpTo = PageModel::findPublishedById($model->jumpTo);
            $objRecipes = $this->fetchItems($model, $blnFeatured, ($limit ?: 0), $offset);
            $arrRecipes = [];

            if ($objRecipes) {
                $t = RecipeModel::getTable();
                $ct = ContentModel::getTable();
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
                            case 'alias':
                                if ($objJumpTo instanceof PageModel) {
                                    $arrRecipes[$i]['jumpTo'] = $objJumpTo->getAbsoluteUrl('/' . $value);
                                }
                                $arrRecipes[$i][$key] = $value;
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
                                if ($value) {
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

    protected function countItems($blnFeatured)
    {
        return RecipeModel::countPublished($blnFeatured);
    }

    protected function fetchItems($model, $blnFeatured, $limit, $offset)
    {
        $t = RecipeModel::getTable();
        $order = '';

        if ($model->recipe_featured == 'featured_recipes_first') {
            $order .= "$t.featured DESC, ";
        }

        switch ($model->recipe_order) {
            case 'order_title_asc':
                $order .= "$t.title";
                break;

            case 'order_title_desc':
                $order .= "$t.title DESC";
                break;

            case 'order_random':
                $order .= "RAND()";
                break;

            case 'order_date_asc':
                $order .= "$t.recipe_date";
                break;

            default:
                $order .= "$t.recipe_date DESC";
        }

        return RecipeModel::findPublished($blnFeatured, $limit, $offset, ['order' => $order]);
    }
}
