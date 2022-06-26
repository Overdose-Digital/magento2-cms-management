<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Test\Unit\Observer;

use Magento\Framework\Model\AbstractModel;
use Overdose\CMSContent\Observer\CmsSaveBefore;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CmsSaveBeforeTest extends TestCase
{
    /**
     * @var CmsSaveBefore|MockObject
     */
    protected $cmsSaveBefore;

    /**
     * Initialize test
     */
    protected function setUp(): void
    {
        /**
         * Init mock for CancelStuckOrders
         */
        $this->cmsSaveBefore = $this->getMockBuilder(CmsSaveBefore::class)
            ->disableOriginalConstructor(true)
            ->getMock();
    }

    /**
     * Test function: HasImportantDataChanges
     *
     * @param $cmsEntity
     * @param bool $expected
     * @dataProvider formHasImportantDataChangesProvider
     *
     * @return void
     * @throws \ReflectionException
     */
    public function testHasImportantDataChanges($cmsEntity, bool $expected)
    {
        $result = $this->invokeMethod(
            $this->cmsSaveBefore,
            'hasImportantDataChanges',
            [
                $cmsEntity
            ]
        );

        $this->assertEquals($expected, $result);
    }

    /**
     * Provider testHasImportantDataChanges
     *
     * @return array
     */
    public function formHasImportantDataChangesProvider(): array
    {
        $testCases = [];

        $dataModel = $this->getMockBuilder(AbstractModel::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $dataModelCase1 = clone $dataModel;
        $dataModelCase1->setData([
            'identifier' => 'no-route',
            'title' => 'no-route',
            'content' => 'no-route',
            'content_heading' => 'no-route',
        ]);
        $dataModelCase1->setOrigData('identifier', 'no-route');
        $dataModelCase1->setOrigData('title', 'no-route');
        $dataModelCase1->setOrigData('content', 'no-route');
        $dataModelCase1->setOrigData('content_heading', 'no-route');

        $testCases['case_1_true'] = [
            'cmsEntity' => $dataModelCase1,
            'expected' => false
        ];

        $dataModelCase2 = clone $dataModel;
        $dataModelCase2->setData([
            'identifier' => 'no-route2',
            'title' => 'no-route',
            'content' => 'no-route',
            'content_heading' => 'no-route',
        ]);
        $dataModelCase2->setOrigData('identifier', 'no-route');
        $dataModelCase2->setOrigData('title', 'no-route');
        $dataModelCase2->setOrigData('content', 'no-route');
        $dataModelCase2->setOrigData('content_heading', 'no-route');

        $testCases['case_2_false'] = [
            'cmsEntity' => $dataModelCase2,
            'expected' => true
        ];

        $dataModelCase3 = clone $dataModel;
        $dataModelCase3->setData([
            'identifier' => 'no-route2',
            'title' => 'no-route',
            'content' => 'no-route',
            'content_heading' => 'no-route',
        ]);

        $testCases['case_3_false'] = [
            'cmsEntity' => $dataModelCase3,
            'expected' => false
        ];

        return $testCases;
    }

    /**
     * Invoke private method
     *
     * @param $object
     * @param $methodName
     * @param array $parameters
     *
     * @return mixed
     * @throws \ReflectionException
     */
    protected function invokeMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
