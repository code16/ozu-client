<?php

namespace Code16\OzuClient\Support\Database;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

trait MigratesOzuTable
{
    protected function createOzuTable(string $table): void
    {
        Schema::create($table, function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->text('title')->nullable();
            $blueprint->text('content')->nullable();
            $blueprint->string('slug')->nullable();
            $blueprint->unsignedInteger('order')->default(1000);
            $blueprint->timestamps();
        });
    }
}
