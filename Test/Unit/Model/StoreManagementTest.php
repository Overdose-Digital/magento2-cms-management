<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Test\Unit\Model;

use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Overdose\CMSContent\Model\StoreManagement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StoreManagementTest extends TestCase
{
    /**
     * @var StoreRepositoryInterface|MockObject
     */
    private $storeRepositoryMock;
    /**
     * @var StoreInterface|MockObject
     */
    private $storeInterfaceMock;
    /**
     * @var StoreManagement
     */
    private $model;

    /**
     * Initialize test
     */
    public function setUp(): void
    {
        $this->storeRepositoryMock = $this->getMockBuilder(StoreRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeInterfaceMock = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = new StoreManagement(
            $this->storeRepositoryMock
        );
    }

    /**
     * @return array
     */
    public function dataProviderGetStoreIdsByCodes(): array
    {
        return [
            'store_not_found_case' => [
                'storeCodes' => ['us'],
                'expectedResult' => []
            ],
            'store_found_case' => [
                'storeCodes' => ['nz'],
                'expectedResult' => [1]
            ],
            'admin_case' => [
                'storeCodes' => ['admin'],
                'expectedResult' => [0]
            ],
            'multi_store_case' => [
                'storeCodes' => ['admin', 'nz', 'us', 'au'],
                'expectedResult' => [0, 1, 2]
            ]
        ];
    }

    /**
     * @dataProvider dataProviderGetStoreIdsByCodes
     * @return void
     */
    public function testGetStoreIdsByCodes($storeCodes, $expectedResult)
    {
        $this->storeInterfaceMock
            ->method('getId')
            ->willReturn(1);

        $auStore = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auStore->method('getId')
            ->willReturn(2);

        $this->storeRepositoryMock
            ->method('get')
            ->withConsecutive(['nz'], ['au'])
            ->willReturnOnConsecutiveCalls($this->storeInterfaceMock, $auStore);

        $this->storeRepositoryMock->expects($this->atLeastOnce())
            ->method('getList')
            ->willReturn(['admin' => 'adminStore', 'nz' => 'nzStore', 'au' => 'auStore']);

        $this->assertEquals($expectedResult, $this->model->getStoreIdsByCodes($storeCodes));
    }

    /**
     * @return array
     */
    public function dataProviderFilterStoresByStoreCodes(): array
    {
        return [
            'store_not_found_case' => [
                'storeCodes' => ['pk'],
                'expectedResult' => []
            ],
            'store_found_case' => [
                'storeCodes' => ['nz'],
                'expectedResult' => ['nz']
            ],
            'admin_case' => [
                'storeCodes' => ['admin'],
                'expectedResult' => ['admin']
            ],
            'multi_store_case' => [
                'storeCodes' => ['admin', 'au', 'nz', 'ua'],
                'expectedResult' => ['admin', 'nz']
            ]
        ];
    }

    /**
     * @dataProvider dataProviderFilterStoresByStoreCodes
     * @return void
     */
    public function testFilterStoresByStoreCodes($storeCodes, $expectedResult)
    {
        $this->storeRepositoryMock->expects($this->once())
            ->method('getList')
            ->willReturn(['admin' => 'adminStore', 'nz' => 'nzStore']);

        $this->assertSame($expectedResult, $this->model->filterStoresByStoreCodes($storeCodes));
    }

    /**
     * @return array
     */
    public function dataProviderFilterStoresByStoreIds(): array
    {
        return [
            'store_not_found_case' => [
                'storeIds' => [77],
                'expectedResult' => []
            ],
            'store_found_case' => [
                'storeIds' => [1],
                'expectedResult' => [1]
            ],
            'admin_case' => [
                'storeIds' => [0],
                'expectedResult' => [0]
            ],
            'multi_store_case' => [
                'storeIds' => [0, 1, 2, 77],
                'expectedResult' => [0, 1, 2]
            ]
        ];
    }

    /**
     * @dataProvider dataProviderFilterStoresByStoreIds
     * @return void
     */
    public function testFilterStoresByStoreIds($storeIds, $expectedResult)
    {
        $adminStore = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $adminStore->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(0);

        $nzStore = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $nzStore->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(1);

        $auStore = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auStore->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(2);

        $this->storeRepositoryMock->expects($this->atLeastOnce())
            ->method('getList')
            ->willReturn(['admin' => $adminStore, 'nz' => $nzStore, 'au' => $auStore]);

        $this->assertSame($expectedResult, $this->model->filterStoresByStoreIds($storeIds));
    }
}
