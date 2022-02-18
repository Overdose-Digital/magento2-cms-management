<?php
declare(strict_types=1);

namespace Overdose\CMSContent\Model\Config\Cron;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class CronConfig
{
    const CMS_DIR = 'cms';
    const HISTORY_DIR = 'history';

    const PERIOD = 'by_periods';
    const OLDER_THAN = 'older_than';

    const DAY = 60 * 60 * 24;
    const WEEK = 60 * 60 * 24 * 7;
    const MONTH = 60 * 60 * 24 * 30;
    const YEAR = 60 * 60 * 24 * 365;

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
    private ScopeConfigInterface $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return bool
     */
    public function isCronEnabled(): bool
    {
        return (bool) $this->scopeConfig->getValue(
            self::XML_PATH_IS_CRON_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function getMethodType()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_DELETE_METHOD_TYPE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function getPeriodType()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_OLDER_THAN_PERIOD_TYPE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function getPeriodNumber()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_OLDER_THAN_PERIOD_NUMBER,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function isLogsEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_LOGS,
            ScopeInterface::SCOPE_STORE
        );
    }
}
