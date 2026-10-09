<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

final class ArchitectureRulesTest extends TestCase
{
    /**
     * Regla R-01: grep -R "Illuminate\\" app/Domain debe arrojar 0.
     */
    public function test_domain_layer_has_no_illuminate_dependencies(): void
    {
        $domainDir = realpath(__DIR__ . '/../../app/Domain');
        $this->assertNotFalse($domainDir, 'El directorio app/Domain debe existir.');

        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($domainDir));
        $illuminateUsages = [];

        foreach ($files as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $content = file_get_contents($file->getPathname());
                if (str_contains($content, 'Illuminate\\')) {
                    $illuminateUsages[] = $file->getPathname();
                }
            }
        }

        $this->assertEmpty(
            $illuminateUsages,
            'Se encontraron dependencias de Illuminate en Domain: ' . implode(', ', $illuminateUsages)
        );
    }

    /**
     * Regla R-02: La capa Application no debe conocer infraestructura ni Illuminate.
     */
    public function test_application_layer_has_no_infrastructure_dependencies(): void
    {
        $appDir = realpath(__DIR__ . '/../../app/Application');
        $this->assertNotFalse($appDir, 'El directorio app/Application debe existir.');

        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($appDir));
        $violatingUsages = [];

        foreach ($files as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $content = file_get_contents($file->getPathname());
                if (str_contains($content, 'App\\Infrastructure') || str_contains($content, 'Illuminate\\Support\\Facades\\DB')) {
                    $violatingUsages[] = $file->getPathname();
                }
            }
        }

        $this->assertEmpty(
            $violatingUsages,
            'Se encontraron violaciones arquitectónicas en Application: ' . implode(', ', $violatingUsages)
        );
    }
}

