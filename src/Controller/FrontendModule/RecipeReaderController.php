<?php

namespace Heartbits\ContaoRecipes\Controller\FrontendModule;

use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\ServiceAnnotation\FrontendModule;
use Contao\BackendTemplate;
use Contao\Config;
use Contao\ContentModel;
use Contao\Controller;
use Contao\Environment;
use Contao\FilesModel;
use Contao\Input;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\System;
use Contao\StringUtil;
use Contao\Template;
use Heartbits\ContaoRecipes\Models\CategoryModel;
use Heartbits\ContaoRecipes\Models\IngredientModel;
use Heartbits\ContaoRecipes\Models\RecipeModel;
use Heartbits\ContaoRecipes\Models\UnitModel;
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
        if (System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest(System::getContainer()->get('request_stack')->getCurrentRequest())) {
            $template = new BackendTemplate('be_wildcard');
            $template->title = $GLOBALS['TL_LANG']['FMD'][RecipeReaderController::TYPE][0];
        } else {
            $alias = Input::get('auto_item');
            $t = 'tl_recipe';
            $objRecipe = RecipeModel::findOneByAlias($alias);
            $objContent = ContentModel::findPublishedByPidAndTable($objRecipe->id, $t);

            if ($objRecipe && $objContent) {
                $this->overwriteMetaData($objRecipe);

                foreach ($objRecipe->row() as $key => $value) {
                    switch ($key) {
                        case 'ingredients':
                            if (is_array($ingredients = StringUtil::deserialize($value))) {
                                $arrIngredients = [];
                                $i = 0;
                                foreach ($ingredients as $ingredient) {
                                    $objUnit = UnitModel::findOneByAlias($ingredient[1]);
                                    $objIngredient = IngredientModel::findOneByAlias($ingredient[2]);

                                    $arrIngredients[$i] = [
                                        'amount' => $ingredient[0],
                                        'unit' => [
                                            'alias' => $objUnit->alias,
                                            'title' => $objUnit->title
                                        ],
                                        'ingredient' => [
                                            'alias' => $objIngredient->alias,
                                            'title' => $objIngredient->title
                                        ]
                                    ];

                                    if ($objIngredient->singleSRC !== '') {
                                        $objFile = FilesModel::findByUuid($objIngredient->singleSRC);
                                        if ($objFile) $this->getImageData($objFile, $model->imgSize);
                                    }

                                    $i++;
                                }
                                $template->$key = $arrIngredients;
                            }
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
                                $template->$key = $categories;
                            }
                            break;
                        case 'singleSRC':
                            if ($value !== '') {
                                $objFile = FilesModel::findByUuid($value);
                                if ($objFile) $template->$key = $this->getImageData($objFile, $model->imgSize);
                            }
                            break;
                        default:
                            $template->$key = $value;
                            break;
                    }
                }

                $template->content = $this->setRecipeContent($request, $objContent);
            } else {
                throw new PageNotFoundException('Page not found: ' . Environment::get('uri'));
            }
        }

        return $template->getResponse();
    }

    public function setRecipeContent($request, $elements): string
    {
        // The layout section is stored in a request attribute
        $section = $request->attributes->get('section', 'main');

        // Get the rendered content elements
        $content = '';

        foreach ($elements as $element) {
            $content .= Controller::getContentElement($element->id, $section);
        }

        return $content;
    }

    public function getImageData(FilesModel $objFile, $size): array
    {
        $data = [];
        $container = System::getContainer();
        $rootDir = $container->getParameter('kernel.project_dir');
        $path = $objFile->path;
        if ($objFile !== null || is_file(System::getContainer()->getParameter('kernel.project_dir') . '/' . $path)) {
            $picture = $container
                ->get('contao.image.picture_factory')
                ->create($rootDir . '/' . $path, StringUtil::deserialize($size));
            $data = [
                'picture' => [
                    'img' => $picture->getImg($rootDir),
                    'sources' => $picture->getSources($rootDir),
                ]
            ];
        }
        return $data;
    }

    public function overwriteMetaData(RecipeModel $objRecipe): void
    {
        $responseContext = System::getContainer()->get('contao.routing.response_context_accessor')->getResponseContext();

        if ($responseContext && $responseContext->has(HtmlHeadBag::class))
        {
            /** @var HtmlHeadBag $htmlHeadBag */
            $htmlHeadBag = $responseContext->get(HtmlHeadBag::class);
            $htmlDecoder = System::getContainer()->get('contao.string.html_decoder');

            if ($objRecipe->title)
            {
                $htmlHeadBag->setTitle($objArticle->pageTitle); // Already stored decoded
            }

            if ($objRecipe->teaser)
            {
                $htmlHeadBag->setMetaDescription($htmlDecoder->htmlToPlainText($objArticle->teaser));
            }

            if ($objArticle->robots)
            {
                $htmlHeadBag->setMetaRobots($objArticle->robots);
            }
        }
    }
}
