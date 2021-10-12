<?php

namespace Overdose\CMSContent\Api;

interface CmsEntityGeneratorInterface
{
    /**
     * @return string
     */
    public function getType(): string;

    /**
     * @param array $data
     * @return string | array
     */
    public function generate(array $data): string;
}
