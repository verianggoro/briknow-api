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
            $table->integer('user_maker');
            $table->integer('is_recommend');
            $table->integer('updated_by')->nullable();
            $table->datetime('approve_at')->nullable();
            $table->integer('approve_by')->nullable();
            $table->datetime('publish_at')->nullable();
            $table->integer('publish_by')->nullable();
            $table->datetime('unpublish_at')->nullable();
            $table->integer('unpublish_by')->nullable();
            $table->datetime('reject_at')->nullable();
            $table->integer('reject_by')->nullable();
            $table->datetime('deleted_at')->nullable();
            $table->integer('deleted_by')->nullable();
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
