<?php

namespace Heartbits\ContaoRecipes\Widgets;

use Contao\Image;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Contao\Widget;
use Heartbits\ContaoRecipes\Models\IngredientModel;
use Heartbits\ContaoRecipes\Models\UnitModel;

class InputIngredients extends Widget
{
    protected $blnSubmitInput = true;
    protected $blnForAttribute = true;
    protected $strTemplate = 'be_widget';

    public function generate(): string
    {
        $arrUnits = $this->getUnits();
        $arrIngredients = $this->getIngredients();
        $arrButtons = ['rcopy', 'rdelete', 'rdrag'];

        // Make sure there is at least an empty array
        if (empty($this->value) || !\is_array($this->value)) {
            $this->value = [[
                0 => '',
                1 => '',
                2 => ''
            ]];
        }
        if (!$this->class) $this->class = 'tl_tablewizard';

        $return = $this->setTableHeaderString($this->id, $this->class);

        foreach ($this->value as $i => $value) {
            $return .= $this->setTableRowString($this->id, $value, $this->name, $i, $arrUnits, $arrIngredients, $arrButtons);
        }

        $return .= $this->setTableFooterString($this->id);

        return $return;
    }

    public function setTableHeaderString($id, $class): string
    {
        return '<div id="inputIngredient"><table id="ctrl_' . $id . '" class="' . $class . '"><thead><tr><th class="recipe_amount">' . $GLOBALS['TL_LANG']['tl_recipe']['headers']['amount'] . '</th><th class="recipe_unit">' . $GLOBALS['TL_LANG']['tl_recipe']['headers']['unit'] . '</th><th class="recipe_ingredient">' . $GLOBALS['TL_LANG']['tl_recipe']['headers']['ingredient'] . '</th><th class="recipe_buttons"></th></tr></thead><tbody class="sortable">';
    }

    public function setTableRowString($id, $value, $name, $i, $arrUnits, $arrIngredients, $arrButtons): string
    {
        return '<tr><td class="recipe_amount">' . $this->setAmountInputString($id, $value, $name, $i) . '</td><td class="recipe_unit">' . $this->setUnitSelectString($id, $value, $name, $i, $arrUnits) . '</td><td class="recipe_ingredient">' . $this->setIngredientSelectString($id, $value, $name, $i, $arrIngredients) . '</td><td class="recipe_buttons">' . $this->setButtonString($arrButtons) . '</td></tr>';
    }

    public function setAmountInputString($id, $currentValue, $name, $i): string
    {
        return '<input id="' . $id . '_amount_' . $i . '" type="text" name="' . $id . '[' . $i . '][0]" class="tl_text" value="' . StringUtil::specialchars($currentValue[0]) . '">';
    }

    public function setUnitSelectString($id, $currentValue, $name, $i, $arrUnits): string
    {
        $unitSelect = '<select id="' . $id . '_unit_' . $i . ' " name="' . $id . '[' . $i . '][1]" class="tl_select" onfocus="Backend.getScrollOffset()">';
        ($currentValue[1] == '') ? $unitSelect .= '<option value="" selected>-</option>' : $unitSelect .= '<option value="">-</option>';
        foreach ($arrUnits as $key => $value) {
            ($currentValue[1] == $key) ? $unitSelect .= '<option value="' . $key . '" selected>' . $value . '</option>' : $unitSelect .= '<option value="' . $key . '">' . $value . '</option>';
        }
        $unitSelect .= '</select>';
        return $unitSelect;
    }

    public function setIngredientSelectString($id, $currentValue, $name, $i, $arrIngredients): string
    {
        $ingredientSelect = '<select id="' . $id . '_ingredient_' . $i . '" name="' . $id . '[' . $i . '][2]" class="tl_select" onfocus="Backend.getScrollOffset()">';
        ($currentValue[2] == '') ? $ingredientSelect .= '<option value="" selected>-</option>' : $ingredientSelect .= '<option value="">-</option>';
        foreach ($arrIngredients as $key => $value) {
            ($currentValue[2] == $key) ? $ingredientSelect .= '<option value="' . $key . '" selected>' . $value . '</option>' : $ingredientSelect .= '<option value="' . $key . '">' . $value . '</option>';
        }
        $ingredientSelect .= '</select>';
        return $ingredientSelect;
    }

    public function setButtonString($arrButtons): string
    {
        $buttons = '';
        foreach ($arrButtons as $button) {
            if ($button == 'rdrag') {
                $buttons .= ' <button type="button" class="drag-handle" title="' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['move']) . '" aria-hidden="true">' . Image::getHtml('drag.svg') . '</button>';
            } else {
                $buttons .= ' <button type="button" class="tl_tablewizard_img" data-command="' . $button . '" title="' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['lw_' . substr($button, 1)]) . '">' . Image::getHtml(substr($button, 1) . '.svg') . '</button>';
            }
        }
        return $buttons;
    }

    public function setTableFooterString($id): string
    {
        return '</tbody></table></div><script>Backend.tableWizard("ctrl_' . $id . '")</script>';
    }

    public function getUnits(): array
    {
        $units = [];

        $objUnits = UnitModel::findAll();
        if ($objUnits !== null) {
            while ($objUnits->next()) {
                $units[$objUnits->alias] = $objUnits->shortcode;
            }
        }

        return $units;
    }

    public function getIngredients(): array
    {
        $units = [];

        $objIngredients = IngredientModel::findAll();
        if ($objIngredients !== null) {
            while ($objIngredients->next()) {
                $units[$objIngredients->alias] = $objIngredients->title;
            }
        }

        return $units;
    }
}
