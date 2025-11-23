<?php

namespace Heartbits\ContaoRecipes\Widgets;

use Contao\Image;
use Contao\Input;
use Contao\StringUtil;
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
                'amount' => '',
                'unit' => '',
                'ingredient' => ''
            ]];
        }
        if (!$this->class) $this->class = 'tl_listwizard';

        $return = $this->setTableHeaderString($this->id, $this->class);

        foreach ($this->value as $i => $value) {
            $return .= $this->setTableRowString($this->id, $value, $this->name, $i, $arrUnits, $arrIngredients, $arrButtons);
        }

        $return .= $this->setTableFooterString($this->id);

        return $return;
    }

    public function setTableHeaderString($id, $class): string
    {
        return '<div class="' . $class . '" id="inputIngredient">
                <table data-controller="contao--row-wizard" id="ctrl_' . $id . '">
                <thead>
                <tr>
                <th class="recipe_drag"></th>
                <th class="recipe_amount">' . $GLOBALS['TL_LANG']['tl_recipe']['headers']['amount'] . '</th>
                <th class="recipe_unit">' . $GLOBALS['TL_LANG']['tl_recipe']['headers']['unit'] . '</th>
                <th class="recipe_ingredient">' . $GLOBALS['TL_LANG']['tl_recipe']['headers']['ingredient'] . '</th>
                <th class="recipe_buttons"></th>
                </tr>
                </thead>
                <tbody class="sortable" data-controller="contao--sortable" data-contao--sortable-handle-value=".drag-handle" data-action="contao--sortable:update->contao--row-wizard#updateSorting" data-contao--row-wizard-target="body">';
    }

    public function setTableRowString($id, $value, $name, $i, $arrUnits, $arrIngredients, $arrButtons): string
    {
        return '<tr data-contao--row-wizard-target="row">
                <td class="recipe_drag"><button type="button" class="drag-handle" title="Move the item via drag and drop" aria-hidden="true" data-action="keydown-&gt;contao--sortable#move" data-contao--tooltips-target="tooltip"><img src="/system/themes/flexible/icons/drag--dark.svg" width="16" height="16" alt="" class="color-scheme--dark" loading="lazy"><img src="/system/themes/flexible/icons/drag.svg" width="16" height="16" alt="" class="color-scheme--light" loading="lazy"></button></td>
                <td class="recipe_amount">' . $this->setAmountInputString($id, $value, $name, $i) . '</td>
                <td class="recipe_unit">' . $this->setUnitSelectString($id, $value, $name, $i, $arrUnits) . '</td>
                <td class="recipe_ingredient">' . $this->setIngredientSelectString($id, $value, $name, $i, $arrIngredients) . '</td>
                <td class="recipe_buttons">' . $this->setButtonString($arrButtons) . '</td>
                </tr>';
    }

    public function setAmountInputString($id, $currentValue, $name, $i): string
    {
        return '<input id="' . $id . '_amount_' . $i . '" type="text" name="' . $id . '[' . $i . '][amount]" class="tl_text" value="' . StringUtil::specialchars($currentValue['amount']) . '">';
    }

    public function setUnitSelectString($id, $currentValue, $name, $i, $arrUnits): string
    {
        $unitSelect = '<select id="' . $id . '_unit_' . $i . ' " name="' . $id . '[' . $i . '][unit]" class="unit_select tl_select tl_chosen" onfocus="Backend.getScrollOffset()">';
        ($currentValue['unit'] == '') ? $unitSelect .= '<option value="" selected>-</option>' : $unitSelect .= '<option value="">-</option>';
        foreach ($arrUnits as $key => $value) {
            ($currentValue['unit'] == $key) ? $unitSelect .= '<option value="' . $key . '" selected>' . $value . '</option>' : $unitSelect .= '<option value="' . $key . '">' . $value . '</option>';
        }
        $unitSelect .= '</select>';
        return $unitSelect;
    }

    public function setIngredientSelectString($id, $currentValue, $name, $i, $arrIngredients): string
    {
        $ingredientSelect = '<select id="' . $id . '_ingredient_' . $i . '" name="' . $id . '[' . $i . '][ingredient]" class="ingredient_select tl_select tl_chosen" onfocus="Backend.getScrollOffset()">';
        ($currentValue['ingredient'] == '') ? $ingredientSelect .= '<option value="" selected>-</option>' : $ingredientSelect .= '<option value="">-</option>';
        foreach ($arrIngredients as $key => $value) {
            ($currentValue['ingredient'] == $key) ? $ingredientSelect .= '<option value="' . $key . '" selected>' . $value . '</option>' : $ingredientSelect .= '<option value="' . $key . '">' . $value . '</option>';
        }
        $ingredientSelect .= '</select>';
        return $ingredientSelect;
    }

    public function setButtonString($arrButtons): string
    {
        return '<button type="button" data-action="contao--row-wizard#copy contao--scroll-offset#store" data-command="copy" title="Duplicate the row" data-contao--tooltips-target="tooltip"><img src="/system/themes/flexible/icons/copy.svg" width="16" height="16" alt=""></button>
                <button type="button" data-action="contao--row-wizard#delete contao--scroll-offset#store" data-command="delete" title="Delete the row" data-contao--tooltips-target="tooltip"><img src="/system/themes/flexible/icons/delete.svg" width="16" height="16" alt=""></button>';
    }

    public function setTableFooterString($id): string
    {
        return '</tbody></table></div><script>Backend.tableWizard("ctrl_' . $id . '")</script>';
    }

    public function getUnits(): array
    {
        $units = [];

        $objUnits = UnitModel::findAll(['order' => UnitModel::getTable() . '.title']);
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

        $objIngredients = IngredientModel::findAll(['order' => IngredientModel::getTable() . '.title']);
        if ($objIngredients !== null) {
            while ($objIngredients->next()) {
                $units[$objIngredients->alias] = $objIngredients->title;
            }
        }

        return $units;
    }
}
