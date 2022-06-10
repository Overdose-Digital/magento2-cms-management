<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Model\OptionSource;

use Magento\Framework\Data\OptionSourceInterface;
use Overdose\CMSContent\Api\ContentImportInterface;

class CmsMode implements OptionSourceInterface
{
    /**
     * To option array
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            [
                'label' => __('Overwrite existing'),
                'value' => ContentImportInterface::OD_CMS_MODE_UPDATE
            ],
            [
                'label' => __('Skip existing'),
                'value' => ContentImportInterface::OD_CMS_MODE_SKIP
            ]
        ];
    }
}
