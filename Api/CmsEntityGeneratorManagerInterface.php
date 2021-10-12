<?php

namespace Overdose\CMSContent\Api;

interface CmsEntityGeneratorManagerInterface
{
    /**
     * @param string $type
     * @return CmsEntityGeneratorInterface
     */
    public function getGenerator(string $type): CmsEntityGeneratorInterface;
}
