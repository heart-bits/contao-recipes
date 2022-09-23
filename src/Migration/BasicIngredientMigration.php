<?php

namespace Heartbits\ContaoRecipes\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

class BasicIngredientMigration extends AbstractMigration
{
    private Connection $connection;
    private string $table = 'tl_recipe_ingredient';
    // TODO: Add more basic ingredients
    private array $ingredients = [
        ['title' => 'Salz', 'alias' => 'salz'],
        ['title' => 'Pfeffer', 'alias' => 'pfeffer'],
        ['title' => 'Olivenöl', 'alias' => 'olivenoel']
    ];

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->getSchemaManager();

        if (!$schemaManager->tablesExist([$this->table])) {
            return false;
        }

        $rowCount = $this->connection->executeQuery("SELECT * FROM " . $this->table)->rowCount();

        return ($rowCount === 0);
    }

    public function run(): MigrationResult
    {
        $date = new \DateTime();

        // Import basic ingredients into table
        foreach ($this->ingredients as $ingredient) {
            $this->connection->executeUpdate('INSERT INTO ' . $this->table . ' (tstamp, title, alias) VALUES (' . $date->getTimestamp() . ', "' . $ingredient['title'] . '", "' . $ingredient['alias'] . '")');
        }

        return new MigrationResult(
            true,
            'Migrated the basic ingredient provided by the bundle developer.'
        );
    }
}
