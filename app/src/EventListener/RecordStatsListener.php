<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Event\FizzBuzzRequestedEvent;
use App\Repository\Interface\StatsRepositoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
final readonly class RecordStatsListener
{
    public function __construct(
        private StatsRepositoryInterface $statsRepository,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(FizzBuzzRequestedEvent $event): void
    {
        try {
            $this->statsRepository->increment($event->getQuery());
        } catch (\Throwable $e) {
            $this->logger->error('Unable to record FizzBuzz statistics.', ['exception' => $e]);
        }
    }
}
