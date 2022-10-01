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
        ['title' => 'Ananas', 'alias' => 'ananas'],
        ['title' => 'Apfel', 'alias' => 'apfel'],
        ['title' => 'Artischoke', 'alias' => 'artischoke'],
        ['title' => 'Aubergine', 'alias' => 'aubergine'],
        ['title' => 'Auster', 'alias' => 'auster'],
        ['title' => 'Avocado', 'alias' => 'avocado'],
        ['title' => 'Banane', 'alias' => 'banane'],
        ['title' => 'Birne', 'alias' => 'birne'],
        ['title' => 'Blattsalat', 'alias' => 'blattsalat'],
        ['title' => 'Blumenkohl', 'alias' => 'blumenkohl'],
        ['title' => 'Bohnen', 'alias' => 'bohnen'],
        ['title' => 'Brokkoli', 'alias' => 'brokkoli'],
        ['title' => 'Brombeeren', 'alias' => 'brombeeren'],
        ['title' => 'Bulgur', 'alias' => 'bulgur'],
        ['title' => 'Buttermilch', 'alias' => 'buttermilch'],
        ['title' => 'Calamari', 'alias' => 'calamari'],
        ['title' => 'Champignons', 'alias' => 'champignons'],
        ['title' => 'Chicoree', 'alias' => 'chicoree'],
        ['title' => 'Chili', 'alias' => 'chili'],
        ['title' => 'Chinakohl', 'alias' => 'chinakohl'],
        ['title' => 'Couscous', 'alias' => 'couscous'],
        ['title' => 'Cranberry', 'alias' => 'cranberry'],
        ['title' => 'Curry', 'alias' => 'curry'],
        ['title' => 'Dattel', 'alias' => 'dattel'],
        ['title' => 'Dickmilch', 'alias' => 'dickmilch'],
        ['title' => 'Dosentomaten', 'alias' => 'dosentomaten'],
        ['title' => 'Ei', 'alias' => 'ei'],
        ['title' => 'Ente', 'alias' => 'ente'],
        ['title' => 'Erbsen', 'alias' => 'erbsen'],
        ['title' => 'Erdbeeren', 'alias' => 'erdbeeren'],
        ['title' => 'Feige', 'alias' => 'feige'],
        ['title' => 'Feldsalat', 'alias' => 'feldsalat'],
        ['title' => 'Fenchel', 'alias' => 'fenchel'],
        ['title' => 'Forelle', 'alias' => 'forelle'],
        ['title' => 'Frischkäse', 'alias' => 'frischkaese'],
        ['title' => 'Frühlingszwiebel', 'alias' => 'fruehlingszwiebel'],
        ['title' => 'Garnelen', 'alias' => 'garnelen'],
        ['title' => 'Granatapfel', 'alias' => 'granatapfel'],
        ['title' => 'Grapefruit', 'alias' => 'grapefruit'],
        ['title' => 'Grünkohl', 'alias' => 'gruenkohl'],
        ['title' => 'Hackfleisch', 'alias' => 'hackfleisch'],
        ['title' => 'Haferflocken', 'alias' => 'haferflocken'],
        ['title' => 'Hähnchen', 'alias' => 'haehnchen'],
        ['title' => 'Hähnchenbrust', 'alias' => 'haehnchenbrust'],
        ['title' => 'Haselnüsse', 'alias' => 'haselnuesse'],
        ['title' => 'Heidelbeeren', 'alias' => 'heidelbeeren'],
        ['title' => 'Heilbutt', 'alias' => 'heilbutt'],
        ['title' => 'Hering', 'alias' => 'hering'],
        ['title' => 'Himbeeren', 'alias' => 'himbeeren'],
        ['title' => 'Holunderbeeren', 'alias' => 'holunderbeeren'],
        ['title' => 'Hummer', 'alias' => 'hummer'],
        ['title' => 'Ingwer', 'alias' => 'ingwer'],
        ['title' => 'Jakobsmuscheln', 'alias' => 'jakobsmuscheln'],
        ['title' => 'Joghurt', 'alias' => 'joghurt'],
        ['title' => 'Johannisbeeren', 'alias' => 'johannisbeeren'],
        ['title' => 'Kabeljau', 'alias' => 'kabeljau'],
        ['title' => 'Kalbfleisch', 'alias' => 'kalbfleisch'],
        ['title' => 'Kapern', 'alias' => 'kapern'],
        ['title' => 'Kartoffeln', 'alias' => 'kartoffeln'],
        ['title' => 'Kassler', 'alias' => 'kassler'],
        ['title' => 'Kaviar', 'alias' => 'kaviar'],
        ['title' => 'Kefir', 'alias' => 'kefir'],
        ['title' => 'Ketchup', 'alias' => 'ketchup'],
        ['title' => 'Kichererbsen', 'alias' => 'kichererbsen'],
        ['title' => 'Kidneybohnen', 'alias' => 'kidneybohnen'],
        ['title' => 'Kirschen', 'alias' => 'kirschen'],
        ['title' => 'Kirschtomaten', 'alias' => 'kirschtomaten'],
        ['title' => 'Kiwi', 'alias' => 'kiwi'],
        ['title' => 'Knoblauch', 'alias' => 'knoblauch'],
        ['title' => 'Kohlrabi', 'alias' => 'kohlrabi'],
        ['title' => 'Kokosnuss', 'alias' => 'kokosnuss'],
        ['title' => 'Kokosraspeln', 'alias' => 'kokosraspeln'],
        ['title' => 'Kopfsalat', 'alias' => 'kopfsalat'],
        ['title' => 'Krabbe', 'alias' => 'krabbe'],
        ['title' => 'Kürbis', 'alias' => 'kuerbis'],
        ['title' => 'Lachs', 'alias' => 'lachs'],
        ['title' => 'Lamm', 'alias' => 'lamm'],
        ['title' => 'Lauch', 'alias' => 'lauch'],
        ['title' => 'Linsen', 'alias' => 'linsen'],
        ['title' => 'Litschi', 'alias' => 'litschi'],
        ['title' => 'Magerquark', 'alias' => 'magerquark'],
        ['title' => 'Mais', 'alias' => 'mais'],
        ['title' => 'Mandeln', 'alias' => 'mandeln'],
        ['title' => 'Mandarine', 'alias' => 'mandarine'],
        ['title' => 'Mango', 'alias' => 'mango'],
        ['title' => 'Mangold', 'alias' => 'mangold'],
        ['title' => 'Meerrettich', 'alias' => 'meerrettich'],
        ['title' => 'Melone', 'alias' => 'melone'],
        ['title' => 'Miesmuscheln', 'alias' => 'miesmuscheln'],
        ['title' => 'Milch', 'alias' => 'milch'],
        ['title' => 'Möhren', 'alias' => 'moehren'],
        ['title' => 'Nektarine', 'alias' => 'nektarine'],
        ['title' => 'Nudeln', 'alias' => 'nudeln'],
        ['title' => 'Oliven', 'alias' => 'oliven'],
        ['title' => 'Olivenöl', 'alias' => 'olivenoel'],
        ['title' => 'Orange', 'alias' => 'orange'],
        ['title' => 'Pangasius', 'alias' => 'pangasius'],
        ['title' => 'Papaya', 'alias' => 'papaya'],
        ['title' => 'Paprika', 'alias' => 'paprika'],
        ['title' => 'Parmesan', 'alias' => 'parmesan'],
        ['title' => 'Pastinaken', 'alias' => 'pastinaken'],
        ['title' => 'Petersilie', 'alias' => 'petersilie'],
        ['title' => 'Pesto', 'alias' => 'pesto'],
        ['title' => 'Pfeffer', 'alias' => 'Pfeffer'],
        ['title' => 'Pfirsich', 'alias' => 'pfirsich'],
        ['title' => 'Pflaumen', 'alias' => 'pflaumen'],
        ['title' => 'Pilz', 'alias' => 'pilz'],
        ['title' => 'Pinienkerne', 'alias' => 'pinienkerne'],
        ['title' => 'Preiselbeeren', 'alias' => 'preiselbeeren'],
        ['title' => 'Pute', 'alias' => 'pute'],
        ['title' => 'Putenbrust', 'alias' => 'putenbrust'],
        ['title' => 'Quark', 'alias' => 'quark'],
        ['title' => 'Quinoa', 'alias' => 'quinoa'],
        ['title' => 'Quitten', 'alias' => 'quitten'],
        ['title' => 'Radicchio', 'alias' => 'radicchio'],
        ['title' => 'Radieschen', 'alias' => 'radieschen'],
        ['title' => 'Rapsöl', 'alias' => 'rapsöl'],
        ['title' => 'Räucherlachs', 'alias' => 'raeucherlachs'],
        ['title' => 'Reis', 'alias' => 'reis'],
        ['title' => 'Rettich', 'alias' => 'rettich'],
        ['title' => 'Rhabarber', 'alias' => 'rhabarber'],
        ['title' => 'Ricotta', 'alias' => 'ricotta'],
        ['title' => 'Rindfleisch', 'alias' => 'rindfleisch'],
        ['title' => 'Risotto', 'alias' => 'risotto'],
        ['title' => 'Römersalat', 'alias' => 'roemersalat'],
        ['title' => 'Rosenkohl', 'alias' => 'rosenkohl'],
        ['title' => 'Rotkohl', 'alias' => 'rotkohl'],
        ['title' => 'Rotbarsch', 'alias' => 'rotbarsch'],
        ['title' => 'Rote Beete', 'alias' => 'rote-beete'],
        ['title' => 'Rucola', 'alias' => 'rucola'],
        ['title' => 'Salat', 'alias' => 'salat'],
        ['title' => 'Salatgurke', 'alias' => 'salatgurke'],
        ['title' => 'Salz', 'alias' => 'Salz'],
        ['title' => 'Sardelle', 'alias' => 'sardelle'],
        ['title' => 'Sardine', 'alias' => 'sardine'],
        ['title' => 'Sauerampfer', 'alias' => 'sauerampfer'],
        ['title' => 'Sauerkraut', 'alias' => 'sauerkraut'],
        ['title' => 'Schafskäse', 'alias' => 'schafskaese'],
        ['title' => 'Schalotte', 'alias' => 'schalotte'],
        ['title' => 'Schwarzwurzel', 'alias' => 'schwarzwurzel'],
        ['title' => 'Schweinefleisch', 'alias' => 'schweinefleisch'],
        ['title' => 'Seelachs', 'alias' => 'seelachs'],
        ['title' => 'Seeteufel', 'alias' => 'seeteufel'],
        ['title' => 'Sellerie', 'alias' => 'sellerie'],
        ['title' => 'Soja', 'alias' => 'soja'],
        ['title' => 'Spaghetti', 'alias' => 'spaghetti'],
        ['title' => 'Spargel', 'alias' => 'spargel'],
        ['title' => 'Spinat', 'alias' => 'spinat'],
        ['title' => 'Spitzkohl', 'alias' => 'spitzkohl'],
        ['title' => 'Stachelbeeren', 'alias' => 'stachelbeeren'],
        ['title' => 'Steckrüben', 'alias' => 'steckrueben'],
        ['title' => 'Suppengrün', 'alias' => 'suppengruen'],
        ['title' => 'Süßkartoffeln', 'alias' => 'suesskartoffeln'],
        ['title' => 'Thunfisch', 'alias' => 'thunfisch'],
        ['title' => 'Tilapia', 'alias' => 'tilapia'],
        ['title' => 'Tofu', 'alias' => 'tofu'],
        ['title' => 'Tomaten', 'alias' => 'tomaten'],
        ['title' => 'Tortilla', 'alias' => 'tortilla'],
        ['title' => 'Topinambur', 'alias' => 'topinambur'],
        ['title' => 'Vollkorn', 'alias' => 'vollkorn'],
        ['title' => 'Walnüsse', 'alias' => 'walnuesse'],
        ['title' => 'Weintrauben', 'alias' => 'weintrauben'],
        ['title' => 'Weißkohl', 'alias' => 'weisskohl'],
        ['title' => 'Wirsing', 'alias' => 'wirsing'],
        ['title' => 'Zander', 'alias' => 'zander'],
        ['title' => 'Ziegenkäse', 'alias' => 'ziegenkaese'],
        ['title' => 'Zitrone', 'alias' => 'zitrone'],
        ['title' => 'Zucchini', 'alias' => 'zucchini'],
        ['title' => 'Zuckerschote', 'alias' => 'zuckerschote'],
        ['title' => 'Zwiebel', 'alias' => 'zwiebel']
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
