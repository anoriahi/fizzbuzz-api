<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Query\FizzBuzzQuery;
use App\Service\FizzBuzzGenerator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(FizzBuzzGenerator::class)]
final class FizzBuzzGeneratorTest extends TestCase
{
    #[DataProvider('provideQueries')]
    public function testGenerate(FizzBuzzQuery $query, string $expected): void
    {
        self::assertSame($expected, (new FizzBuzzGenerator())->generate($query));
    }

    /**
     * @return iterable<string, array{FizzBuzzQuery, string}>
     */
    public static function provideQueries(): iterable
    {
        yield 'classic' => [new FizzBuzzQuery(3, 5, 15, 'fizz', 'buzz'), '1,2,fizz,4,buzz,fizz,7,8,fizz,buzz,11,fizz,13,14,fizzbuzz'];
        yield 'limit = 1' => [new FizzBuzzQuery(1, 1, 1, 'a', 'b'), 'ab'];
        yield 'int1 = 1: every number' => [new FizzBuzzQuery(1, 3, 4, 'a', 'b'), 'a,a,ab,a'];
        yield 'int1 = int2' => [new FizzBuzzQuery(2, 2, 4, 'a', 'b'), '1,ab,3,ab'];
        yield 'int2 = limit' => [new FizzBuzzQuery(3, 5, 5, 'fizz', 'buzz'), '1,2,fizz,4,buzz'];
    }
}
