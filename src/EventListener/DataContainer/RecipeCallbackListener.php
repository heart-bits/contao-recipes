<?php

namespace Heartbits\ContaoRecipes\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\Database;
use Contao\DataContainer;
use Contao\System;
use Symfony\Component\Config\Definition\Exception\Exception;

class RecipeCallbackListener
{
    #[AsCallback(table: 'tl_recipe', target: 'list.operations.test.button', priority: 100)]
    public function onToggleCallback(array $record, ?string $href, string $label, string $title, ?string $icon, string $attributes, string $table, array $rootRecords, ?array $childRecords, bool $isCircular, ?string $prevLabel, ?string $nextLabel, DataContainer $dc): string
    {
        return '<a href="">TEST</a>';
    }

    #[AsCallback(table: 'tl_recipe', target: 'fields.alias.save', priority: 100)]
    #[AsCallback(table: 'tl_recipe_unit', target: 'fields.alias.save', priority: 100)]
    #[AsCallback(table: 'tl_recipe_ingredient', target: 'fields.alias.save', priority: 100)]
    #[AsCallback(table: 'tl_recipe_category', target: 'fields.alias.save', priority: 100)]
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
}