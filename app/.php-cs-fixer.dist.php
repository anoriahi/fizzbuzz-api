<?php

declare(strict_types=1);

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude(['var', 'vendor'])
    ->notPath([
        'config/bundles.php',
        'config/reference.php',
    ]);

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        '@PSR12' => true,
    ])
    ->setFinder($finder)
;
