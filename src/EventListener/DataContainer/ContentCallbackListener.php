<?php

namespace Heartbits\ContaoRecipes\EventListener\DataContainer;

use Contao\BackendUser;
use Contao\DataContainer;
use Heartbits\ContaoRecipes\Controller\ContentElement\RecipeImageController;
use Heartbits\ContaoRecipes\Controller\ContentElement\RecipeStepController;
use Symfony\Component\Config\Definition\Exception\Exception;

class ContentCallbackListener
{
    public function onLoadTypeCallback(DataContainer $dc): array
    {
        $options = [];

        if ($dc->activeRecord->ptable === 'tl_recipe') {
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
}