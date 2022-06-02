<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Model\Converter;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\StoreRepositoryInterface;

abstract class AbstractConverter
{
    /**
     * @var BlockRepositoryInterface
     */
    protected $blockRepositoryInterface;

    /**
     * @var StoreRepositoryInterface
     */
    protected $storeRepositoryInterface;

    /**
     * @param StoreRepositoryInterface $storeRepositoryInterface
     * @param BlockRepositoryInterface $blockRepositoryInterface
     */
    public function __construct(
        StoreRepositoryInterface $storeRepositoryInterface,
        BlockRepositoryInterface $blockRepositoryInterface
    ) {
        $this->storeRepositoryInterface = $storeRepositoryInterface;
        $this->blockRepositoryInterface = $blockRepositoryInterface;
    }

    /**
     * Get media attachments from content
     *
     * @param $content
     *
     * @return array
     */
    protected function getMediaAttachments($content): array
    {
        $result = [];
        if (preg_match_all('/\{\{media.+?url\s*=\s*("|&quot;)(.+?)("|&quot;).*?\}\}/', $content, $matches)) {
            $result += $matches[2];
        }

        if (preg_match_all('/{{media.+?url\s*=\s*(?!"|&quot;)(.+?)}}/', $content, $matches)) {
            $result += $matches[1];
        }

        return $result;
    }

    /**
     * @param string $content
     *
     * @return array
     * @throws LocalizedException
     */
    protected function saveBlockByIdent(string $content): array
    {
        $references = [];

        $pattern = '/{{widget.+?block_id\s*=\s*("|&quot;)(\d+?)("|&quot;).*?}}/';

        if (preg_match_all($pattern, $content, $matches)) {
            foreach ($matches[2] as $blockId) {
                $block = $this->blockRepositoryInterface->getById($blockId);
                $references[$blockId] = $block->getIdentifier();
            }
        }

        return $references;
    }

    /**
     * Get store codes
     *
     * @param array $storeIds
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getStoreCodes(array $storeIds): array
    {
        $return = [];
        foreach ($storeIds as $storeId) {
            $return[] = $this->storeRepositoryInterface->getById($storeId)->getCode();
        }
        return $return;
    }
}
