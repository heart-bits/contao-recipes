<?php

namespace Heartbits\ContaoRecipes\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Heartbits\ContaoRecipes\HeartbitsContaoRecipesBundle;

class Plugin implements BundlePluginInterface
{
    public function getBundles(ParserInterface $parser)
    {
        return [
            BundleConfig::create(HeartbitsContaoRecipesBundle::class)
                ->setLoadAfter([ContaoCoreBundle::class])
        ];
    }
}
