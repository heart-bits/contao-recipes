<?php

declare(strict_types=1);

namespace Heartbits\ContaoRecipes\Models;

use Contao\Model;

class IngredientModel extends Model
{
    protected static $strTable = 'tl_recipe_ingredient';

    public static function fetchOptionsForDca()
    {
        $ingredients = [];

        $objIngredients = self::findAll(['order' => self::getTable() . '.title']);
        if ($objIngredients !== null) {
            while ($objIngredients->next()) {
                $ingredients[$objIngredients->alias] = $objIngredients->title;
            }
        }

        return $ingredients;
    }
}