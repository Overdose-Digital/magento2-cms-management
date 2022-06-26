<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Console\Command;

use Overdose\CMSContent\Model\Config;
use Overdose\CMSContent\Model\Service\ClearCMSHistory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ClearCMSHistoryCommand extends Command
{
    /**
     * @var ClearCMSHistory
     */
    private $clearCMSHistory;

    /**
     * ClearCMSHistoryCommand constructor
     *
     * @param ClearCMSHistory $clearCMSHistory
     */
    public function __construct(
        ClearCMSHistory $clearCMSHistory
    ) {
        parent::__construct();
        $this->clearCMSHistory = $clearCMSHistory;
    }

    /**
     * @inheritdoc
     */
    protected function configure(): void
    {
        $this->setName('od:cms:history-clear')
            ->setDescription('Delete old CMS history files.')
            ->setDefinition([
                new InputOption(
                    'type',
                    '-t',
                    InputOption::VALUE_REQUIRED,
                    'CMS-type to upgrade: [block|blocks|page|pages]'
                )
            ]);

        parent::configure();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cmsType = $input->getOption('type');
        if (empty($cmsType)) {
            $output->writeln('<info>CMS Entity type OPTION [--type] missed!</info>');

            return;
        }

        try {
            switch ($cmsType) {
                case 'block':
                case 'blocks':
                    $count = $this->clearCMSHistory->execute(Config::TYPE_BLOCK);
                    break;
                case 'page':
                case 'pages':
                    $count = $this->clearCMSHistory->execute(Config::TYPE_PAGE);
                    break;
                default:
                    throw new \InvalidArgumentException($cmsType . ' is incorrect CMS entity type');
            }
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());

            return;
        }

        if ($count > 0) {
            $output->writeln("<info>" . __('%1 history files was deleted!', $count)->render() . "</info>");
        } else {
            $output->writeln('<info>All backups are actual!</info>');
        }
    }
}
