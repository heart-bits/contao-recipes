<?php

use Heartbits\ContaoRecipes\EventListener\DataContainer\ContentCallbackListener;

$GLOBALS['TL_DCA']['tl_content']['fields']['type']['options_callback'] = [[ContentCallbackListener::class, 'onLoadTypeCallback']];
