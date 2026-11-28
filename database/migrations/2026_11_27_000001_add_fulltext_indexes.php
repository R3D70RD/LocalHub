<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Index FULLTEXT MySQL pour la recherche plein texte (fr/en).
 *
 * SQLite (utilisé par les tests) ne gère pas FULLTEXT : la recherche y est
 * assurée par un repli `LIKE` dans `RechercheService`. La migration est donc
 * volontairement neutre hors MySQL.
 */
return new class extends Migration
{
    /**
     * @var array<int, string>
     */
    private array $tables = [
        'publication',
        'outil',
        'demande_aide',
        'annonce',
        'evenement',
        'rencontre',
    ];

    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        foreach ($this->tables as $table) {
            DB::statement("ALTER TABLE `{$table}` ADD FULLTEXT `{$table}_texte_fulltext` (`titre`, `description`)");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        foreach ($this->tables as $table) {
            DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$table}_texte_fulltext`");
        }
    }
};
