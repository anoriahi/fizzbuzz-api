<?php

declare(strict_types=1);

namespace App\Tests\Integration\Event;

use App\Event\FizzBuzzRequestedEvent;
use App\EventListener\RecordStatsListener;
use App\Query\FizzBuzzQuery;
use App\Repository\Interface\StatsRepositoryInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(RecordStatsListener::class)]
final class FizzBuzzRequestedEventDispatchTest extends KernelTestCase
{
    public function testDispatchingTheEventRecordsStats(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $dispatcher = $container->get(EventDispatcherInterface::class);
        $repository = $container->get(StatsRepositoryInterface::class);
        self::assertInstanceOf(EventDispatcherInterface::class, $dispatcher);
        self::assertInstanceOf(StatsRepositoryInterface::class, $repository);

        $query = new FizzBuzzQuery(3, 5, 15, 'fizz', 'buzz');
        $dispatcher->dispatch(new FizzBuzzRequestedEvent($query));

        $mostFrequent = $repository->findMostFrequent();
        self::assertNotNull($mostFrequent);
        self::assertSame(1, $mostFrequent->getHits());
        self::assertEquals($query, $mostFrequent->getQuery());
    }
}
