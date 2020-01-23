<?php

namespace Overdose\CMSContent\Model\Config\Page;

class Reader extends \Overdose\CMSContent\Model\Config\ReaderAbstract
{
    protected $_idAttributes = [
        '/config/cms_pages' => '',
        '/config/cms_pages/cms_page' => 'identifier',
        '/config/cms_pages/cms_page/attribute' => 'code',
    ];
}
