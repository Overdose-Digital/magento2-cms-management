<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Model\Generator\Json;

use Magento\Framework\Serialize\SerializerInterface;
use Overdose\CMSContent\Model\Generator\CmsEntityGeneratorInterface;

class Generator implements CmsEntityGeneratorInterface
{
    /**
     * @var SerializerInterface
     */
    private $serializerInterface;

    /**
     * @param SerializerInterface $serializerInterface
     */
    public function __construct(
        SerializerInterface $serializerInterface
    ) {
        $this->serializerInterface = $serializerInterface;
    }

    /**
     * @param array $data
     *
     * @return string
     */
    public function generate(array $data): string
    {
        return $this->serializerInterface->serialize($data);
    }
}
