<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Model\Config\Cron\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Overdose\CMSContent\Model\Config;

class Methods implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            [
                'label' => __('By Periods'),
                'value' => Config::PERIOD
            ],
            [
                'label' => __('Older Than'),
                'value' => Config::OLDER_THAN
            ]
        ];
    }
}
