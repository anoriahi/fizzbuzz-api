<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/config',
        __DIR__.'/migrations',
        __DIR__.'/src',
        __DIR__ . '/tests'
    ])
    ->withSkip([
        __DIR__.'/config/bundles.php',
        __DIR__.'/config/preload.php',
        __DIR__.'/config/reference.php',
    ])
    // Syntaxe moderne jusqu'à la version PHP de composer.json (d'où ">=8.4")
    ->withPhpSets()
    // Règles Symfony, Doctrine et PHPUnit adaptées aux versions installées
    ->withComposerBased(symfony: true, doctrine: true, phpunit: true)
    ->withSymfonyContainerXml(__DIR__.'/var/cache/dev/App_KernelDevDebugContainer.xml')
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        typeDeclarations: true,
        earlyReturn: true,
    )
    ->withImportNames(importShortClasses: false, removeUnusedImports: true);
