<?php

namespace Heartbits\ContaoRecipes\EventListener;

use Contao\CoreBundle\ContaoCoreBundle;
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
        $this->framework->initialize();
        var_dump(ContaoCoreBundle::getVersion() > 5);
        if (ContaoCoreBundle::getVersion() > 5) {
            $this->UpdateTableWizardFunctionForContao5();
        } else {
            $this->UpdateTableWizardFunctionForContao413();
        }
    }

    // Fixes the tableWizard for our use case in the InputIngredients
    private function UpdateTableWizardFunctionForContao5(): void
    {
        $rootPath = $this->projectDir . '/';
        (file_exists($rootPath . 'web/')) ? $rootFolder = 'web/' : $rootFolder = 'public/';
        $files = [];
        $pluginPaths = preg_grep('~^backend.*\.js$~', scandir($rootPath . $rootFolder . 'bundles/contaocore/'));
        foreach ($pluginPaths as $path) {
            $files[] = $path;
        }
        $pluginPath = $rootPath . $rootFolder . 'bundles/contaocore/' . $files[0];

        if (!empty($files) && file_exists($pluginPath)) {
            // Fix input/select names after row copy
            $content = file_get_contents($pluginPath);
            $updatedContent = str_replace('(t=n[r].getFirst("textarea"))&&(t.name=t.name.replace(/\[[0-9]+][[0-9]+]/g,"["+i+"]["+r+"]"))', '(t=n[r].getFirst("textarea,input,select"))&&(t.name=t.name.replace(/\[[0-9]+][[0-9]+]/g,"["+i+"]["+r+"]"),(t.hasClass("unit_select"))&&(t.id="ingredients_unit_"+i),(t.hasClass("ingredient_select"))&&(t.id="ingredients_ingredient_"+i))', $content);
            file_put_contents($pluginPath, $updatedContent);

            // Add tl_chosen functionality after row copy
            if (str_contains($content, 'c.inject(e,"after"),r(c),s(i),c.getFirst("td").getFirst("textarea").select()')) {
                $content = file_get_contents($pluginPath);
                $updatedContent = str_replace('c.inject(e,"after"),r(c),s(i),c.getFirst("td").getFirst("textarea").select()', 'c.inject(e,"after"),r(c),s(i),c.getFirst("td").getFirst("textarea,input,select").select()', $content);
                file_put_contents($pluginPath, $updatedContent);
            }
        }
    }

    private function UpdateTableWizardFunctionForContao413(): void
    {
        $rootPath = $this->projectDir . '/';
        (file_exists($rootPath . 'web/')) ? $rootFolder = 'web/' : $rootFolder = 'public/';
        $pluginPath = $rootPath . $rootFolder . 'bundles/contaocore/core.min.js';

        if (file_exists($pluginPath)) {
            // Fix input/select names after row copy
            $content = file_get_contents($pluginPath);
            $updatedContent = str_replace('(t=n[o].getFirst("textarea"))&&(t.name=t.name.replace(/\[[0-9]+][[0-9]+]/g,"["+a+"]["+o+"]"))', '(t=n[o].getFirst("textarea,input,select"))&&(t.name=t.name.replace(/\[[0-9]+][[0-9]+]/g,"["+a+"]["+o+"]"),(t.hasClass("unit_select"))&&(t.id="ingredients_unit_"+a),(t.hasClass("ingredient_select"))&&(t.id="ingredients_ingredient_"+a))', $content);
            file_put_contents($pluginPath, $updatedContent);

            // Add tl_chosen functionality after row copy
            if (str_contains($content, 'h(l),f(g),Backend.addInteractiveHelp()}')) {
                $content = file_get_contents($pluginPath);
                $updatedContent = str_replace('h(l),f(g),Backend.addInteractiveHelp()}', 'h(l),f(g),Backend.addInteractiveHelp(),$$(".chzn-container").destroy(),$$(".tl_chosen").chosen()}', $content);
                file_put_contents($pluginPath, $updatedContent);
            }
        }
    }
}