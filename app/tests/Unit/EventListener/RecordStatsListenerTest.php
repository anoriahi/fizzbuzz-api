<?php

declare(strict_types=1);

namespace App\Tests\Unit\EventListener;

use App\Event\FizzBuzzRequestedEvent;
use App\EventListener\RecordStatsListener;
use App\Query\FizzBuzzQuery;
use App\Repository\Interface\StatsRepositoryInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

#[CoversClass(RecordStatsListener::class)]
final class RecordStatsListenerTest extends TestCase
{
    public function testRecordsTheRequestedQuery(): void
    {
        $query = new FizzBuzzQuery(3, 5, 15, 'fizz', 'buzz');

        $repository = $this->createMock(StatsRepositoryInterface::class);
        $repository->expects(self::once())->method('increment')->with(self::identicalTo($query));

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('error');

        (new RecordStatsListener($repository, $logger))(new FizzBuzzRequestedEvent($query));
    }

    public function testFailureIsLoggedAndNotRethrown(): void
    {
        $exception = new \RuntimeException('Database unavailable');

        $repository = $this->createStub(StatsRepositoryInterface::class);
        $repository->method('increment')->willThrowException($exception);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())
            ->method('error')
            ->with('Unable to record FizzBuzz statistics.', ['exception' => $exception]);

        // Aucune exception ne doit remonter : la réponse FizzBuzz ne dépend pas des stats
        (new RecordStatsListener($repository, $logger))(
            new FizzBuzzRequestedEvent(new FizzBuzzQuery(3, 5, 15, 'fizz', 'buzz')),
        );
    }
}
