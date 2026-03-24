<?php

namespace App\Services;

class HardwareFingerprint
{
    private static ?string $cachedFingerprint = null;

    public function generate(): string
    {
        if (self::$cachedFingerprint !== null) {
            return self::$cachedFingerprint;
        }

        $components = [
            'motherboard' => $this->getMotherboardSerial(),
            'cpu' => $this->getCpuId(),
            'uuid' => $this->getSystemUuid(),
        ];

        // Filter out empty/failed values, need at least 2 components
        $valid = array_filter($components, fn ($v) => ! empty($v) && $v !== 'None');

        if (count($valid) < 2) {
            // Fallback: include BIOS serial if we don't have enough
            $valid['bios'] = $this->getBiosSerial();
            $valid = array_filter($valid, fn ($v) => ! empty($v) && $v !== 'None');
        }

        ksort($valid);
        self::$cachedFingerprint = hash('sha256', json_encode($valid));

        return self::$cachedFingerprint;
    }

    public function verify(string $storedFingerprint): bool
    {
        return hash_equals($storedFingerprint, $this->generate());
    }

    public static function clearCache(): void
    {
        self::$cachedFingerprint = null;
    }

    private function getMotherboardSerial(): string
    {
        return $this->runWmic('baseboard get serialnumber');
    }

    private function getCpuId(): string
    {
        return $this->runWmic('cpu get processorid');
    }

    private function getSystemUuid(): string
    {
        return $this->runWmic('csproduct get uuid');
    }

    private function getBiosSerial(): string
    {
        return $this->runWmic('bios get serialnumber');
    }

    private function runWmic(string $query): string
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            return '';
        }

        try {
            $output = [];
            $code = 0;
            exec("wmic {$query} /value 2>NUL", $output, $code);

            if ($code !== 0) {
                return '';
            }

            foreach ($output as $line) {
                $line = trim($line);
                if (str_contains($line, '=')) {
                    $value = trim(explode('=', $line, 2)[1]);
                    if ($value !== '' && strtolower($value) !== 'none') {
                        return $value;
                    }
                }
            }
        } catch (\Throwable) {
            // Silently fail — we'll use other components
        }

        return '';
    }
}
