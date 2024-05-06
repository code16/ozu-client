<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();

            $table->text('title')->nullable();
            $table->unsignedInteger('order')->default(1000);
            $table->text('content')->nullable();
            $table->string('slug')->nullable();
            $table->string('collection_key');
            $table->json('custom_properties')->nullable();

            $table->timestamps();
        });

        Schema::create('medias', function (Blueprint $table) {
            $table->id();
            $table->morphs('model');
            $table->string('model_key')->nullable();
            $table->string('file_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->string('disk')->default('local')->nullable();
            $table->unsignedInteger('size')->nullable();
            $table->text('custom_properties')->nullable();
            $table->unsignedInteger('order')->nullable();
            $table->timestamps();
        });
    }
};
