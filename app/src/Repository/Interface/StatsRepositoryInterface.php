<?php

declare(strict_types=1);

namespace App\Repository\Interface;

use App\Model\MostFrequentRequest;
use App\Query\FizzBuzzQuery;

interface StatsRepositoryInterface
{
    public function increment(FizzBuzzQuery $query): void;

    public function findMostFrequent(): ?MostFrequentRequest;
}
