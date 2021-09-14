<?php

namespace Overdose\CMSContent\Model\Source;

use Overdose\CMSContent\Api\ContentImportExportInterface;

class MediaMode
{
    /**
     * To option array
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['label' => __('Do not import'), 'value' => ContentImportExportInterface::OD_MEDIA_MODE_NONE],
            ['label' => __('Overwrite existing'), 'value' => ContentImportExportInterface::OD_MEDIA_MODE_UPDATE],
            ['label' => __('Skip existing'), 'value' => ContentImportExportInterface::OD_MEDIA_MODE_SKIP],
        ];
    }
}
