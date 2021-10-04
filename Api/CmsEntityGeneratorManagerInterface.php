<?php


namespace Overdose\CMSContent\Api;

interface CmsEntityGeneratorManagerInterface
{
    public function getGenerator(string $type): CmsEntityGeneratorInterface;
}
