<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommunicationInitiativeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('communication_initiative', function (Blueprint $table) {
            $table->id();
            $table->string("title");
            $table->string("slug");
            $table->string("type_file");
            $table->text('desc');
            $table->string("status");
            $table->integer('views')->default(0);
            $table->string("thumbnail")->nullable();
            $table->datetime('approve_at')->nullable();
            $table->datetime('publish_at')->nullable();
            $table->datetime('reject_at')->nullable();
            $table->datetime('deleted_at')->nullable();
            $table->timestamps();

            $table->engine = 'MyISAM';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('communication_initiative');
    }
}
