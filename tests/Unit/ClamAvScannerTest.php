<?php

declare(strict_types=1);

namespace JohnRivera7\FilamentAntivirus\Tests\Unit;

use JohnRivera7\FilamentAntivirus\FilamentAntivirusServiceProvider;
use JohnRivera7\FilamentAntivirus\Scanners\ClamAvScanner;
use Orchestra\Testbench\TestCase;

final class ClamAvScannerTest extends TestCase
{
    /** @var list<string> */
    private array $temporales = [];

    protected function getPackageProviders($app): array
    {
        return [FilamentAntivirusServiceProvider::class];
    }

    protected function tearDown(): void
    {
        foreach ($this->temporales as $ruta) {
            @unlink($ruta);
        }

        parent::tearDown();
    }

    public function test_clamdscan_envia_el_contenido_en_vez_de_pasar_el_descriptor(): void
    {
        $registro = $this->temporal('args');
        $binario = $this->temporal('clam');
        file_put_contents($binario, "#!/bin/sh\nif [ \"\$1\" = \"--version\" ]; then echo 'ClamAV 1.4.3/27800'; exit 0; fi\nprintf '%s\n' \"\$@\" > ".escapeshellarg($registro)."\necho \"\$2: OK\"\n");
        chmod($binario, 0755);
        $archivo = $this->temporal('pdf');
        file_put_contents($archivo, '%PDF-1.4');

        config([
            'filament-antivirus.clamav.prefer_clamdscan' => true,
            'filament-antivirus.clamav.clamdscan_binary' => $binario,
            'filament-antivirus.clamav.timeout' => 10,
        ]);

        $resultado = (new ClamAvScanner)->scanFile($archivo);

        $this->assertTrue($resultado->clean);
        $this->assertFalse($resultado->skipped);
        $this->assertSame("--stream\n".$archivo, trim((string) file_get_contents($registro)));
    }

    private function temporal(string $prefijo): string
    {
        $ruta = tempnam(sys_get_temp_dir(), $prefijo);
        $this->temporales[] = $ruta;

        return $ruta;
    }
}
