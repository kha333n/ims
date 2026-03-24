<?php

namespace App\Console\Commands;

use App\Services\IntegrityChecker;
use Illuminate\Console\Command;

class IntegrityGenerate extends Command
{
    protected $signature = 'integrity:generate';

    protected $description = 'Generate file integrity manifest for anti-tampering protection';

    public function handle(IntegrityChecker $checker): int
    {
        $path = $checker->generateManifest();

        $this->info("Integrity manifest generated: {$path}");

        return self::SUCCESS;
    }
}
