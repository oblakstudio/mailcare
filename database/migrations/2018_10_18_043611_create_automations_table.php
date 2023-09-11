<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAutomationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('automations', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('title');
            $table->string('sender')->nullable();
            $table->string('inbox')->nullable();
            $table->string('subject')->nullable();
            $table->boolean('has_attachments')->default(false);
            $table->string('action_url');
            $table->string('action_secret_token')->nullable();
            $table->integer('emails_received')->default(0);
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
        Schema::dropIfExists('automations');
    }
}
