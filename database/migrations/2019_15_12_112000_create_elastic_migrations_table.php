<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateElasticMigrationsTable extends Migration
{
    public function up()
    {
        Schema::create('elastic_migrations', function (Blueprint $table) {
            $table->string('migration');
            $table->integer('batch');
        });
    }

    public function down()
    {
        Schema::dropIfExists('elastic_migrations');
    }
}
