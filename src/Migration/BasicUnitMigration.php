<?php

namespace Heartbits\ContaoRecipes\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

class BasicUnitMigration extends AbstractMigration
{
    private Connection $connection;
    private string $table = 'tl_recipe_unit';
    // TODO: Add more basic units
    private array $units = [
        ['title' => 'Prise', 'alias' => 'prise', 'shortcode' => 'Prise'],
        ['title' => 'Esslöffel', 'alias' => 'el', 'shortcode' => 'EL'],
        ['title' => 'Teelöffel', 'alias' => 'tl', 'shortcode' => 'TL']
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

        // Import basic units into table
        foreach ($this->units as $unit) {
            $this->connection->executeUpdate('INSERT INTO ' . $this->table . ' (tstamp, title, alias, shortcode) VALUES (' . $date->getTimestamp() . ', "' . $unit['title'] . '", "' . $unit['alias'] . '", "' . $unit['shortcode'] . '")');
        }

        return new MigrationResult(
            true,
            'Migrated the basic units provided by the bundle developer.'
        );
    }
}
