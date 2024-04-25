<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Console\Command;

use Overdose\CMSContent\Api\ContentImportInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportContent extends Command
{
    /**
     * @var ContentImportInterface
     */
    private $contentImport;

    /**
     * ImportContent constructor.
     *
     * @param ContentImportInterface $contentImport
     */
    public function __construct(
        ContentImportInterface $contentImport
    ) {
        $this->contentImport = $contentImport;
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('od:cms:import');
        $this->setDescription('Import CMS zip file');
        $this->addArgument('zipfile', InputArgument::REQUIRED, __('Zip file containing CMS information')->render());

        parent::configure();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $zipFile = $input->getArgument('zipfile');
        if ($this->contentImport->importContentFromZipFile($zipFile, false) == 0) {
            $output->writeln('Archive is empty.');

            return;
        }
        $output->writeln('Done.');
        return 0;
    }
}
