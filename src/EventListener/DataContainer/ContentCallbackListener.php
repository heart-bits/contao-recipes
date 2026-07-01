<?php

namespace Heartbits\ContaoRecipes\EventListener\DataContainer;

use Contao\BackendUser;
use Contao\DataContainer;
use Heartbits\ContaoRecipes\Controller\ContentElement\RecipeImageController;
use Heartbits\ContaoRecipes\Controller\ContentElement\RecipeListController;
use Heartbits\ContaoRecipes\Controller\ContentElement\RecipeReaderController;
use Heartbits\ContaoRecipes\Controller\ContentElement\RecipeStepController;
use Symfony\Component\Config\Definition\Exception\Exception;

class ContentCallbackListener
{
    public function typeOptionsCallback(DataContainer $dc): array
    {
        $options = [];

        if ($dc->getCurrentRecord()['ptable'] === 'tl_recipe') {
            $options = [
                RecipeStepController::TYPE => $GLOBALS['TL_LANG']['CTE'][RecipeStepController::TYPE][0],
                RecipeImageController::TYPE => $GLOBALS['TL_LANG']['CTE'][RecipeImageController::TYPE][0],
                'gallery' => $GLOBALS['TL_LANG']['CTE']['gallery'][0]
            ];
        } else {
            foreach ($GLOBALS['TL_CTE'] as $k=>$v)
            {
                foreach (array_keys($v) as $kk)
                {
                    $objBackendUser = BackendUser::getInstance();
                    if ($objBackendUser->getInstance($kk, 'elements'))
                    {
                        $options[$k][] = $kk;
                    }
                }
            }
        }

        return $options;
    }

    public function onLoadTypeCallback(mixed $varValue, DataContainer $dc): string
    {
        switch ($varValue) {
            case RecipeListController::TYPE:
            case RecipeReaderController::TYPE:
                $GLOBALS['TL_DCA'][$dc->table]['fields']['text']['eval']['mandatory'] = false;
                break;
            default:
                break;
        }

        return $varValue;
    }
}