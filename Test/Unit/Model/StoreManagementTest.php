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

    public function testGetStoreIdsByCodes()
    {
        $storeCodes = ['admin', 'nz'];

        $this->storeInterfaceMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(1);

        $this->storeRepositoryMock->expects($this->atLeastOnce())
            ->method('get')
            ->with('nz')
            ->willReturn($this->storeInterfaceMock);

        $this->storeRepositoryMock->expects($this->atLeastOnce())
            ->method('getList')
            ->willReturn(['admin' => 'adminStore', 'nz' => 'nzStore']);

        $expectedResult = [0, 1];

        $this->assertEquals($expectedResult, $this->model->getStoreIdsByCodes($storeCodes));
    }

    public function testFilterStoresByStoreCodes()
    {
        $storeCodes = ['admin', 'nz'];
        $this->storeRepositoryMock->expects($this->atLeastOnce())
            ->method('getList')
            ->willReturn(['admin' => 'adminStore', 'nz' => 'nzStore']);


        $this->assertSame($storeCodes, $this->model->filterStoresByStoreCodes($storeCodes));
    }

    public function testFilterStoresByStoreIds()
    {
        $storeIds = [1, 2];

        $this->storeRepositoryMock->expects($this->atLeastOnce())
            ->method('getList')
            ->willReturn(['nz' => $this->storeInterfaceMock, 'au' => $this->storeInterfaceMock]);

        $this->storeInterfaceMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturnOnConsecutiveCalls(1, 1, 2, 2);

        $this->assertEquals($storeIds, $this->model->filterStoresByStoreIds($storeIds));
    }
}
