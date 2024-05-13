<?php

namespace Code16\OzuClient\Support\Database;

use Illuminate\Support\Facades\Schema;

trait MigratesOzuTable
{
    protected function createOzuTable(string $table): void
    {
        Schema::create($table, function ($table) {
            $table->id();
            $table->text('title')->nullable();
            $table->text('content')->nullable();
            $table->string('slug')->nullable();
            $table->unsignedInteger('order')->default(1000);
            $table->timestamps();
        });
    }
}
