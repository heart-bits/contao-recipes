<?php

declare(strict_types=1);

namespace Heartbits\ContaoRecipes\Models;

use Contao\Model;

class RecipeModel extends Model
{
    protected static $strTable = 'tl_recipe';

    public static function countPublished($blnFeatured=null, array $arrOptions=[])
    {
        $t = static::$strTable;
        $arrColumns = [];

        if ($blnFeatured === true) {
            $arrColumns[] = "$t.featured='1'";
        }
        elseif ($blnFeatured === false) {
            $arrColumns[] = "$t.featured=''";
        }

        if (!static::isPreviewMode($arrOptions)) {
            $arrColumns[] = "$t.published=1";
        } else {
            $arrColumns[] = "$t.published=1 OR $t.published=0";
        }

        return static::countBy($arrColumns, null, $arrOptions);
    }

    public static function findPublished($blnFeatured = false, int $intLimit = 0, int $intOffset = 0, array $arrOptions = [])
    {
        $t = static::$strTable;
        $arrColumns = [];

        if ($blnFeatured === true) {
            $arrColumns[] = "$t.featured='1'";
        } elseif ($blnFeatured === false) {
            $arrColumns[] = "$t.featured=''";
        }

        if (!static::isPreviewMode($arrOptions)) {
            $arrColumns[] = "$t.published=1";
        } else {
            $arrColumns[] = "$t.published=1 OR $t.published=0";
        }

        if (!isset($arrOptions['order'])) {
            $arrOptions['order'] = "$t.id DESC";
        }

        $arrOptions['limit'] = $intLimit;
        $arrOptions['offset'] = $intOffset;

        return static::findBy($arrColumns, null, $arrOptions);
    }
}