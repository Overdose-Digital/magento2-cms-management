<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Model\OptionSource;

use Magento\Framework\Data\OptionSourceInterface;
use Overdose\CMSContent\Api\ContentImportInterface;

class MediaMode implements OptionSourceInterface
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
                'label' => __('Do not import'),
                'value' => ContentImportInterface::OD_MEDIA_MODE_NONE
            ],
            [
                'label' => __('Overwrite existing'),
                'value' => ContentImportInterface::OD_MEDIA_MODE_UPDATE
            ],
            [
                'label' => __('Skip existing'),
                'value' => ContentImportInterface::OD_MEDIA_MODE_SKIP
            ]
        ];
    }
}
