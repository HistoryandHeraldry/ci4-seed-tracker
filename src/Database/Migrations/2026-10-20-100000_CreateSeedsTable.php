<?php

declare(strict_types=1);

namespace HistoryHeraldry\SeedTracker\Database\Migrations;

use CodeIgniter\Database\Migration;
use HistoryHeraldry\SeedTracker\Config\SeedTracker;

final class CreateSeedsTable extends Migration
{
    public function up(): void
    {
        $table = $this->table();

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'version' => ['type' => 'VARCHAR', 'constraint' => 17],
            'name' => ['type' => 'VARCHAR', 'constraint' => 191],
            'class' => ['type' => 'VARCHAR', 'constraint' => 255],
            'checksum' => ['type' => 'CHAR', 'constraint' => 64],
            'batch' => ['type' => 'INT', 'unsigned' => true],
            'execution_time' => ['type' => 'INT', 'unsigned' => true],
            'executed_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('version', 'seeds_version');
        $this->forge->createTable($table, true);
    }

    public function down(): void
    {
        $this->forge->dropTable($this->table(), true);
    }

    private function table(): string
    {
        $applicationConfig = 'Config\\SeedTracker';
        $config = class_exists($applicationConfig) ? new $applicationConfig() : new SeedTracker();

        return $config->table;
    }
}
