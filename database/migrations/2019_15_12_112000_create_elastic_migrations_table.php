<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateElasticMigrationsTable extends Migration
{
    private string $table;

    public function __construct()
    {
        $this->table = config('elastic.migrations.database.table');
    }

    public function up(): void
    {
        Schema::create($this->table, static function (Blueprint $table) {
            $table->string('migration')->primary();
            $table->integer('batch');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->table);
    }
}
