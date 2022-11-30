<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImporterLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('importer_log', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->dateTime('run_at');
            $table->integer('entries_processed');
            $table->integer('entries_created');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('importer_log', function (Blueprint $table) {
            $table->drop();
        });
    }
}
