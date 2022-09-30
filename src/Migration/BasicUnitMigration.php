<?php

namespace Heartbits\ContaoRecipes\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

class BasicUnitMigration extends AbstractMigration
{
    private Connection $connection;
    private string $table = 'tl_recipe_unit';
    private array $units = [
        ['title' => 'Milligramm', 'alias' => 'mg', 'shortcode' => 'mg'],
        ['title' => 'Gramm', 'alias' => 'g', 'shortcode' => 'g'],
        ['title' => 'Kilogramm', 'alias' => 'kg', 'shortcode' => 'kg'],
        ['title' => 'Pfund', 'alias' => 'pfd', 'shortcode' => 'pfd'],
        ['title' => 'Milliliter', 'alias' => 'ml', 'shortcode' => 'ml'],
        ['title' => 'Centiliter', 'alias' => 'cl', 'shortcode' => 'cl'],
        ['title' => 'Deziliter', 'alias' => 'dl', 'shortcode' => 'dl'],
        ['title' => 'Liter', 'alias' => 'l', 'shortcode' => 'l'],
        ['title' => 'Mass', 'alias' => 'mass', 'shortcode' => 'mass'],
        ['title' => 'Tropfen', 'alias' => 'tr', 'shortcode' => 'Tr'],
        ['title' => 'Spritzer', 'alias' => 'sp', 'shortcode' => 'Sp'],
        ['title' => 'Schuss', 'alias' => 'schuss', 'shortcode' => 'Schuss'],
        ['title' => 'Prise', 'alias' => 'pr', 'shortcode' => 'Pr'],
        ['title' => 'Messerspitze', 'alias' => 'msp', 'shortcode' => 'Msp'],
        ['title' => 'Teelöffel', 'alias' => 'tl', 'shortcode' => 'TL'],
        ['title' => 'Esslöffel', 'alias' => 'el', 'shortcode' => 'EL'],
        ['title' => 'Tasse', 'alias' => 'tas', 'shortcode' => 'Tas'],
        ['title' => 'Bund', 'alias' => 'bd', 'shortcode' => 'Bd'],
        ['title' => 'Scheibe', 'alias' => 'sc', 'shortcode' => 'Sc'],
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
