<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Model\Config\Page;

use Overdose\CMSContent\Model\Config\ReaderAbstract;

class Reader extends ReaderAbstract
{
    const FILE_NAME = 'cms_page_data.xml';

    /**
     * @inheridoc
     */
    protected $_idAttributes = [
        '/config/cms_pages' => '',
        '/config/cms_pages/cms_page' => 'identifier',
        '/config/cms_pages/cms_page/attribute' => 'code',
    ];
}
