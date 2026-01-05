<?php
declare(strict_types=1);

namespace App\Command;

use App\Service\CountryService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'countries:sync',
    description: 'Synchronize countries from REST Countries API'
)]
class CountrySyncCommand extends Command
{
    public function __construct(
        private readonly CountryService $countryService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Synchronizing countries from REST Countries API');

        try {
            $stats = $this->countryService->syncCountries();

            $io->success('Countries synchronized successfully!');
            $io->table(
                ['Action', 'Count'],
                [
                    ['Created', $stats['created']],
                    ['Updated', $stats['updated']],
                    ['Deleted', $stats['deleted']],
                ]
            );

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Failed to synchronize countries: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}