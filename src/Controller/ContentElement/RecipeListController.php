<?php

namespace Heartbits\ContaoRecipes\Controller\ContentElement;

use Contao\BackendTemplate;
use Contao\Config;
use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\Environment;
use Contao\FilesModel;
use Contao\Input;
use Contao\Model\Collection;
use Contao\PageModel;
use Contao\Pagination;
use Contao\StringUtil;
use Contao\System;
use Contao\Template;
use Heartbits\ContaoRecipes\Models\CategoryModel;
use Heartbits\ContaoRecipes\Models\IngredientModel;
use Heartbits\ContaoRecipes\Models\RecipeModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class RecipeListController extends AbstractContentElementController
{
    public const string TYPE = 'recipe_list';

    public function __construct(
        private readonly ScopeMatcher $scopeMatcher,
        private readonly TranslatorInterface $translator,
        private readonly string $projectDir,
        private ContentUrlGenerator $urlGenerator
    )
    {
    }

    protected function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        if ($this->scopeMatcher->isBackendRequest($request)) {
            $template = new BackendTemplate('be_wildcard');
            $template->title = $this->translator->trans('CTE.'.RecipeListController::TYPE.'.0', [], 'contao_default');

            return $template->getResponse();
        }

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
        $filterCategories = [];
        $filterIngredients = [];

        if ($objRecipes) {
            $t = RecipeModel::getTable();
            $ct = ContentModel::getTable();
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
                            $arrRecipes[$i]['jumpTo'] = ($objJumpTo instanceof PageModel) ? $this->urlGenerator->generate($objJumpTo, ['parameters' => '/' . $value]) : '';
                            $arrRecipes[$i][$key] = $value;
                            break;
                        case 'categories':
                            $categories = [];
                            if ($value) {
                                $arrCategories = StringUtil::deserialize($value);
                                foreach ($arrCategories as $category) {
                                    $objCategory = CategoryModel::findByIdOrAlias($category);
                                    $categories[] = [
                                        'title' => $objCategory->title,
                                        'alias' => $objCategory->alias,
                                        'singleSRC' => (null !== ($objFile = FilesModel::findByUuid($value)) && is_file($this->projectDir . '/' . $objFile->path)) ? $objCategory->singleSRC : '',
                                    ];
                                    if (!isset($filterCategories[$objCategory->alias])) {
                                        $filterCategories[$objCategory->alias] = $objCategory->title;
                                    }
                                }
                                $arrRecipes[$i][$key] = $categories;
                            }
                            break;
                        case 'ingredients':
                            $ingredients = [];
                            if ($value) {
                                $arrIngredients = StringUtil::deserialize($value);
                                foreach ($arrIngredients as $ingredient) {
                                    $objIngredient = IngredientModel::findByIdOrAlias($ingredient['ingredient']);
                                    $ingredients[] = [
                                        'title' => $objIngredient->title,
                                        'alias' => $objIngredient->alias,
                                        'singleSRC' => (null !== ($objFile = FilesModel::findByUuid($value)) && is_file($this->projectDir . '/' . $objFile->path)) ? $objIngredient->singleSRC : '',
                                    ];
                                }
                                if (!isset($filterIngredients[$objIngredient->alias])) {
                                    $filterIngredients[$objIngredient->alias] = $objIngredient->title;
                                }
                                $arrRecipes[$i][$key] = $ingredients;
                            }
                            break;
                        case 'singleSRC':
                            if (null !== ($objFile = FilesModel::findByUuid($value)) && is_file($this->projectDir . '/' . $objFile->path)) {
                                $arrRecipes[$i][$key] = $value;
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
            $template->message = $this->translator->trans('tl_recipe.noEntriesFound', [], 'contao_tl_recipe');
        }

        $template->recipes = $arrRecipes;
        $template->ingredients = $filterIngredients;
        $template->categories = $filterCategories;
        $template->addRecipeFilter = $model->addRecipeFilter;
        $template->size = $model->size;
        $template->text = $model->text;

        return $template->getResponse();
    }

    protected function countItems($blnFeatured)
    {
        return RecipeModel::countAllPublished($blnFeatured);
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
                $order .= "$t.date";
                break;

            default:
                $order .= "$t.date DESC";
        }

        return RecipeModel::findAllPublished($blnFeatured, $limit, $offset, ['order' => $order]);
    }
}
