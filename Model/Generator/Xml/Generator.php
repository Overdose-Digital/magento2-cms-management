<?php

namespace Overdose\CMSContent\Model\Generator\Xml;

use Magento\Framework\Xml\GeneratorFactory as XmlGeneratorFactory;
use Overdose\CMSContent\Api\CmsEntityGeneratorInterface;

class Generator implements CmsEntityGeneratorInterface
{
    const TYPE = 'xml';

    /**
     * @var XmlGeneratorFactory
     */
    private $xmlGeneratorFactory;

    public function __construct(
        XmlGeneratorFactory $xmlGeneratorFactory
    ) {
        $this->xmlGeneratorFactory = $xmlGeneratorFactory;
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
     * @throws \DOMException
     */
    public function generate(array $data): string
    {
        $xmlGenerator = $this->xmlGeneratorFactory->create();
        $xml = $xmlGenerator->arrayToXml(['config' => $data]);
        return (string)$xml;
    }
}
