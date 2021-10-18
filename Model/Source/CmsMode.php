<?php

namespace Overdose\CMSContent\Model\Source;

use Overdose\CMSContent\Api\ContentImportInterface;

class CmsMode
{
    /**
     * To option array
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['label' => __('Overwrite existing'), 'value' => ContentImportInterface::OD_CMS_MODE_UPDATE],
            ['label' => __('Skip existing'), 'value' => ContentImportInterface::OD_CMS_MODE_SKIP],
        ];
    }
}
