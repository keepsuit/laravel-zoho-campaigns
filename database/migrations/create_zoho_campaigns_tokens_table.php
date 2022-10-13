<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('zoho_campaigns_tokens', function (Blueprint $table) {
            $table->id();
            $table->text('token');
            $table->string('token_type')->index();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }
};
