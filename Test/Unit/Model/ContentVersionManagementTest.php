<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Test\Unit\Model;

use Overdose\CMSContent\Api\ContentVersionRepositoryInterface;
use Overdose\CMSContent\Api\Data\ContentVersionInterface;
use Overdose\CMSContent\Api\StoreManagementInterface;
use Overdose\CMSContent\Model\BackupManager;
use Overdose\CMSContent\Model\Config;
use Overdose\CMSContent\Model\Config\Block\Reader as BlocksConfigReader;
use Overdose\CMSContent\Model\Config\Page\Reader as PagesConfigReader;
use Overdose\CMSContent\Model\ContentVersion;
use Overdose\CMSContent\Model\ContentVersionManagement;
use Overdose\CMSContent\Model\EntityManagement;
use Overdose\CMSContent\Model\Service\GetCmsEntityItems;
use Overdose\CMSContent\Model\Service\GetContentVersions;
use Overdose\CMSContent\Api\Data\ContentVersionInterfaceFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ContentVersionManagementTest extends TestCase
{
    /**
     * @var GetContentVersions|MockObject
     */
    protected $getContentVersions;

    /**
     * @var ContentVersionManagement|MockObject
     */
    protected $getContentVersionManagement;

    /**
     * @var Config|MockObject
     */
    protected $config;

    /**
     * @var MockObject
     */
    protected $contentVersionFactory;

    /**
     * @var ContentVersionRepositoryInterface|MockObject
     */
    protected $contentVersionRepository;

    /**
     * @var BlocksConfigReader|MockObject
     */
    protected $blockConfigReader;

    /**
     * @var PagesConfigReader|MockObject
     */
    protected $pagesConfigReader;

    /**
     * @var BackupManager|MockObject
     */
    protected $backupManager;

    /**
     * @var EntityManagement|MockObject
     */
    protected $entityManagement;

    /**
     * @var GetCmsEntityItems|MockObject
     */
    protected $getCmsEntityItems;

    /**
     * @var StoreManagementInterface|MockObject
     */
    protected $storeManagement;

    /**
     * @var MockObject|LoggerInterface
     */
    protected $logger;

    /**
     * Initialize test
     */
    protected function setUp(): void
    {
        $this->config = $this->createMock(Config::class);

        $this->contentVersionFactory = $this->getMockBuilder(
            '\Overdose\CMSContent\Api\Data\ContentVersionInterfaceFactory'
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->contentVersionFactory->method('create')
            ->willReturn($this->createMock(ContentVersionInterface::class));

        $this->contentVersionRepository = $this->createMock(ContentVersionRepositoryInterface::class);
        $this->blockConfigReader = $this->createMock(BlocksConfigReader::class);
        $this->pagesConfigReader = $this->createMock(PagesConfigReader::class);
        $this->backupManager = $this->createMock(BackupManager::class);
        $this->entityManagement = $this->createMock(EntityManagement::class);
        $this->getCmsEntityItems = $this->createMock(GetCmsEntityItems::class);
        $this->storeManagement = $this->createMock(StoreManagementInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->getContentVersions = $this->createMock(GetContentVersions::class);
    }

    /**
     * Test ResolveBackupType
     *
     * @param string $type
     * @param string $expected
     *
     * @dataProvider formResolveBackupTypeProvider
     * @return void
     * @throws \ReflectionException
     */
    public function testResolveBackupType(string $type, string $expected)
    {
        $getContentVersionManagement = new ContentVersionManagement(
            $this->config,
            $this->contentVersionFactory,
            $this->contentVersionRepository,
            $this->blockConfigReader,
            $this->pagesConfigReader,
            $this->backupManager,
            $this->entityManagement,
            $this->getContentVersions,
            $this->getCmsEntityItems,
            $this->storeManagement,
            $this->logger
        );

        $result = $this->invokeMethod($getContentVersionManagement, 'resolveBackupType', [$type]);

        $this->assertEquals($expected, $result);
    }

    /**
     * Provider testMatchContentVersion
     *
     * @return array
     */
    public function formResolveBackupTypeProvider(): array
    {
        return [
            'case_1_block' => [
                'type' => EntityManagement::TYPE_BLOCK,
                'expected' => BackupManager::TYPE_CMS_BLOCK
            ],
            'case_2_page' => [
                'type' => EntityManagement::TYPE_PAGE,
                'expected' => BackupManager::TYPE_CMS_PAGE
            ]
        ];
    }

    /**
     * Test function: matchContentVersion
     *
     * @param array $contentVersions
     * @param string $identifier
     * @param int $type
     * @param string|null $storeIds
     * @param $expected
     *
     * @dataProvider formMatchContentVersionProvider
     * @return void
     * @throws \ReflectionException
     */
    public function testMatchContentVersion(
        array $contentVersions,
        string $identifier,
        int $type,
        ?string $storeIds,
        $expected
    ) {
        $this->getContentVersions
            ->expects($this->once())
            ->method('execute')
            ->willReturn($contentVersions);

        $getContentVersionManagement = new ContentVersionManagement(
            $this->config,
            $this->contentVersionFactory,
            $this->contentVersionRepository,
            $this->blockConfigReader,
            $this->pagesConfigReader,
            $this->backupManager,
            $this->entityManagement,
            $this->getContentVersions,
            $this->getCmsEntityItems,
            $this->storeManagement,
            $this->logger
        );

        $result = $this->invokeMethod(
            $getContentVersionManagement,
            'matchContentVersion',
            [
                $identifier,
                $type,
                $storeIds
            ]
        );

        $this->assertEquals($expected, $result);
    }

    /**
     * Provider testMatchContentVersion
     *
     * @return array
     */
    public function formMatchContentVersionProvider(): array
    {
        $contentVersion1 = $this->getMockBuilder(ContentVersion::class)
            ->disableOriginalConstructor()
            ->getMock();
        $contentVersion1->method('getStoreIds')->willReturn('1,2');

        $contentVersion2 = $this->getMockBuilder(ContentVersion::class)
            ->disableOriginalConstructor()
            ->getMock();
        $contentVersion2->method('getStoreIds')->willReturn('0');

        return [
            'case_1_exist' => [
                'contentVersions' => [
                    $contentVersion1,
                    $contentVersion2
                ],
                'identifier' => 'test',
                'type' => ContentVersionInterface::TYPE_BLOCK,
                'storeIds' => '0',
                'expected' => $contentVersion1
            ],
            'case_2_null' => [
                'contentVersions' => [
                    $contentVersion1,
                    $contentVersion2
                ],
                'identifier' => 'test',
                'type' => ContentVersionInterface::TYPE_BLOCK,
                'storeIds' => '3,4',
                'expected' => NULL
            ]
        ];
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
