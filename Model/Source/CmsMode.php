<?php

namespace Overdose\CMSContent\Model\Source;

use Overdose\CMSContent\Api\ContentInterface;

class CmsMode
{
    /**
     * To option array
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['label' => __('Overwrite existing'), 'value' => ContentInterface::OD_CMS_MODE_UPDATE],
            ['label' => __('Skip existing'), 'value' => ContentInterface::OD_CMS_MODE_SKIP],
        ];
    }
}
