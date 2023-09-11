<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('email_id');
            $table->string('headers_hashed', 32);
            $table->string('file_name', 100);
            $table->string('content_type', 100);
            $table->integer('size_in_bytes');
            $table->timestamps();

            $table->primary('id');
            $table->unique(['email_id', 'headers_hashed']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attachments');
    }
}
