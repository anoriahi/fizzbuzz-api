<?php

declare(strict_types=1);

namespace App\Tests\Unit\Event;

use App\Event\FizzBuzzRequestedEvent;
use App\Query\FizzBuzzQuery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FizzBuzzRequestedEvent::class)]
final class FizzBuzzRequestedEventTest extends TestCase
{
    public function testCarriesTheQuery(): void
    {
        $query = new FizzBuzzQuery(3, 5, 15, 'fizz', 'buzz');

        self::assertSame($query, (new FizzBuzzRequestedEvent($query))->getQuery());
    }
}
