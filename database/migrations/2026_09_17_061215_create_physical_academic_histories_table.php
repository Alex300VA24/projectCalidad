<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('physical_academic_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procedure_id')->nullable()->constrained('procedures')->nullOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedSmallInteger('entry_year');
            $table->json('source_acts_references')->nullable();
            $table->json('physical_history_data')->nullable();
            $table->timestamp('certificate_printed_at')->nullable();
            $table->string('status')->default('elaborado');
            $table->timestamps();
            $table->softDeletes();
        });

        if (in_array(DB::getDriverName(), ['mysql', 'pgsql'], true)) {
            DB::statement('ALTER TABLE physical_academic_histories ADD CONSTRAINT chk_entry_year_pre_2007 CHECK (entry_year <= 2007)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('physical_academic_histories');
    }
};
