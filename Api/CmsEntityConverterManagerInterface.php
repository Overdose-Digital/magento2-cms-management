<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Api;

use Magento\Framework\Exception\LocalizedException;
use Overdose\CMSContent\Model\Content\Converter\CmsEntityConverterInterface;

interface CmsEntityConverterManagerInterface
{
    /**#@+
     * Entity Types
     */
    const PAGE_ENTITY_CODE = 'pages';
    const BLOCK_ENTITY_CODE = 'blocks';
    /**#@-*/

    /**
     * Get converter by Entity
     *
     * @param string $type
     *
     * @return CmsEntityConverterInterface
     * @throws LocalizedException
     */
    public function getConverter(string $type): CmsEntityConverterInterface;
}
