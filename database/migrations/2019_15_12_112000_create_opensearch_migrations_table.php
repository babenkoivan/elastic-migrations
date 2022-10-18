<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOpenSearchMigrationsTable extends Migration
{
    private string $table;

    public function __construct()
    {
        $this->table = config('opensearch.migrations.database.table');
    }

    public function up(): void
    {
        Schema::create($this->table, static function (Blueprint $table) {
            $table->string('migration');
            $table->integer('batch');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->table);
    }
}
