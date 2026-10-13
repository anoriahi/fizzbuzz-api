<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\StatsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StatsRepository::class)]
#[ORM\UniqueConstraint(name: 'uniq_stats_query', columns: ['query'])]
#[ORM\Index(name: 'idx_stats_hits', columns: ['hits'])]
class Stats
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $query;

    #[ORM\Column(options: ['unsigned' => true, 'default' => 1])]
    private int $hits = 1;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function setQuery(string $query): static
    {
        $this->query = $query;

        return $this;
    }

    public function getHits(): int
    {
        return $this->hits;
    }

    public function setHits(int $hits): static
    {
        $this->hits = $hits;

        return $this;
    }
}
