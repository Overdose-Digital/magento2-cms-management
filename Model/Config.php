<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Store\Model\ScopeInterface;

class Config
{
    /**#@+
     * CMSContent Objects Types
     */
    public const TYPE_PAGE = 'pages';
    public const TYPE_BLOCK = 'blocks';
    /**#@-*/

    /**#@+
     * Export folders
     */
    const EXPORT_PATH = 'export/export';
    const EXTRACT_PATH = 'export/extract';
    const UPLOAD_PATH = 'export/extract';
    /**#@-*/

    public const CMS_DIR = 'cms';
    public const HISTORY_DIR = 'history';

    public const PERIOD = 'by_periods';
    public const OLDER_THAN = 'older_than';

    public const DAY = 60 * 60 * 24;
    public const WEEK = 60 * 60 * 24 * 7;
    public const MONTH = 60 * 60 * 24 * 30;
    public const YEAR = 60 * 60 * 24 * 365;

    const XML_PATH_IS_CRON_ENABLED = 'cms_content/delete_backups_by_cron/cron_enabled';
    const XML_PATH_DELETE_METHOD_TYPE = 'cms_content/delete_backups_by_cron/delete_methods/method';
    const XML_PATH_OLDER_THAN_PERIOD_TYPE = 'cms_content/delete_backups_by_cron/delete_methods/period';
    const XML_PATH_OLDER_THAN_PERIOD_NUMBER = 'cms_content/delete_backups_by_cron/delete_methods/number';
    const XML_PATH_LOGS = 'cms_content/delete_backups_by_cron/logs/logs_enabled';

    const CRON_STRING_PATH = 'crontab/default/jobs/cms_content_delete_backups/schedule/cron_expr';
    const CRON_ARRAY_PATH_TIME_VALUE = 'groups/delete_backups_by_cron/groups/cron_run_settings/fields/time/value';
    const CRON_ARRAY_PATH_FREQUENCY_VALUE = 'groups/delete_backups_by_cron/groups/cron_run_settings/fields/frequency/value';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param DirectoryList $directoryList
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        DirectoryList $directoryList
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->directoryList = $directoryList;
    }

    /**
     * Check is delete by cron enabled
     *
     * @return bool
     */
    public function isCronEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_IS_CRON_ENABLED, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get method type
     *
     * @return string
     */
    public function getMethodType(): string
    {
        if (!$result = $this->scopeConfig->getValue(self::XML_PATH_DELETE_METHOD_TYPE, ScopeInterface::SCOPE_STORE)) {
            return self::PERIOD;
        }
        return $result;
    }

    /**
     * Get period type
     *
     * @return int
     */
    public function getPeriodType(): int
    {
        if (!$result = $this->scopeConfig->getValue(
            self::XML_PATH_OLDER_THAN_PERIOD_TYPE,
            ScopeInterface::SCOPE_STORE
        )) {
            return self::MONTH;
        }
        return (int)$result;
    }

    /**
     * Get period number
     *
     * @return int
     */
    public function getPeriodNumber(): int
    {
        if (!$result = $this->scopeConfig->getValue(
            self::XML_PATH_OLDER_THAN_PERIOD_NUMBER, ScopeInterface::SCOPE_STORE
        )) {
            return 5;
        }
        return (int)$result;
    }

    /**
     * @return bool
     */
    public function isLogsEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_LOGS, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get full path to directory with backups
     *
     * @return string
     */
    public function getBackupsDir(): string
    {
        try {
            return $this->directoryList->getPath(DirectoryList::VAR_DIR) . DIRECTORY_SEPARATOR . self::CMS_DIR;
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Form export path
     *
     * @return string
     */
    public function getExportPath(): string
    {
        try {
            return $this->directoryList->getPath(DirectoryList::VAR_DIR)
                . DIRECTORY_SEPARATOR
                . self::CMS_DIR
                . DIRECTORY_SEPARATOR
                . self::EXPORT_PATH;
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Form Upload path
     *
     * @return string
     */
    public function getUploadPath(): string
    {
        try {
            return $this->directoryList->getPath(DirectoryList::VAR_DIR)
                . DIRECTORY_SEPARATOR
                . self::CMS_DIR
                . DIRECTORY_SEPARATOR
                . self::UPLOAD_PATH;
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Get Extract Path
     *
     * @return string
     */
    public function getExtractPath(): string
    {
        try {
            return $this->directoryList->getPath(DirectoryList::VAR_DIR)
                . DIRECTORY_SEPARATOR
                . self::CMS_DIR
                . DIRECTORY_SEPARATOR
                . self::EXTRACT_PATH;
        } catch (\Exception $e) {
            return '';
        }
    }
}
