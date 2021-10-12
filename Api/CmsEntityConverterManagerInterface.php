<?php

namespace Overdose\CMSContent\Api;

interface CmsEntityConverterManagerInterface
{
    /**
     * @param array $entities
     * @return $this
     */
    public function setEntities(array $entities): self;

    /**
     * @return CmsEntityConverterInterface
     */
    public function getConverter(): CmsEntityConverterInterface;
}
