<?php

namespace Heartbits\ContaoRecipes\EventListener\DataContainer;

use Contao\BackendUser;
use Contao\ContentModel;
use Contao\DataContainer;
use Contao\StringUtil;
use Heartbits\ContaoRecipes\Controller\ContentElement\RecipeImageController;
use Heartbits\ContaoRecipes\Controller\ContentElement\RecipeListController;
use Heartbits\ContaoRecipes\Controller\ContentElement\RecipeReaderController;
use Heartbits\ContaoRecipes\Controller\ContentElement\RecipeStepController;
use Heartbits\ContaoRecipes\Models\IngredientModel;
use Heartbits\ContaoRecipes\Models\RecipeModel;
use Heartbits\ContaoRecipes\Models\UnitModel;
use Symfony\Component\Config\Definition\Exception\Exception;

class ContentCallbackListener
{
    public function typeOptionsCallback(DataContainer $dc): array
    {
        $options = [];

        if ($dc->getCurrentRecord()['ptable'] === 'tl_recipe') {
            $options = [
                RecipeStepController::TYPE => $GLOBALS['TL_LANG']['CTE'][RecipeStepController::TYPE][0],
                RecipeImageController::TYPE => $GLOBALS['TL_LANG']['CTE'][RecipeImageController::TYPE][0],
                'gallery' => $GLOBALS['TL_LANG']['CTE']['gallery'][0]
            ];
        } else {
            foreach ($GLOBALS['TL_CTE'] as $k=>$v)
            {
                foreach (array_keys($v) as $kk)
                {
                    $objBackendUser = BackendUser::getInstance();
                    if ($objBackendUser->getInstance($kk, 'elements'))
                    {
                        $options[$k][] = $kk;
                    }
                }
            }
        }

        return $options;
    }

    public function onLoadTypeCallback(mixed $varValue, DataContainer $dc): string
    {
        switch ($varValue) {
            case RecipeListController::TYPE:
            case RecipeReaderController::TYPE:
                $GLOBALS['TL_DCA'][$dc->table]['fields']['text']['eval']['mandatory'] = false;
                break;
            default:
                break;
        }

        return $varValue;
    }

    public function stepIngredientOptionsCallback(DataContainer $dc): array
    {
        $options = [];

        foreach ($this->getIngredientAvailability($dc) as $alias => $data) {
            if ($data['remaining'] <= 0) {
                continue;
            }

            $ingredient = IngredientModel::findOneByAlias($alias);
            $unit = UnitModel::findOneByAlias($data['unit']);
            $options[$alias] = trim(($ingredient->title ?? $alias) . ' (' . self::formatAmount($data['remaining']) . ' ' . ($unit->shortcode ?: ($unit->title ?? '')) . ')');
        }

        return $options;
    }

    public function stepIngredientAmountOptionsCallback(DataContainer $dc): array
    {
        $max = 0;

        foreach ($this->getIngredientAvailability($dc) as $data) {
            $max = max($max, (int) floor($data['remaining']));
        }

        // ponytail: one shared 1..max range for every row, the exact per-ingredient cap is
        // enforced server-side in onSaveIngredientsCallback(); a per-row cascading select
        // would need AJAX and wasn't the chosen trade-off.
        $options = [];
        for ($i = 1; $i <= $max; $i++) {
            $options[$i] = (string) $i;
        }

        return $options;
    }

    public function onSaveIngredientsCallback(mixed $varValue, DataContainer $dc)
    {
        $availability = $this->getIngredientAvailability($dc);
        $seen = [];

        foreach (StringUtil::deserialize($varValue, true) as $row) {
            if (empty($row['ingredient']) || $row['amount'] === '' || $row['amount'] === null) {
                continue;
            }

            if (isset($seen[$row['ingredient']])) {
                $ingredient = IngredientModel::findOneByAlias($row['ingredient']);
                throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['ingredientDuplicate'], $ingredient->title ?? $row['ingredient']));
            }

            $seen[$row['ingredient']] = true;
            $remaining = $availability[$row['ingredient']]['remaining'] ?? 0;

            if ((float) $row['amount'] > $remaining) {
                $ingredient = IngredientModel::findOneByAlias($row['ingredient']);
                throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['ingredientAmountExceeded'], $ingredient->title ?? $row['ingredient'], self::formatAmount($remaining)));
            }
        }

        return $varValue;
    }

    private function getIngredientAvailability(DataContainer $dc): array
    {
        $availability = [];
        $recipe = RecipeModel::findByPk($dc->activeRecord->pid ?? null);

        if ($recipe === null) {
            return $availability;
        }

        foreach (StringUtil::deserialize($recipe->ingredients, true) as $row) {
            if (empty($row['ingredient'])) {
                continue;
            }

            $availability[$row['ingredient']] ??= ['unit' => $row['unit'] ?? '', 'remaining' => 0.0];
            $availability[$row['ingredient']]['remaining'] += (float) ($row['amount'] ?? 0);
        }

        $steps = ContentModel::findBy(
            ['tl_content.pid=?', 'tl_content.ptable=?', 'tl_content.type=?', 'tl_content.id!=?'],
            [$recipe->id, RecipeModel::getTable(), RecipeStepController::TYPE, (int) ($dc->activeRecord->id ?? 0)]
        );

        if ($steps !== null) {
            foreach ($steps as $step) {
                foreach (StringUtil::deserialize($step->ingredients, true) as $row) {
                    if (empty($row['ingredient']) || !isset($availability[$row['ingredient']])) {
                        continue;
                    }

                    $availability[$row['ingredient']]['remaining'] -= (float) ($row['amount'] ?? 0);
                }
            }
        }

        return $availability;
    }

    private static function formatAmount(float $amount): string
    {
        return rtrim(rtrim(number_format($amount, 2, '.', ''), '0'), '.');
    }
}