<?php

namespace Heartbits\ContaoRecipes\EventListener\DataContainer;

use Contao\BackendUser;
use Contao\CoreBundle\Slug\Slug;
use Contao\DataContainer;
use Contao\System;
use Heartbits\ContaoRecipes\Models\CategoryModel;
use Symfony\Component\Config\Definition\Exception\Exception;

class RecipeCallbackListener
{
    public function __construct(private Slug $slug)
    {
    }

    public function onSaveCallback(string $value, DataContainer $dc): string
    {
        if (!$value) {
            ($dc->table === 'tl_recipe_unit') ? $alias = $dc->activeRecord->shortcode : $alias = $dc->activeRecord->title;
            $value = $this->slug->generate($alias);
        } elseif (preg_match('/^[1-9]\d*$/', $value)) {
            throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasNumeric'], $value));
        }

        return $value;
    }

    public function loadCategoriesCallback(DataContainer $dc): array
    {
        $options = [];
        $objCategories = CategoryModel::findAllPublished([]);
        if ($objCategories) {
            foreach ($objCategories as $category) {
                $options[$category->id] = $category->title;
            }
        }
        return $options;
    }

    public function loadDate($value, DataContainer $dc): string
    {
        return strtotime(date('Y-m-d', $value) . ' 00:00:00');
    }
}