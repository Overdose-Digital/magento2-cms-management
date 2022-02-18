<?php
declare(strict_types=1);

namespace Overdose\CMSContent\Model\Config\Cron\Source;

use Overdose\CMSContent\Model\Config\Cron\CronConfig;

class Methods implements \Magento\Framework\Data\OptionSourceInterface
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
                ['label' => __('By Periods'), 'value' => CronConfig::PERIOD],
                ['label' => __('Older Than'), 'value' => CronConfig::OLDER_THAN],
            ];
        }
        return $this->options;
    }
}
