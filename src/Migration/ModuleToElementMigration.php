<?php

namespace Heartbits\ContaoRecipes\Migration;

use App\Controller\ContentElement\RatesOverviewController;
use Contao\ContentModel;
use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Contao\ModuleModel;
use Doctrine\DBAL\Connection;
use Heartbits\ContaoRecipes\Controller\ContentElement\RecipeFilterController;
use Heartbits\ContaoRecipes\Controller\ContentElement\RecipeListController;
use Heartbits\ContaoRecipes\Controller\ContentElement\RecipeReaderController;

class ModuleToElementMigration extends AbstractMigration
{
    public array $elementsToMigrate = [];
    public function __construct(private readonly Connection $connection)
    {
    }

    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

        if (!$schemaManager->tablesExist([ContentModel::getTable(), ModuleModel::getTable()])) {
            return false;
        }

        $moduleIds = [];
        $modules = $this->connection->executeQuery("SELECT * FROM " . ModuleModel::getTable() . " WHERE type = '".RecipeListController::TYPE."' OR type = '".RecipeReaderController::TYPE."' OR type = '".RecipeFilterController::TYPE."'")->fetchAllAssociative();
        if (count($modules) > 0) {
            foreach ($modules as $module) {
                $moduleIds[$module['id']] = [
                    'id' => $module['id'],
                    'type' => $module['type'],
                    'name' => $module['name'],
                    'size' => $module['imgSize'],
                    'jumpTo' => $module['jumpTo'],
                    'numberOfItems' => $module['numberOfItems'],
                    'skipFirst' => $module['skipFirst'],
                    'recipe_featured' => $module['recipe_featured'],
                    'recipe_order' => $module['recipe_order'],
                    'perPage' => $module['perPage'],
                ];
            }
        }

        $elements = $this->connection->executeQuery("SELECT id, module FROM " . ContentModel::getTable() . " WHERE type = 'module'")->fetchAllAssociative();
        if (count($elements) > 0) {
            foreach ($elements as $element) {
                if (isset($moduleIds[$element['module']])) {
                    $this->elementsToMigrate[$element['id']] = [
                        'id' => $element['id'],
                        'module' => $moduleIds[$element['module']],
                    ];
                }
            }
        }

        return count($this->elementsToMigrate) > 0;
    }

    #[\Override]
    public function run(): MigrationResult
    {
        $count = 0;
        $this->connection->executeStatement('ALTER TABLE " . ContentModel::getTable() . " ADD recipe_featured varchar(32) COLLATE ascii_bin NOT NULL default "all_recipes"');
        $this->connection->executeStatement('ALTER TABLE " . ContentModel::getTable() . " ADD recipe_order varchar(32) COLLATE ascii_bin NOT NULL default "order_date_desc"');

        foreach ($this->elementsToMigrate as $element) {
            $this->connection->executeQuery("UPDATE " . ContentModel::getTable() . " SET type = :type, name = :name, size = :size, jumpTo = :jumpTo, numberOfItems = :numberOfItems, skipFirst = :skipFirst, recipe_featured = :recipe_featured, recipe_order = :recipe_order, perPage = :perPage WHERE id = :id", [
                'id' => $element['id'],
                'type' => $element['module']['type'],
                'name' => $element['module']['name'],
                'size' => $element['module']['imgSize'],
                'jumpTo' => $element['module']['jumpTo'],
                'numberOfItems' => $element['module']['numberOfItems'],
                'skipFirst' => $element['module']['skipFirst'],
                'recipe_featured' => $element['module']['recipe_featured'],
                'recipe_order' => $element['module']['recipe_order'],
                'perPage' => $element['module']['perPage']
            ]);

            $count++;
        }

        $this->elementsToMigrate = [];

        return $this->createResult(
            true,
            'Migrated '.$count.' redesigned rates overview modules.'
        );
    }
}