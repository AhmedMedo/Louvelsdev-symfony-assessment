<?php
declare(strict_types=1);

namespace App\Controller\V1;

use App\Entity\Country;
use App\Entity\Currency;
use App\Repository\CountryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/countries')]
#[OA\Tag(name: 'Countries')]
class CountryController extends AbstractController
{
    public function __construct(
        private readonly CountryRepository $countryRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/list', name: 'countries_list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/v1/countries/list',
        summary: 'Get all countries',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns the list of all countries',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: Country::class))
                )
            )
        ]
    )]
    public function getCountries(): JsonResponse
    {
        $countries = $this->countryRepository->findAll();

        return $this->json($countries, Response::HTTP_OK);
    }

    #[Route('/{uuid}', name: 'country_get', methods: ['GET'])]
    #[OA\Get(
        path: '/api/v1/countries/{uuid}',
        summary: 'Get a single country by UUID',
        parameters: [
            new OA\Parameter(
                name: 'uuid',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns the country',
                content: new OA\JsonContent(ref: new Model(type: Country::class))
            ),
            new OA\Response(response: 404, description: 'Country not found')
        ]
    )]
    public function getCountry(string $uuid): JsonResponse
    {
        $country = $this->countryRepository->findByUuid($uuid);

        if (!$country) {
            return $this->json(['error' => 'Country not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($country, Response::HTTP_OK);
    }

    #[Route('/', name: 'country_create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/v1/countries/',
        summary: 'Create a new country',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['uuid', 'name'],
                properties: [
                    new OA\Property(property: 'uuid', type: 'string', example: 'TST'),
                    new OA\Property(property: 'name', type: 'string', example: 'Test Country'),
                    new OA\Property(property: 'region', type: 'string', example: 'Europe'),
                    new OA\Property(property: 'subRegion', type: 'string', example: 'Western Europe'),
                    new OA\Property(property: 'demonym', type: 'string', example: 'Testian'),
                    new OA\Property(property: 'population', type: 'integer', example: 1000000),
                    new OA\Property(property: 'independent', type: 'boolean', example: true),
                    new OA\Property(property: 'flag', type: 'string', example: 'https://example.com/flag.png'),
                    new OA\Property(
                        property: 'currency',
                        properties: [
                            new OA\Property(property: 'name', type: 'string', example: 'Test Dollar'),
                            new OA\Property(property: 'symbol', type: 'string', example: 'T$')
                        ],
                        type: 'object'
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Country created successfully',
                content: new OA\JsonContent(ref: new Model(type: Country::class))
            ),
            new OA\Response(response: 400, description: 'Invalid input'),
            new OA\Response(response: 409, description: 'Country with this UUID already exists')
        ]
    )]
    public function addCountry(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['uuid']) || !isset($data['name'])) {
            return $this->json(['error' => 'UUID and name are required'], Response::HTTP_BAD_REQUEST);
        }

        // Check if country with UUID already exists
        if ($this->countryRepository->findByUuid($data['uuid'])) {
            return $this->json(['error' => 'Country with this UUID already exists'], Response::HTTP_CONFLICT);
        }

        $country = new Country();
        $country->setUuid($data['uuid']);
        $country->setName($data['name']);
        $country->setRegion($data['region'] ?? null);
        $country->setSubRegion($data['subRegion'] ?? null);
        $country->setDemonym($data['demonym'] ?? null);
        $country->setPopulation($data['population'] ?? null);
        $country->setIndependent($data['independent'] ?? null);
        $country->setFlag($data['flag'] ?? null);

        // Handle currency
        if (isset($data['currency'])) {
            $currency = new Currency();
            $currency->setName($data['currency']['name'] ?? null);
            $currency->setSymbol($data['currency']['symbol'] ?? null);
            $country->setCurrency($currency);
        }

        $this->countryRepository->save($country, true);

        return $this->json($country, Response::HTTP_CREATED);
    }

    #[Route('/{uuid}', name: 'country_update', methods: ['PATCH'])]
    #[OA\Patch(
        path: '/api/v1/countries/{uuid}',
        summary: 'Update an existing country',
        parameters: [
            new OA\Parameter(
                name: 'uuid',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string')
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'region', type: 'string'),
                    new OA\Property(property: 'subRegion', type: 'string'),
                    new OA\Property(property: 'demonym', type: 'string'),
                    new OA\Property(property: 'population', type: 'integer'),
                    new OA\Property(property: 'independent', type: 'boolean'),
                    new OA\Property(property: 'flag', type: 'string'),
                    new OA\Property(
                        property: 'currency',
                        properties: [
                            new OA\Property(property: 'name', type: 'string'),
                            new OA\Property(property: 'symbol', type: 'string')
                        ],
                        type: 'object'
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Country updated successfully',
                content: new OA\JsonContent(ref: new Model(type: Country::class))
            ),
            new OA\Response(response: 404, description: 'Country not found')
        ]
    )]
    public function updateCountry(string $uuid, Request $request): JsonResponse
    {
        $country = $this->countryRepository->findByUuid($uuid);

        if (!$country) {
            return $this->json(['error' => 'Country not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['name'])) {
            $country->setName($data['name']);
        }
        if (isset($data['region'])) {
            $country->setRegion($data['region']);
        }
        if (isset($data['subRegion'])) {
            $country->setSubRegion($data['subRegion']);
        }
        if (isset($data['demonym'])) {
            $country->setDemonym($data['demonym']);
        }
        if (isset($data['population'])) {
            $country->setPopulation($data['population']);
        }
        if (isset($data['independent'])) {
            $country->setIndependent($data['independent']);
        }
        if (isset($data['flag'])) {
            $country->setFlag($data['flag']);
        }

        // Handle currency update
        if (isset($data['currency'])) {
            $currency = $country->getCurrency();
            if (isset($data['currency']['name'])) {
                $currency->setName($data['currency']['name']);
            }
            if (isset($data['currency']['symbol'])) {
                $currency->setSymbol($data['currency']['symbol']);
            }
        }

        $this->entityManager->flush();

        return $this->json($country, Response::HTTP_OK);
    }

    #[Route('/{uuid}', name: 'country_delete', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/v1/countries/{uuid}',
        summary: 'Delete a country',
        parameters: [
            new OA\Parameter(
                name: 'uuid',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string')
            )
        ],
        responses: [
            new OA\Response(response: 204, description: 'Country deleted successfully'),
            new OA\Response(response: 404, description: 'Country not found')
        ]
    )]
    public function deleteCountry(string $uuid): JsonResponse
    {
        $country = $this->countryRepository->findByUuid($uuid);

        if (!$country) {
            return $this->json(['error' => 'Country not found'], Response::HTTP_NOT_FOUND);
        }

        $this->countryRepository->remove($country, true);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}