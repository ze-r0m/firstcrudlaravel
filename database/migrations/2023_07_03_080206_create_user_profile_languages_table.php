<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_profile_language', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_profile_id');
            $table->integer('language_id')->index();
            $table->timestamps();
            $table->foreign('user_profile_id')->references('id')->on('user_profiles')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_profile_language');
    }
};
