<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateElasticMigrationsTable extends Migration
{
    /**
     * @var string
     */
    private $table;

    public function __construct()
    {
        $this->table = config('elastic.migrations.table');
    }

    public function up()
    {
        Schema::create($this->table, function (Blueprint $table) {
            $table->string('migration');
            $table->integer('batch');
        });
    }

    public function down()
    {
        Schema::dropIfExists($this->table);
    }
}
