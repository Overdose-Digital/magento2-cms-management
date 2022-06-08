<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Model\Config\Block;

use Overdose\CMSContent\Model\Config\ConverterAbstract;

class Converter extends ConverterAbstract
{
    protected $itemsNode = 'cms_blocks';
    protected $childNode = 'cms_block';
}
