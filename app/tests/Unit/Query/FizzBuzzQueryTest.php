<?php

declare(strict_types=1);

namespace App\Tests\Unit\Query;

use App\Query\FizzBuzzQuery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[CoversClass(FizzBuzzQuery::class)]
final class FizzBuzzQueryTest extends TestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();
    }

    public function testStatsKeyRoundTrip(): void
    {
        $query = new FizzBuzzQuery(3, 5, 15, 'Fizz', 'buzz ');

        self::assertSame('3,5,15,Fizz,buzz ', $query->toStatsKey());
        self::assertEquals($query, FizzBuzzQuery::fromStatsKey($query->toStatsKey()));
    }

    public function testValidQueryHasNoViolation(): void
    {
        self::assertCount(0, $this->validator->validate(new FizzBuzzQuery(3, 5, 15, 'fizz', 'buzz')));
    }

    #[DataProvider('provideInvalidQueries')]
    public function testInvalidQuery(FizzBuzzQuery $query, string $expectedField): void
    {
        $violations = $this->validator->validate($query);

        self::assertGreaterThan(0, \count($violations));
        self::assertSame($expectedField, $violations[0]?->getPropertyPath());
    }

    /**
     * @return iterable<string, array{FizzBuzzQuery, string}>
     */
    public static function provideInvalidQueries(): iterable
    {
        yield 'int1 = 0' => [new FizzBuzzQuery(0, 5, 15, 'fizz', 'buzz'), 'int1'];
        yield 'int1 > limit' => [new FizzBuzzQuery(20, 5, 15, 'fizz', 'buzz'), 'int1'];
        yield 'int2 > limit' => [new FizzBuzzQuery(3, 20, 15, 'fizz', 'buzz'), 'int2'];
        yield 'limit > 100' => [new FizzBuzzQuery(3, 5, 101, 'fizz', 'buzz'), 'limit'];
        yield 'str1 empty' => [new FizzBuzzQuery(3, 5, 15, '', 'buzz'), 'str1'];
        yield 'str1 blank' => [new FizzBuzzQuery(3, 5, 15, '   ', 'buzz'), 'str1'];
        yield 'str1 with comma' => [new FizzBuzzQuery(3, 5, 15, 'fi,zz', 'buzz'), 'str1'];
        yield 'str2 too long' => [new FizzBuzzQuery(3, 5, 15, 'fizz', str_repeat('b', 51)), 'str2'];
    }
}
