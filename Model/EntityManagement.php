<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Model;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\Data\BlockInterface;
use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;

class EntityManagement
{
    /**#@+
     * Types
     */
    const TYPE_BLOCK = 'block';
    const TYPE_PAGE = 'page';
    /**#@-*/

    /**
     * @var array
     */
    private $repositoryList = [];

    /**
     * @var array
     */
    private $factoryList = [];

    /**
     * @param array $repositoryList
     * @param array $factoryList
     */
    public function __construct(
        array $repositoryList = [],
        array $factoryList = []
    ) {
        $this->repositoryList = $repositoryList;
        $this->factoryList = $factoryList;
    }

    /**
     * Get repository
     *
     * @return BlockRepositoryInterface|PageRepositoryInterface
     * @throws LocalizedException
     */
    public function getRepository(string $type)
    {
        if (isset($this->repositoryList[$type])) {
            return $this->repositoryList[$type];
        }
        throw new LocalizedException(__('Expected entity instance not found.'));
    }

    /**
     * Get repository
     *
     * @return BlockInterface|PageInterface
     * @throws LocalizedException
     */
    public function getFactory(string $type)
    {
        if (isset($this->factoryList[$type])) {
            return $this->factoryList[$type]->create();
        }
        throw new LocalizedException(__('Expected entity instance not found.'));
    }
}
