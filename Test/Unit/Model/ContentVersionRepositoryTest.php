<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Test\Unit\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResults;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Overdose\CMSContent\Api\Data\ContentVersionInterface;
use Overdose\CMSContent\Model\ContentVersion;
use Overdose\CMSContent\Model\ContentVersionFactory;
use Overdose\CMSContent\Model\ContentVersionRepository;
use Overdose\CMSContent\Model\ResourceModel\ContentVersion as ResourceModel;
use Overdose\CMSContent\Model\ResourceModel\ContentVersion\Collection;
use Overdose\CMSContent\Model\ResourceModel\ContentVersion\CollectionFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ContentVersionRepositoryTest extends TestCase
{
    /**
     * @var ContentVersion|MockObject
     */
    private $resourceMock;
    /**
     * @var ContentVersionFactory|MockObject
     */
    private $contentVersionFactoryMock;
    /**
     * @var CollectionFactory|MockObject
     */
    private $collectionFactoryMock;
    /**
     * @var SearchResultsInterfaceFactory|MockObject
     */
    private $searchResultFactoryMock;
    /**
     * @var CollectionProcessorInterface|MockObject
     */
    private $collectionProcessorMock;
    /**
     * @var ContentVersionInterface|MockObject
     */
    private $contentVersionMock;
    /**
     * @var ContentVersionRepository
     */
    private $repository;
    /**
     * @var SearchCriteriaInterface|MockObject
     */
    private $searchCriteriaMock;
    /**
     * @var Collection|MockObject
     */
    private $collectionMock;
    /**
     * @var SearchResults|MockObject
     */
    private $searchResultsMock;

    /**
     * Initialize test
     */
    public function setUp(): void
    {
        $this->resourceMock = $this->getMockBuilder(ResourceModel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contentVersionMock = $this->getMockBuilder(ContentVersion::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contentVersionFactoryMock = $this->getMockBuilder(ContentVersionFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->collectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchResultFactoryMock = $this->getMockBuilder(SearchResultsInterfaceFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->collectionProcessorMock = $this->getMockBuilder(CollectionProcessorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaMock = $this->getMockBuilder(SearchCriteriaInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->collectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchResultsMock = $this->getMockBuilder(SearchResults::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->repository = new ContentVersionRepository(
            $this->resourceMock,
            $this->contentVersionFactoryMock,
            $this->collectionFactoryMock,
            $this->searchResultFactoryMock,
            $this->collectionProcessorMock
        );
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testSave()
    {
        $id = '1';
        $this->contentVersionMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($id);
        $this->resourceMock->expects($this->atLeastOnce())
            ->method('save')
            ->with($this->contentVersionMock);
        $this->resourceMock->expects($this->atLeastOnce())
            ->method('load')
            ->with($this->contentVersionMock, $id)
            ->willReturn($this->contentVersionMock);
        $this->contentVersionFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->contentVersionMock);

        $this->assertEquals($this->contentVersionMock, $this->repository->save($this->contentVersionMock));
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testGet()
    {
        $id = '1';
        $this->contentVersionMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($id);
        $this->contentVersionFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->contentVersionMock);
        $this->resourceMock->expects($this->atLeastOnce())
            ->method('load')
            ->with($this->contentVersionMock, $id)
            ->willReturn($this->contentVersionMock);

        $this->assertEquals($this->contentVersionMock, $this->repository->get($id));
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testGetList()
    {
        $items = [$this->contentVersionMock, $this->contentVersionMock];
        $this->collectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn($items);
        $this->collectionFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->collectionMock);

        $this->collectionProcessorMock->expects($this->once())
            ->method('process')
            ->with($this->searchCriteriaMock, $this->collectionMock);

        $this->searchResultFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->searchResultsMock);

        $this->searchResultsMock->expects($this->once())
            ->method('setItems');

        $this->searchResultsMock->expects($this->once())
            ->method('setTotalCount');

        $this->searchResultsMock->expects($this->once())
            ->method('setSearchCriteria');

        $this->assertSame($this->searchResultsMock, $this->repository->getList($this->searchCriteriaMock));
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testDelete()
    {
        $this->contentVersionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->contentVersionMock);

        $this->contentVersionMock->expects($this->once())
            ->method('getId')
            ->willReturn('1');

        $this->resourceMock->expects($this->once())
            ->method('load');

        $this->resourceMock->expects($this->once())
            ->method('delete')
            ->with($this->contentVersionMock);

        $this->assertEquals(true, $this->repository->delete($this->contentVersionMock));
    }
}
