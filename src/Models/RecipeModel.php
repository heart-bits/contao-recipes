<?php

declare(strict_types=1);

namespace Heartbits\ContaoRecipes\Models;

use Contao\Model;

class RecipeModel extends Model
{
    protected static $strTable = 'tl_recipe';

    public static function findPublished(bool $blnFeatured=false, int $intLimit=0, array $arrOptions=[])
    {
        $t = static::$strTable;
        $arrColumns = [];

        if (!static::isPreviewMode($arrOptions)) {
            $arrColumns[] = "$t.published=1";
        } else {
            $arrColumns[] = "$t.published=1 OR $t.published=0";
        }

        if ($blnFeatured === true) {
            $arrColumns[] = "$t.featured=1";
        }

        $arrOptions['order'] = "$t.id DESC";

        if ($intLimit > 0) {
            $arrOptions['limit'] = $intLimit;
        }

        return static::findBy($arrColumns, null, $arrOptions);
    }
}