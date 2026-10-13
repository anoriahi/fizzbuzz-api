<?php

declare(strict_types=1);

namespace App\Controller;

use App\Event\FizzBuzzRequestedEvent;
use App\Query\FizzBuzzQuery;
use App\Repository\Interface\StatsRepositoryInterface;
use App\Service\FizzBuzzGenerator;
use OpenApi\Attributes as OA;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/fizzbuzz/')]
final class FizzBuzzController extends AbstractController
{
    #[Route('generate', name: 'api.fizzbuzz.generate', methods: ['GET'])]
    #[OA\Get(summary: 'FizzBuzz Generator API')]
    #[OA\QueryParameter(name: 'int1', description: 'Multiples of this integer are replaced by str1. Must be less than or equal to limit.', required: true, schema: new OA\Schema(type: 'integer', minimum: 1), example: 3)]
    #[OA\QueryParameter(name: 'int2', description: 'Multiples of this integer are replaced by str2. Must be less than or equal to limit.', required: true, schema: new OA\Schema(type: 'integer', minimum: 1), example: 5)]
    #[OA\QueryParameter(name: 'limit', description: 'Numbers from 1 to limit.', required: true, schema: new OA\Schema(type: 'integer', maximum: 100, minimum: 1), example: 50)]
    #[OA\QueryParameter(name: 'str1', description: 'Replacement for multiples of int1.', required: true, schema: new OA\Schema(type: 'string', maxLength: 50), example: 'fizz')]
    #[OA\QueryParameter(name: 'str2', description: 'Replacement for multiples of int2.', required: true, schema: new OA\Schema(type: 'string', maxLength: 50), example: 'buzz')]
    #[OA\Response(
        response: 200,
        description: 'FizzBuzz sequence',
        content: new OA\JsonContent(properties: [
            new OA\Property(property: 'data', type: 'string', example: '1,2,fizz,4,buzz'),
        ])
    )]
    #[OA\Response(
        response: 400,
        description: 'Invalid or missing parameters'
    )]
    #[OA\Tag(name: 'FizzBuzz')]
    public function index(
        #[MapQueryString(
            serializationContext: [AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => false],
            validationFailedStatusCode: Response::HTTP_BAD_REQUEST
        )] FizzBuzzQuery $query,
        FizzBuzzGenerator $fizzBuzzGenerator,
        EventDispatcherInterface $dispatcher,
    ): JsonResponse {
        $data = $fizzBuzzGenerator->generate($query);

        $dispatcher->dispatch(new FizzBuzzRequestedEvent($query));

        return $this->json(['data' => $data]);
    }

    #[Route('stats', name: 'api.fizzbuzz.stats', methods: ['GET'])]
    #[OA\Get(description: 'Returns the most frequent FizzBuzz request and its number of hits.', summary: 'FizzBuzz Stats API')]
    #[OA\Response(
        response: 200,
        description: 'Most frequent request, or null if no request has been made yet',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'data',
                    properties: [
                        new OA\Property(
                            property: 'query',
                            description: 'Parameters of the most frequent request',
                            properties: [
                                new OA\Property(property: 'int1', type: 'integer', example: 3),
                                new OA\Property(property: 'int2', type: 'integer', example: 5),
                                new OA\Property(property: 'limit', type: 'integer', example: 15),
                                new OA\Property(property: 'str1', type: 'string', example: 'fizz'),
                                new OA\Property(property: 'str2', type: 'string', example: 'buzz'),
                            ],
                            type: 'object',
                        ),
                        new OA\Property(property: 'hits', description: 'Number of times this request was made', type: 'integer', example: 42),
                    ],
                    type: 'object',
                    nullable: true,
                ),
            ],
            examples: [
                new OA\Examples(
                    example: 'found',
                    summary: 'Most frequent request',
                    value: ['data' => ['query' => ['int1' => 3, 'int2' => 5, 'limit' => 15, 'str1' => 'fizz', 'str2' => 'buzz'], 'hits' => 42]],
                ),
                new OA\Examples(
                    example: 'empty',
                    summary: 'No request made yet',
                    value: ['data' => null],
                ),
            ],
        ),
    )]
    #[OA\Tag(name: 'FizzBuzz')]
    public function stats(StatsRepositoryInterface $statsRepository): JsonResponse
    {
        return $this->json([
            'data' => $statsRepository->findMostFrequent(),
        ]);
    }
}
