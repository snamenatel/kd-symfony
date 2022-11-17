<?php

namespace App\Command;

use App\Repository\ProductRepository;
use DateTime;
use DOMElement;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name: 'import:product',
    description: 'Import Product from xml file',
)]
class ImportProductsCommand extends Command
{
    private const FILENAME = 'import.xml';

    public function __construct(
        private ProductRepository $productRepository,
        private Filesystem $filesystem,
        private string $uploadsDir,
        private string $archiveDir,
        private string $xmlContent = '',
        private array $products = [],
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->setContent();
        $this->parseXml();
        $this->saveInDB();
        $this->moveFileInArchive();

        return Command::SUCCESS;
    }

    private function setContent(): void
    {
        $path = $this->getFilePath();
        if (file_exists($path)) {
           $this->xmlContent = file_get_contents($path);
        }
    }

    private function getFilePath(): string
    {
        return $this->uploadsDir . DIRECTORY_SEPARATOR . 'xml' . DIRECTORY_SEPARATOR . self::FILENAME;
    }

    private function parseXml(): void
    {
        $crawler = new Crawler($this->xmlContent);
        $crawler = $crawler->filterXPath('products/product');

        /** @var DOMElement $item **/
        foreach ($crawler as $item) {
            $product = [];

            /** @var DOMElement $props **/
            foreach ($item->childNodes as $props) {
                $product[$props->nodeName] = $props->textContent;
            }
            $this->products[] = $product;
        }
    }

    private function saveInDB(): void
    {
        $this->productRepository->createProductsWithCategoriesFromXml($this->products);
    }

    private function moveFileInArchive(): void
    {
        $path = $this->getFilePath();
        if (file_exists($path)) {
            $archiveFilename = (new DateTime())->format('Y_m_d_H_i_s') . '_' . self::FILENAME;
            $this->filesystem->copy($path, $this->archiveDir . DIRECTORY_SEPARATOR . $archiveFilename);
            $this->filesystem->remove($path);
        }
    }
}
