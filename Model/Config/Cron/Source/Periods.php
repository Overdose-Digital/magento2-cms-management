<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Model\Config\Cron\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Overdose\CMSContent\Model\Config;

class Periods implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            [
                'label' => __('Days'),
                'value' => Config::DAY
            ],
            [
                'label' => __('Weeks'),
                'value' => Config::WEEK
            ],
            [
                'label' => __('Months'),
                'value' => Config::MONTH
            ],
            [
                'label' => __('Years'),
                'value' => Config::YEAR
            ]
        ];
    }
}
