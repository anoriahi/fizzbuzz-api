<?php

declare(strict_types=1);

namespace App\Model;

use App\Query\FizzBuzzQuery;

final readonly class MostFrequentRequest
{
    private FizzBuzzQuery $query;

    public function __construct(string $queryStr, private int $hits)
    {
        $this->query = FizzBuzzQuery::fromStatsKey($queryStr);
    }

    public function getQuery(): FizzBuzzQuery
    {
        return $this->query;
    }

    public function getHits(): int
    {
        return $this->hits;
    }
}
