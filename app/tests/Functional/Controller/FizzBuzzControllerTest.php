<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Controller\FizzBuzzController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[CoversClass(FizzBuzzController::class)]
final class FizzBuzzControllerTest extends WebTestCase
{
    private const GENERATE = '/api/fizzbuzz/generate';
    private const STATS = '/api/fizzbuzz/stats';
    private const VALID_QUERY = 'int1=3&int2=5&limit=15&str1=fizz&str2=buzz';

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();
    }

    public function testGenerate(): void
    {
        $this->client->request('GET', self::GENERATE.'?'.self::VALID_QUERY);

        self::assertResponseIsSuccessful();
        self::assertJsonStringEqualsJsonString(
            '{"data":"1,2,fizz,4,buzz,fizz,7,8,fizz,buzz,11,fizz,13,14,fizzbuzz"}',
            $this->content(),
        );
    }

    #[DataProvider('provideInvalidQueries')]
    public function testInvalidQueryReturnsValidationProblem(string $queryString, string $field): void
    {
        $this->client->request('GET', self::GENERATE.'?'.$queryString);

        self::assertResponseStatusCodeSame(400);
        self::assertResponseHeaderSame('Content-Type', 'application/problem+json');
        $problem = $this->json();
        self::assertIsArray($problem['violations'] ?? null);
        self::assertContains($field, array_column($problem['violations'], 'field'));
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function provideInvalidQueries(): iterable
    {
        yield 'int1 = 0' => ['int1=0&int2=5&limit=15&str1=fizz&str2=buzz', 'int1'];
        yield 'int1 > limit' => ['int1=20&int2=5&limit=15&str1=fizz&str2=buzz', 'int1'];
        yield 'int2 not an integer' => ['int1=3&int2=abc&limit=15&str1=fizz&str2=buzz', 'int2'];
        yield 'limit > 100' => ['int1=3&int2=5&limit=101&str1=fizz&str2=buzz', 'limit'];
        yield 'str1 with comma' => ['int1=3&int2=5&limit=15&str1=fi%2Czz&str2=buzz', 'str1'];
        yield 'str2 blank' => ['int1=3&int2=5&limit=15&str1=fizz&str2=%20%20', 'str2'];
        yield 'str2 missing' => ['int1=3&int2=5&limit=15&str1=fizz', 'str2'];
    }

    public function testUnknownParameterIsRejected(): void
    {
        $this->client->request('GET', self::GENERATE.'?'.self::VALID_QUERY.'&foo=bar');

        self::assertResponseStatusCodeSame(400);
        self::assertResponseHeaderSame('Content-Type', 'application/problem+json');
        self::assertSame(['foo'], $this->json()['unknownParameters'] ?? null);
    }

    public function testEmptyQueryStringIsRejected(): void
    {
        $this->client->request('GET', self::GENERATE);

        self::assertResponseStatusCodeSame(400);
    }

    public function testPostIsNotAllowed(): void
    {
        $this->client->request('POST', self::GENERATE.'?'.self::VALID_QUERY);

        self::assertResponseStatusCodeSame(405);
        self::assertResponseHeaderSame('Allow', 'GET');
    }

    public function testStatsAreEmptyWithoutRequest(): void
    {
        $this->client->request('GET', self::STATS);

        self::assertResponseIsSuccessful();
        self::assertJsonStringEqualsJsonString('{"data":null}', $this->content());
    }

    public function testStatsReturnTheMostFrequentRequest(): void
    {
        $this->client->request('GET', self::GENERATE.'?'.self::VALID_QUERY);
        $this->client->request('GET', self::GENERATE.'?'.self::VALID_QUERY);
        $this->client->request('GET', self::GENERATE.'?int1=2&int2=7&limit=20&str1=foo&str2=bar');
        // Une requête invalide ne doit pas être comptée
        $this->client->request('GET', self::GENERATE.'?int1=0&int2=7&limit=20&str1=foo&str2=bar');

        $this->client->request('GET', self::STATS);

        self::assertResponseIsSuccessful();
        self::assertJsonStringEqualsJsonString(
            '{"data":{"query":{"int1":3,"int2":5,"limit":15,"str1":"fizz","str2":"buzz"},"hits":2}}',
            $this->content(),
        );
    }

    private function content(): string
    {
        return (string) $this->client->getResponse()->getContent();
    }

    /**
     * @return array<string, mixed>
     */
    private function json(): array
    {
        $data = json_decode($this->content(), true, 512, \JSON_THROW_ON_ERROR);
        self::assertIsArray($data);

        return $data;
    }
}
