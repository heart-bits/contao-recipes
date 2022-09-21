<?php

declare(strict_types=1);

namespace Heartbits\ContaoRecipes\Models;

use Contao\Model;

/**
 * Reads and writes companies.
 */
class Recipe extends Model
{
    protected static string $strTable = 'tl_recipe';
}