<?php

namespace Overdose\CMSContent\Model\Config\Block;

class Reader extends \Overdose\CMSContent\Model\Config\ReaderAbstract
{
    protected $_idAttributes = [
        '/config/cms_blocks' => '',
        '/config/cms_blocks/cms_block' => 'identifier',
        '/config/cms_blocks/cms_block/attribute' => 'code',
    ];
}
