<?php

declare(strict_types=1);

namespace App\Event;

use App\Query\FizzBuzzQuery;
use Symfony\Contracts\EventDispatcher\Event;

final class FizzBuzzRequestedEvent extends Event
{
    public function __construct(
        private readonly FizzBuzzQuery $query,
    ) {
    }

    public function getQuery(): FizzBuzzQuery
    {
        return $this->query;
    }
}
