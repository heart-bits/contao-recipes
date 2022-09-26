<?php

namespace Heartbits\ContaoRecipes\EventListener\DataContainer;

use Contao\Database;
use Contao\DataContainer;
use Contao\System;
use Heartbits\ContaoRecipes\Models\CategoryModel;
use Symfony\Component\Config\Definition\Exception\Exception;

class RecipeCallbackListener
{
    public function onSaveCallback(string $value, DataContainer $dc): string
    {
        $aliasExists = function (string $alias) use ($dc): bool {
            $db = Database::getInstance();
            return $db->prepare("SELECT id FROM " . $dc->table . " WHERE alias=? AND id!=?")->execute($alias, $dc->id)->numRows > 0;
        };

        if (!$value) {
            ($dc->table === 'tl_recipe_unit') ? $alias = $dc->activeRecord->shortcode : $alias = $dc->activeRecord->title;
            $value = System::getContainer()->get('contao.slug')->generate($alias, $dc->activeRecord->pid, $aliasExists);
        } elseif (preg_match('/^[1-9]\d*$/', $value)) {
            throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasNumeric'], $value));
        } elseif ($aliasExists($value)) {
            throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $value));
        }

        return $value;
    }

    public function loadCategoriesCallback(DataContainer $dc): array
    {
        $options = [];
        $objCategories = CategoryModel::findPublished([]);
        if ($objCategories) {
            foreach ($objCategories as $category) {
                $options[$category->id] = $category->title;
            }
        }
        return $options;
    }
}