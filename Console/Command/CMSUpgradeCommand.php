<?php

namespace Overdose\CMSContent\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Overdose\CMSContent\Api\ContentVersionManagementInterface;

class CMSUpgradeCommand extends Command
{
    /**
     * Type option
     */
    const OPTION_CMS_TYPE = 'type';

    /**
     * Identifier option
     */
    const OPTION_CMS_IDENTIFIER = 'identifier';

    /**
     * @var ContentVersionManagementInterface
     */
    private $contentVersionManagement;

    /**
     * CMSUpgradeCommand constructor.
     *
     * @param ContentVersionManagementInterface $contentVersionManagement
     * @param null $name
     */
    public function __construct(
        ContentVersionManagementInterface $contentVersionManagement,
        $name = null
    ) {
        parent::__construct($name);
        $this->contentVersionManagement = $contentVersionManagement;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('od:cms:upgrade')
            ->setDescription('CMS page/blocks upgrade configuration')
            ->setDefinition([
                new InputOption(
                    'type',
                    '-t',
                    InputOption::VALUE_REQUIRED,
                    'CMS-type to upgrade: [block|blocks|page|pages]'
                ),
                new InputOption(
                    'identifier',
                    '-i',
                    InputOption::VALUE_REQUIRED,
                    'Comma-separated Identifiers of Block/Page to upgrade'
                ),

            ]);

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $identifiers = [];
        $cmsIdentifier = $input->getOption(self::OPTION_CMS_IDENTIFIER);
        if (!empty($cmsIdentifier)) {
            $identifiers = explode(',', $cmsIdentifier);
        }

        $cmsType = $input->getOption(self::OPTION_CMS_TYPE);
        if (empty($cmsType) && !empty($cmsIdentifier)) {
            throw new \InvalidArgumentException('CMS Entity type OPTION [--type] missed');
        }

        if (!empty($cmsType)) {
            switch ($cmsType) {
                case 'block':
                case 'blocks':
                    $result = $this->contentVersionManagement->processBlocks($identifiers);
                    break;
                case 'page':
                case 'pages':
                    $result = $this->contentVersionManagement->processPages($identifiers);
                    break;
                default:
                    throw new \InvalidArgumentException($cmsType . ' is incorrect CMS entity type');
            }
        } else {
            $result = $this->contentVersionManagement->processAll();
        }
        $output->writeln('<info>Upgrade Completed!</info>');
//        $output->writeln('<info>Upgrade Completed! ' . count($result). ' items processed</info>');
//        foreach ($result as $item) {
//            $message = $item['type']  . ': ' . $item['identifier'] . ' ' . $item['old_version'] . ' --> ' . $item['new_version'];
//            $output->writeln('<comment>' . $message . '</comment>');
//        }
    }
}
