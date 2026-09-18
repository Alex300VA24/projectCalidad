<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->string('document_type', 20)->default('institucional')->after('title')->index();
            $table->foreignId('periodo_academico_id')->nullable()->after('document_type')
                ->constrained('periodos_academicos')->nullOnDelete();
            $table->string('section', 100)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('periodo_academico_id');
            $table->dropColumn('document_type');
            $table->string('section', 100)->nullable(false)->change();
        });
    }
};
