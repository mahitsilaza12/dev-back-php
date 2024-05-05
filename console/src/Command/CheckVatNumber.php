<?php

namespace App\Command;

use App\Vat\VatValidatorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Predis\Client;
use Psr\Log\LoggerInterface;

class CheckVatNumber extends Command
{
    /**
     * @var VatValidatorInterface
     */
    private $vatValidator;

    private $redis;

    private $logger;

    /**
     * @param VatValidatorInterface $vatValidator
     */
    public function __construct(VatValidatorInterface $vatValidator, Client $redis, LoggerInterface $logger)
    {
        $this->vatValidator = $vatValidator;
        $this->redis = $redis;
        $this->logger = $logger;
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('check-vat-number');

        $this->addArgument('vatNumber', InputArgument::REQUIRED);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $vatNumber = $input->getArgument('vatNumber');
    
            if ($this->redis->exists($vatNumber)) {
                $output->writeln("Vat number $vatNumber is valid (cached)!");
                $this->logger->info(sprintf('Date: %s - VAT Number: %s - Result: valid (from cache)', date('Y-m-d H:i:s'), $vatNumber));
                return Command::SUCCESS;
            }
    
            $isValid = $this->vatValidator->validate($vatNumber);
    
            if ($isValid) {
                $this->logger->info(sprintf('Date: %s - VAT Number: %s - Result: valid (from webservice)', date('Y-m-d H:i:s'), $vatNumber));
                $output->writeln("Vat number $vatNumber is valid!");
                $this->redis->setex($vatNumber, 3600, true);
                return Command::SUCCESS;
            } else {
                $this->logger->info(sprintf('Date: %s - VAT Number: %s - Result: invalid (from webservice)', date('Y-m-d H:i:s'), $vatNumber));
                $output->writeln("Vat number $vatNumber is not valid!");
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $this->logger->error(sprintf('Date: %s - VAT Number: %s - Error: %s', date('Y-m-d H:i:s'), $vatNumber, $e->getMessage()));
            $output->writeln("An error occurred while validating the VAT number: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
