<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Console\Command;

use Overdose\CMSContent\Model\Service\ClearCMSHistory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearCMSHistoryCommand extends Command
{
    /**
     * @var ClearCMSHistory
     */
    private ClearCMSHistory $clearCMSHistory;

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
            ->setDescription('Delete old CMS history files.');

        parent::configure();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $count = $this->clearCMSHistory->execute();
        if ($count > 0) {
            $output->writeln("<info>" . __('%1 history files was deleted!', $count)->render() . "</info>");
        } else {
            $output->writeln('<info>All backups are actual!</info>');
        }
    }
}
