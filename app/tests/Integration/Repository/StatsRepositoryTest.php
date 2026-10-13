<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Query\FizzBuzzQuery;
use App\Repository\Interface\StatsRepositoryInterface;
use App\Repository\StatsRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(StatsRepository::class)]
final class StatsRepositoryTest extends KernelTestCase
{
    private StatsRepositoryInterface $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $repository = self::getContainer()->get(StatsRepositoryInterface::class);
        self::assertInstanceOf(StatsRepositoryInterface::class, $repository);
        $this->repository = $repository;
    }

    public function testReturnsNullWhenNoRequestWasRecorded(): void
    {
        self::assertNull($this->repository->findMostFrequent());
    }

    public function testIncrementCountsHits(): void
    {
        $query = new FizzBuzzQuery(3, 5, 15, 'fizz', 'buzz');
        $this->record($query, 3);

        $result = $this->repository->findMostFrequent();

        self::assertNotNull($result);
        self::assertSame(3, $result->getHits());
        self::assertEquals($query, $result->getQuery());
    }

    public function testReturnsTheMostFrequentRequest(): void
    {
        $this->record(new FizzBuzzQuery(3, 5, 15, 'fizz', 'buzz'), 1);
        $this->record($expected = new FizzBuzzQuery(2, 7, 20, 'foo', 'bar'), 2);

        self::assertEquals($expected, $this->repository->findMostFrequent()?->getQuery());
    }

    public function testTieReturnsTheFirstRecordedRequest(): void
    {
        $this->record($first = new FizzBuzzQuery(3, 5, 15, 'fizz', 'buzz'), 2);
        $this->record(new FizzBuzzQuery(2, 7, 20, 'foo', 'bar'), 2);

        self::assertEquals($first, $this->repository->findMostFrequent()?->getQuery());
    }

    // Vérifie la collation utf8mb4_nopad_bin : sinon ces 3 requêtes seraient fusionnées
    public function testStringsAreCaseAndTrailingSpaceSensitive(): void
    {
        $this->record(new FizzBuzzQuery(3, 5, 15, 'fizz', 'buzz'), 2);
        $this->record(new FizzBuzzQuery(3, 5, 15, 'Fizz', 'buzz'), 1);
        $this->record(new FizzBuzzQuery(3, 5, 15, 'fizz ', 'buzz'), 1);

        self::assertSame(2, $this->repository->findMostFrequent()?->getHits());
    }

    private function record(FizzBuzzQuery $query, int $times): void
    {
        for ($i = 0; $i < $times; ++$i) {
            $this->repository->increment($query);
        }
    }
}
