<?php
declare(strict_types=1);

namespace Overdose\CMSContent\Model\Config\Cron\Source;

use Overdose\CMSContent\Model\Config\Cron\CronConfig;

class Periods implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var null|array
     */
    private ?array $options = null;

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        if (!$this->options) {
            $this->options = [
                ['label' => __('Days'), 'value' => CronConfig::DAY],
                ['label' => __('Weeks'), 'value' => CronConfig::WEEK],
                ['label' => __('Months'), 'value' => CronConfig::MONTH],
                ['label' => __('Years'), 'value' => CronConfig::YEAR],
            ];
        }
        return $this->options;
    }
}
