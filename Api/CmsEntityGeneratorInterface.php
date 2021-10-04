<?php


namespace Overdose\CMSContent\Api;


interface CmsEntityGeneratorInterface
{
    public function getType(): string;

    public function generate(array $data): string;
}
