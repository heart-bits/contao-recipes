<?php

declare(strict_types=1);

namespace Heartbits\ContaoRecipes\Models;

use Contao\Model;

class UnitModel extends Model
{
    protected static $strTable = 'tl_recipe_unit';

    public static function fetchOptionsForDca()
    {
        $units = [];

        $objUnits = self::findAll(['order' => self::getTable() . '.title']);
        if ($objUnits !== null) {
            while ($objUnits->next()) {
                $units[$objUnits->alias] = $objUnits->title;
            }
        }

        return $units;
    }
}