<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->codigos() as $codigoAnterior => $codigoCorrecto) {
            if (! DB::table('indicadores_maestros')->where('codigo', $codigoCorrecto)->exists()) {
                DB::table('indicadores_maestros')
                    ->where('codigo', $codigoAnterior)
                    ->update(['codigo' => $codigoCorrecto]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->codigos() as $codigoAnterior => $codigoCorrecto) {
            if (! DB::table('indicadores_maestros')->where('codigo', $codigoAnterior)->exists()) {
                DB::table('indicadores_maestros')
                    ->where('codigo', $codigoCorrecto)
                    ->update(['codigo' => $codigoAnterior]);
            }
        }
    }

    /** @return array<string, string> */
    private function codigos(): array
    {
        return [
            'M01.03.02.02-PG-I1' => 'M01.03.02.02/PG-I1',
            'M01.03.02.02-PG-I2' => 'M01.03.02.02/PG-I2',
            'M01.04-PG-I2' => 'M01.04/PG-I2',
            'M01.04-PG-I3' => 'M01.04/PG-I3',
            'M01.04-PG-I5' => 'M01.04/PG-I5',
        ];
    }
};
