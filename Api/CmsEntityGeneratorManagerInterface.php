<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Api;

use Magento\Framework\Exception\LocalizedException;
use Overdose\CMSContent\Model\Generator\CmsEntityGeneratorInterface;

interface CmsEntityGeneratorManagerInterface
{
    /**
     * Get generator
     *
     * @param string $type
     *
     * @return CmsEntityGeneratorInterface
     * @throws LocalizedException
     */
    public function getGenerator(string $type): CmsEntityGeneratorInterface;
}
