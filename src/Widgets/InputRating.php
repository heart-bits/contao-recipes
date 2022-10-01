<?php

namespace Heartbits\ContaoRecipes\Widgets;

use Contao\Widget;

class InputRating extends Widget
{
    protected $blnSubmitInput = true;
    protected $blnForAttribute = true;
    protected $strTemplate = 'be_widget';

    public function generate(): string
    {
        if (!$this->value) {
            $this->value = 0;
        }
        if (!$this->class) $this->class = 'tl_rating';
        ($this->mandatory) ? $this->arrAttributes['required'] = ' required=""' : $this->arrAttributes['required'] = '';

        $return = '<div class="rating-container"><div class="zero-based-rating"><input class="' . $this->class . '" type="range" name="' . $this->name . '" id="ctrl_' . $this->id . '"' . $this->arrAttributes['required'] . ' min="0" max="5" class="tl_rating" value="' . $this->value . '" onfocus="Backend.getScrollOffset()"></div></div>';

        return $return;
    }
}
