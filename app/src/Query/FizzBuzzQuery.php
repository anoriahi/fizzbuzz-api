<?php

declare(strict_types=1);

namespace App\Query;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class FizzBuzzQuery
{
    public function __construct(
        #[Assert\GreaterThanOrEqual(1)]
        #[Assert\LessThanOrEqual(propertyPath: 'limit', message: 'This value should be less than or equal to limit ({{ compared_value }}).')]
        private int $int1,
        #[Assert\GreaterThanOrEqual(1)]
        #[Assert\LessThanOrEqual(propertyPath: 'limit', message: 'This value should be less than or equal to limit ({{ compared_value }}).')]
        private int $int2,
        #[Assert\GreaterThanOrEqual(1)]
        #[Assert\LessThanOrEqual(100)]
        private int $limit,
        #[Assert\NotBlank(normalizer: 'trim')]
        #[Assert\Length(min: 1, max: 50)]
        #[Assert\Regex(pattern: '/^[^,]+$/', message: 'This value must not contain a comma.')]
        private string $str1,
        #[Assert\NotBlank(normalizer: 'trim')]
        #[Assert\Length(min: 1, max: 50)]
        #[Assert\Regex(pattern: '/^[^,]+$/', message: 'This value must not contain a comma.')]
        private string $str2,
    ) {
    }

    public function getInt1(): int
    {
        return $this->int1;
    }

    public function getInt2(): int
    {
        return $this->int2;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getStr1(): string
    {
        return $this->str1;
    }

    public function getStr2(): string
    {
        return $this->str2;
    }

    public function toStatsKey(): string
    {
        return "{$this->int1},{$this->int2},{$this->limit},{$this->str1},{$this->str2}";
    }

    public static function fromStatsKey(string $queryStr): self
    {
        [$int1, $int2, $limit, $str1, $str2] = explode(',', $queryStr, 5);

        return new self((int) $int1, (int) $int2, (int) $limit, $str1, $str2);
    }
}
