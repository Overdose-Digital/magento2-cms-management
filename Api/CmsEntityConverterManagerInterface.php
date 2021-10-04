<?php


namespace Overdose\CMSContent\Api;


interface CmsEntityConverterManagerInterface
{
    public function setEntities(array $entities): self;

    public function getConverter(): CmsEntityConverterInterface;
}
