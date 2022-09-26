<?php

declare(strict_types=1);

namespace Heartbits\ContaoRecipes\Models;

use Contao\Model;

class CategoryModel extends Model
{
    protected static $strTable = 'tl_recipe_category';

    public static function findPublished(array $arrOptions=[]) {
        $t = static::$strTable;
        $arrColumns = [];

        if (!static::isPreviewMode($arrOptions)) {
            $arrColumns[] = "$t.published=1";
        } else {
            $arrColumns[] = "$t.published=1 OR $t.published=0";
        }

        return static::findBy($arrColumns, null, $arrOptions);
    }
}