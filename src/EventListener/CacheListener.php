<?php

namespace Heartbits\ContaoRecipes\EventListener;

use Contao\CoreBundle\Framework\ContaoFramework;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

class CacheListener implements CacheWarmerInterface
{
    private string $projectDir;
    private ContaoFramework $framework;

    public function __construct(ContaoFramework $framework, string $projectDir)
    {
        $this->framework = $framework;
        $this->projectDir = $projectDir;
    }

    public function isOptional(): bool
    {
        return true;
    }

    public function warmUp($cacheDir)
    {
        $this->UpdateTableWizardFunction();
    }

    // Fixes the tableWizard for our use case in the InputIngredients
    private function UpdateTableWizardFunction(): void
    {
        $this->framework->initialize();
        $rootPath = $this->projectDir . '/';
        $pluginPath = $rootPath . 'web/bundles/contaocore/core.min.js';

        if (file_exists($pluginPath)) {
            $oldContent = file_get_contents($pluginPath);
            $updatedContent = str_replace('t=n[o].getFirst("textarea")', 't=n[o].getFirst("textarea,input,select")', $oldContent);
            file_put_contents($pluginPath, $updatedContent);
        }
    }
}