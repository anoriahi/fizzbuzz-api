<?php

declare(strict_types=1);

namespace App\Service;

use App\Query\FizzBuzzQuery;

final class FizzBuzzGenerator
{
    public function generate(FizzBuzzQuery $query): string
    {
        $result = [];

        for ($i = 1; $i <= $query->getLimit(); ++$i) {
            $value = '';
            if (0 === $i % $query->getInt1()) {
                $value .= $query->getStr1();
            }
            if (0 === $i % $query->getInt2()) {
                $value .= $query->getStr2();
            }

            $result[] = '' !== $value ? $value : (string) $i;
        }

        return implode(',', $result);
    }
}
