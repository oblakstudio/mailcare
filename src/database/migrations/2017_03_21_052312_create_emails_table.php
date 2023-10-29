<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('emails', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('sender_id');
            $table->uuid('inbox_id');
            $table->string('subject', 100);
            $table->dateTime('read')->nullable();
            $table->boolean('favorite')->default(false);
            $table->boolean('has_html')->default(false);
            $table->boolean('has_text')->default(false);
            $table->integer('size_in_bytes')->nullable();
            $table->timestamps();

            $table->primary('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('emails');
    }
};
