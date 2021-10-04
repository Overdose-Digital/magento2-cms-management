<?php


namespace Overdose\CMSContent\Model\Generator\Xml;


use Magento\Framework\Xml\Generator as XmlGenerator;
use Overdose\CMSContent\Api\CmsEntityGeneratorInterface;

class Generator implements CmsEntityGeneratorInterface
{
    const TYPE = 'xml';
    /**
     * @var XmlGenerator
     */
    private $xmlGenerator;

    public function __construct(
        XmlGenerator $xmlGenerator
    )
    {
        $this->xmlGenerator = $xmlGenerator;
    }

    public function getType(): string
    {
        return self::TYPE;
    }

    public function generate(array $data): string
    {
        $xml = $this->xmlGenerator->arrayToXml($data);
        return (string)$xml;
    }
}
