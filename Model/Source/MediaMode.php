<?php

namespace Overdose\CMSContent\Model\Source;

use Overdose\CMSContent\Api\ContentInterface;

class MediaMode
{
    /**
     * To option array
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['label' => __('Do not import'), 'value' => ContentInterface::OD_MEDIA_MODE_NONE],
            ['label' => __('Overwrite existing'), 'value' => ContentInterface::OD_MEDIA_MODE_UPDATE],
            ['label' => __('Skip existing'), 'value' => ContentInterface::OD_MEDIA_MODE_SKIP],
        ];
    }
}
