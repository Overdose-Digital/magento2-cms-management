<?php

namespace Overdose\CMSContent\Model\Generator\Json;

use Magento\Framework\Serialize\SerializerInterface;
use Overdose\CMSContent\Api\CmsEntityGeneratorInterface;

class Generator implements CmsEntityGeneratorInterface
{
    const TYPE = 'json';

    /**
     * @var SerializerInterface
     */
    private $serializerInterface;

    public function __construct(
        SerializerInterface $serializerInterface
    ) {
        $this->serializerInterface = $serializerInterface;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return self::TYPE;
    }

    /**
     * @param array $data
     * @return string
     */
    public function generate(array $data): string
    {
        return $this->serializerInterface->serialize($data);
    }
}
