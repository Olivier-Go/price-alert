<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * bin/console app:price-alert:item --url="https://www.boulanger.com/ref/1195870" --selector="section.product__summary p.price__amount" --price=900
 */
#[AsCommand(
    name: 'app:price-alert:item',
    description: 'Notify if a product price trigger a limit',
)]
class PriceAlertItemCommand extends Command
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('url', null, InputOption::VALUE_REQUIRED, 'Product url')
            ->addOption('selector', null, InputOption::VALUE_REQUIRED, 'Css selector for price sibling')
            ->addOption('price', null, InputOption::VALUE_REQUIRED, 'Product price to trigger')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $url = $input->getOption('url');
        $selector = $input->getOption('selector');
        $priceAlert = $input->getOption('price');
        if (null === $url || null == $selector || (null == $priceAlert || 0 === intval($priceAlert))) {
            throw new InvalidOptionException();
        }

        $response = $this->httpClient->request(Request::METHOD_GET, $url);
        if (200 !== $response->getStatusCode()) {
            throw new NotFoundHttpException();
        }

        $html = $response->getContent();
        $crawler = new Crawler($html);
        $crawler = $crawler
            ->filter('section.product__summary p.price__amount')
            ->reduce(fn (Crawler $node, $i): bool => ($i % 2) === 0);

        if ($crawler->count() !== 1) {
            throw new RuntimeException('Invalid product price css selector');
        }

        $priceValue = str_replace(
            search: ['€', ',', ' '],
            replace: ['', '.', ''],
            subject: trim($crawler->getNode(0)->nodeValue)
        );

        $io->info('Actual product price is : ' . $priceValue . '€');

        $price = intval($priceValue);
        $priceAlert = intval($priceAlert);

        if ($price <= $priceAlert) {
            dd('notif');
        }

        $io->success('Price alert not triggered');

        return Command::SUCCESS;
    }
}
