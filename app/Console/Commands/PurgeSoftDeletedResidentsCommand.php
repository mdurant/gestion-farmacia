<?php

namespace App\Console\Commands;

use App\Models\Resident;
use Illuminate\Console\Command;

class PurgeSoftDeletedResidentsCommand extends Command
{
    protected $signature = 'acalis:purge-residents
        {--days= : Días de retención (default config)}
        {--dry-run : Solo listar, no eliminar}';

    protected $description = 'Purga forzosa de residentes en soft-delete tras el período de retención (Ley 21.719)';

    public function handle(): int
    {
        $days = (int) ($this->option('days') ?: config('acalis.residents.soft_delete_retention_days', 365));
        $cutoff = now()->subDays(max(1, $days));

        $query = Resident::onlyTrashed()->where('deleted_at', '<=', $cutoff);
        $count = $query->count();

        if ($count === 0) {
            $this->info('No hay residentes para purgar.');

            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->warn("Dry-run: {$count} residente(s) serían purgados (deleted_at <= {$cutoff->toDateString()}).");

            return self::SUCCESS;
        }

        $query->forceDelete();
        $this->info("Purgados {$count} residente(s) soft-deleted anteriores a {$cutoff->toDateString()}.");

        return self::SUCCESS;
    }
}
