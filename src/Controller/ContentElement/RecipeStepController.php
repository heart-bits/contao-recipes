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
use Heartbits\ContaoRecipes\Models\IngredientModel;
use Heartbits\ContaoRecipes\Models\RecipeModel;
use Heartbits\ContaoRecipes\Models\UnitModel;
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
            if ($model->text) $template->wildcard = strip_tags($model->text);

            return $template->getResponse();
        }

        $template->singleSRC = $model->singleSRC;
        $template->size = $model->size;
        $template->floating = $model->floating;
        $template->text = $model->text;
        $template->ingredients = $this->prepareIngredients($model);

        return $template->getResponse();
    }

    private function prepareIngredients(ContentModel $model): array
    {
        $rows = StringUtil::deserialize($model->ingredients, true);

        if (empty($rows)) {
            return [];
        }

        $recipe = RecipeModel::findByPk($model->pid);

        if ($recipe === null) {
            return [];
        }

        $units = [];
        foreach (StringUtil::deserialize($recipe->ingredients, true) as $row) {
            if (!empty($row['ingredient'])) {
                $units[$row['ingredient']] = $row['unit'] ?? '';
            }
        }

        $ingredients = [];
        foreach ($rows as $row) {
            if (empty($row['ingredient']) || $row['amount'] === '' || $row['amount'] === null) {
                continue;
            }

            $ingredient = IngredientModel::findOneByAlias($row['ingredient']);
            $unit = UnitModel::findOneByAlias($units[$row['ingredient']] ?? '');

            $ingredients[] = [
                'amount' => $row['amount'],
                'unit' => [
                    'shortcode' => $unit->shortcode ?? '',
                    'title' => $unit->title ?? '',
                ],
                'ingredient' => [
                    'alias' => $row['ingredient'],
                    'title' => $ingredient->title ?? '',
                ],
            ];
        }

        return $ingredients;
    }
}
