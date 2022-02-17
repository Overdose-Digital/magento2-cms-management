<?php
/**
 * Copyright © Overdose Digital. All rights reserved.
 * See LICENSE_OVERDOSE.txt for license details.
 */

namespace Overdose\CMSContent\Logger\Handler;

use Magento\Framework\Logger\Handler\Base;

class ImportLog extends Base
{
    const FILENAME = '/var/log/od_cms_import.log';

    /**
     * @var string
     */
    protected $fileName = self::FILENAME;

    /**
     * @var int
     */
    protected $loggerType = \Monolog\Logger::DEBUG;
}
