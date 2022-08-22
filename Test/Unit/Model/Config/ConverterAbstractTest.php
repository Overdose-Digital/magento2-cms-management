<?php

namespace Overdose\CMSContent\Test\Unit\Model\Config;

use DOMDocument;
use Overdose\CMSContent\Model\Config\Block\Converter;
use PHPUnit\Framework\TestCase;

class ConverterAbstractTest extends TestCase
{
    /**
     * @var Converter
     */
    private $converterObject;

    public function setUp(): void
    {
        $this->converterObject = new Converter();
    }

    /**
     * @dataProvider dataProviderForConvert
     */
    public function testConvert($xmlContent)
    {
        $domDocument = new DOMDocument();
        $domDocument->loadXML($xmlContent);

        $result = $this->converterObject->convert($domDocument);
        $expected = [
            'some-identifier_0' => [
                'identifier' => 'some-identifier',
                'title' => 'Some block title',
                'content' => '<div>Content here</div>',
                'version' => '1.0.0',
                'is_active' => '1',
                'store_ids' => ''
            ],
            'some-identifier-2_1_2_3' => [
                'identifier' => 'some-identifier-2',
                'title' => 'Some block title 2',
                'content' => '<div>Content here 2</div>',
                'version' => '1.0.1',
                'is_active' => '1',
                'store_ids' => '1,2,3'
            ]
        ];

        $this->assertSame($expected, $result);
    }

    public function dataProviderForConvert()
    {
        $xmlContent = '<?xml version="1.0"?><config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="cms_block_data.xsd"><cms_blocks><cms_block identifier="some-identifier"><attribute code="title">Some block title</attribute><attribute code="content"><![CDATA[<div>Content here</div>]]></attribute><attribute code="version">1.0.0</attribute><attribute code="is_active">1</attribute><attribute code="store_ids"/></cms_block><cms_block identifier="some-identifier-2"><attribute code="title">Some block title 2</attribute><attribute code="content"><![CDATA[<div>Content here 2</div>]]></attribute><attribute code="version">1.0.1</attribute><attribute code="is_active">1</attribute><attribute code="store_ids">1,2,3</attribute></cms_block></cms_blocks></config>';
        return [[$xmlContent]];
    }
}
