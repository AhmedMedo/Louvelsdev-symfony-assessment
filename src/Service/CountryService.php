<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Country;
use App\Entity\Currency;
use App\Repository\CountryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CountryService
{
    private const REST_COUNTRIES_API = 'https://restcountries.com/v3.1/all?fields=cca3,name,region,subregion,demonyms,population,independent,flags,currencies';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly CountryRepository $countryRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Fetch countries from REST Countries API and sync to database
     */
    public function syncCountries(): array
    {
        $stats = [
            'created' => 0,
            'updated' => 0,
            'deleted' => 0,
        ];

        // Fetch data from API
        $response = $this->httpClient->request('GET', self::REST_COUNTRIES_API);
        $apiCountries = $response->toArray();

        // Get existing countries from database
        $existingCountries = $this->countryRepository->findAll();
        $existingUuids = array_map(fn(Country $c) => $c->getUuid(), $existingCountries);
        $apiUuids = [];

        // Process each country from API
        foreach ($apiCountries as $apiCountry) {
            $uuid = $apiCountry['cca3'] ?? null; // Using cca3 as unique identifier

            if (!$uuid) {
                continue;
            }

            $apiUuids[] = $uuid;

            // Check if country exists
            $country = $this->countryRepository->findByUuid($uuid);

            if ($country === null) {
                // Create new country
                $country = new Country();
                $country->setUuid($uuid);
                $stats['created']++;
            } else {
                // Update existing country
                $stats['updated']++;
            }

            // Map API data to entity
            $this->mapApiDataToEntity($apiCountry, $country);

            $this->countryRepository->save($country);
        }

        // Delete countries that no longer exist in API
        $uuidsToDelete = array_diff($existingUuids, $apiUuids);
        foreach ($uuidsToDelete as $uuid) {
            $country = $this->countryRepository->findByUuid($uuid);
            if ($country) {
                $this->countryRepository->remove($country);
                $stats['deleted']++;
            }
        }

        // Flush all changes
        $this->entityManager->flush();

        return $stats;
    }

    /**
     * Map API data to Country entity
     */
    private function mapApiDataToEntity(array $apiData, Country $country): void
    {
        // Set basic fields
        $country->setName($apiData['name']['common'] ?? 'Unknown');
        $country->setRegion($apiData['region'] ?? null);
        $country->setSubRegion($apiData['subregion'] ?? null);
        $country->setDemonym($apiData['demonyms']['eng']['m'] ?? null);
        $country->setPopulation($apiData['population'] ?? null);
        $country->setIndependent($apiData['independent'] ?? null);
        $country->setFlag($apiData['flags']['png'] ?? $apiData['flags']['svg'] ?? null);

        // Set currency (get first currency if multiple exist)
        $currency = new Currency();
        if (isset($apiData['currencies']) && is_array($apiData['currencies'])) {
            $firstCurrency = reset($apiData['currencies']);
            if ($firstCurrency) {
                $currency->setName($firstCurrency['name'] ?? null);
                $currency->setSymbol($firstCurrency['symbol'] ?? null);
            }
        }
        $country->setCurrency($currency);
    }
}
