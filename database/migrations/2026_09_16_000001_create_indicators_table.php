<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('indicators', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 160);
            $table->string('area', 100);
            $table->text('objective');
            $table->string('unit', 30);
            $table->decimal('target_value', 12, 2);
            $table->decimal('current_value', 12, 2)->default(0);
            $table->string('frequency', 30);
            $table->string('responsible', 120);
            $table->string('period', 30);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('indicators');
    }
};
