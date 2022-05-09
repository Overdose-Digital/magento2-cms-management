<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Cron;

use Overdose\CMSContent\Model\Config;
use Overdose\CMSContent\Model\Service\ClearCMSHistory;

class DeleteBackups
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var ClearCMSHistory
     */
    private $clearCMSHistory;

    /**
     * @param ClearCMSHistory $clearCMSHistory
     * @param Config $config
     */
    public function __construct(
        ClearCMSHistory $clearCMSHistory,
        Config $config
    ) {
        $this->clearCMSHistory = $clearCMSHistory;
        $this->config = $config;
    }

    /**
     * Process getting and deleting old CMS files
     *
     * @return void
     */
    public function execute(): void
    {
        if ($this->config->isCronEnabled()) {
            return;
        }
        $this->clearCMSHistory->execute(Config::TYPE_BLOCK);
        $this->clearCMSHistory->execute(Config::TYPE_PAGE);
    }
}
