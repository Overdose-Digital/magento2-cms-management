<?php

namespace Overdose\CMSContent\Model\Config\Block;

class Reader extends \Overdose\CMSContent\Model\Config\ReaderAbstract
{
    const FILE_NAME = 'cms_block_data.xml';
    /**
     * @inheridoc
     */
    protected $_idAttributes = [
        '/config/cms_blocks' => '',
        '/config/cms_blocks/cms_block' => 'identifier',
        '/config/cms_blocks/cms_block/attribute' => 'code',
    ];
}
