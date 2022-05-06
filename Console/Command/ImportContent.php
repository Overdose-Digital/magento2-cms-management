<?php

namespace Overdose\CMSContent\Console\Command;

use Magento\Framework\ObjectManagerInterface;
use Overdose\CMSContent\Api\ContentImportExportInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportContent extends Command
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * ImportContent constructor.
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('od:cms:import');
        $this->setDescription('Import CMS zip file');
        $this->addArgument('zipfile', InputArgument::REQUIRED, __('Zip file containing CMS information'));

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $contentImportInterface = $this->objectManager->get(ContentImportExportInterface::class);

        $zipFile = $input->getArgument('zipfile');
        if ($contentImportInterface->importContentFromZipFile($zipFile, false) == 0) {
            throw new \Exception(__('Archive is empty'));
        }

        $output->writeln('Done.');
    }
}
