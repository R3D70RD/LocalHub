<?php

namespace App\Console\Commands;

use App\Services\LoanService;
use Illuminate\Console\Command;

class MarquerPretsEnRetard extends Command
{
    protected $signature = 'prets:marquer-en-retard';

    protected $description = 'Marque comme « en retard » les prêts dont la date de fin est dépassée.';

    public function handle(LoanService $prets): int
    {
        $nombre = $prets->marquerEnRetard();

        $this->info(sprintf('%d prêt(s) marqué(s) en retard.', $nombre));

        return self::SUCCESS;
    }
}
