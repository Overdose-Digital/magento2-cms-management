<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Model\Content;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Io\File;
use Overdose\CMSContent\Api\CmsEntityGeneratorManagerInterface;
use Overdose\CMSContent\Api\ContentExportInterface;
use Overdose\CMSContent\File\FileManagerInterface;
use ZipArchive;

class Export implements ContentExportInterface
{
    const FILENAME = 'cms';
    const MEDIA_ARCHIVE_PATH = 'media';

    /**
     * @var CmsEntityGeneratorManagerInterface
     */
    private $cmsEntityGeneratorManager;

    /**
     * @var File
     */
    private $file;

    /**
     * @var FileManagerInterface
     */
    private $fileManager;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param CmsEntityGeneratorManagerInterface $cmsEntityGeneratorManager
     * @param Config $config
     * @param FileManagerInterface $fileManager
     * @param File $file
     */
    public function __construct(
        CmsEntityGeneratorManagerInterface $cmsEntityGeneratorManager,
        Config $config,
        FileManagerInterface $fileManager,
        File $file
    ) {
        $this->cmsEntityGeneratorManager = $cmsEntityGeneratorManager;
        $this->config = $config;
        $this->file = $file;
        $this->fileManager = $fileManager;
    }

    /**
     * @inheridoc
     */
    public function createZipFile(
        array $convertedEntities,
        string $entityType,
        string $type,
        string $fileName,
        bool $split
    ): string {
        $exportPath = $this->fileManager->getFolder($this->config->getExportPath());

        $relativeZipFile = Config::CMS_DIR . DIRECTORY_SEPARATOR
            . Config::EXPORT_PATH . DIRECTORY_SEPARATOR
            . $fileName;

        $zipArchive = $this->putContentToZip(
            $convertedEntities,
            $entityType,
            $exportPath . '/' . $fileName,
            $type,
            $split
        );

        // Add media files
        foreach ($convertedEntities['media'] as $mediaFile) {
            //Strip Quotes if any
            $mediaFile = str_replace(['"',"&quot;","'"], '', $mediaFile);
            $absMediaPath = $this->fileManager->getMediaPath($mediaFile);
            if ($this->file->fileExists($absMediaPath, true)) {
                $zipArchive->addFile($absMediaPath, self::MEDIA_ARCHIVE_PATH . '/' . $mediaFile);
            }
        }

        $zipArchive->close();

        // Clear export path
        $this->file->rm($relativeZipFile);

        return $relativeZipFile;
    }

    /**
     * @param array $contentArray
     * @param string $cmsEntityCode
     * @param string $zipFileName
     * @param string $type
     * @param bool $split
     * @return ZipArchive
     * @throws LocalizedException
     */
    private function putContentToZip(
        array $contentArray,
        string $cmsEntityCode,
        string $zipFileName,
        string $type,
        bool $split
    ): ZipArchive {
        $zipArchive = new ZipArchive();
        $zipArchive->open($zipFileName, ZipArchive::CREATE);
        $generator = $this->cmsEntityGeneratorManager->getGenerator($type);

        if ($split) {
            foreach ($contentArray[$cmsEntityCode] as $key => $content) {
                $payload = $generator->generate(
                    [
                        $cmsEntityCode => [$key => $content]
                    ]
                );

                $zipArchive->addFromString(
                    sprintf(
                        '%s_%s_%s.%s',
                        self::FILENAME,
                        $this->prepareEntityPartName($cmsEntityCode),
                        $key,
                        $type
                    ),
                    $payload
                );
            }
        } else {
            $fullContent = [];
            foreach ($contentArray[$cmsEntityCode] as $key => $content) {
                $entityContent = [
                    $cmsEntityCode => [$key => $content]
                ];
                $fullContent = array_merge_recursive($fullContent, $entityContent);
            }
            $payload = $generator->generate($fullContent);
            $zipArchive->addFromString(
                sprintf('%s_%s.%s', self::FILENAME, $this->prepareEntityPartName($cmsEntityCode), $type),
                $payload
            );
        }

        return $zipArchive;
    }

    /**
     * Prepare entity part name
     *
     * @param string $cmsEntityCode
     *
     * @return string
     */
    private function prepareEntityPartName(string $cmsEntityCode): string
    {
        return substr($cmsEntityCode, 0, strlen($cmsEntityCode) - 1) . '_data';
    }
}
