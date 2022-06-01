<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Io\File;
use Overdose\CMSContent\Api\CmsEntityGeneratorManagerInterface;
use Overdose\CMSContent\Api\ContentExportInterface;
use ZipArchive;

class ContentExport implements ContentExportInterface
{
    const FILENAME = 'cms';
    const MEDIA_ARCHIVE_PATH = 'media';

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var CmsEntityGeneratorManagerInterface
     */
    private $cmsEntityGeneratorManager;

    /**
     * @var File
     */
    private $file;

    /**
     * @param CmsEntityGeneratorManagerInterface $cmsEntityGeneratorManager
     * @param Filesystem $filesystem
     * @param File $file
     */
    public function __construct(
        CmsEntityGeneratorManagerInterface $cmsEntityGeneratorManager,
        Filesystem $filesystem,
        File $file
    ) {
        $this->cmsEntityGeneratorManager = $cmsEntityGeneratorManager;
        $this->filesystem = $filesystem;
        $this->file = $file;
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
        $exportPath = $this->filesystem->getExportPath();
        $relativeZipFile = Filesystem::EXPORT_PATH . '/' . $fileName;

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
            $absMediaPath = $this->filesystem->getMediaPath($mediaFile);
            if ($this->file->fileExists($absMediaPath, true)) {
                $zipArchive->addFile($absMediaPath, self::MEDIA_ARCHIVE_PATH . '/' . $mediaFile);
            }
        }

        $zipArchive->close();

        // Clear export path
        $this->file->rm($exportPath);

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
                    sprintf('%s_%s_%s.%s', self::FILENAME, $this->prepareEntityPartName($cmsEntityCode), $key, $type),
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
