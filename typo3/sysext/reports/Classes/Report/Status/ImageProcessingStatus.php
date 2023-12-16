<?php

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace TYPO3\CMS\Reports\Report\Status;

use TYPO3\CMS\Core\Imaging\GraphicalFunctions;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Reports\Status;
use TYPO3\CMS\Reports\StatusProviderInterface;

/**
 * Performs several checks about processing images
 */
class ImageProcessingStatus implements StatusProviderInterface
{
    /**
     * Determines the status of the FAL index.
     *
     * @return Status[] List of statuses
     */
    public function getStatus(): array
    {
        return [
            'webp' => $this->getMissingFilesStatus(),
        ];
    }

    public function getLabel(): string
    {
        return 'imageprocessing';
    }

    /**
     * Checks if webp support is activated, but webp is not enabled in ImageMagick / GraphicsMagick
     */
    protected function getMissingFilesStatus(): Status
    {
        $imageProcessing = GeneralUtility::makeInstance(GraphicalFunctions::class);
        if (!$imageProcessing->isProcessingEnabled()) {
            $severity = ContextualFeedbackSeverity::INFO;
            $value = $this->getLanguageService()->sL('LLL:EXT:reports/Resources/Private/Language/locallang_reports.xlf:status_disabled');
            $message = $this->getLanguageService()->sL('LLL:EXT:reports/Resources/Private/Language/locallang_reports.xlf:status_imageprocessing_disabled');
        // ImageMagick / GraphicsMagick is not enabled, all good
        } elseif (!in_array('webp', $imageProcessing->getImageFileExt(), true)) {
            // webp is not enabled in TYPO3's Configuration
            $severity = ContextualFeedbackSeverity::INFO;
            $message = $this->getLanguageService()->sL('LLL:EXT:reports/Resources/Private/Language/locallang_reports.xlf:status_imageprocessing_webp_not_configured');
            $value = $this->getLanguageService()->sL('LLL:EXT:reports/Resources/Private/Language/locallang_reports.xlf:status_disabled');
            // But ImageMagick can do it, maybe it could be activated
            if ($imageProcessing->webpSupportAvailable()) {
                $message = $this->getLanguageService()->sL('LLL:EXT:reports/Resources/Private/Language/locallang_reports.xlf:status_imageprocessing_webp_available');
            }
        } elseif (!$imageProcessing->webpSupportAvailable()) {
            // webp is configured to be available, but ImageMagick/GraphicsMagick does not support this.
            $severity = ContextualFeedbackSeverity::WARNING;
            $message = $this->getLanguageService()->sL('LLL:EXT:reports/Resources/Private/Language/locallang_reports.xlf:status_imageprocessing_webp_not_available');
            $value = $this->getLanguageService()->sL('LLL:EXT:reports/Resources/Private/Language/locallang_reports.xlf:status_enabled');
        } else {
            // webp is configured to be available, and ImageMagick/GraphicsMagick supports this.
            $severity = ContextualFeedbackSeverity::OK;
            $message = $this->getLanguageService()->sL('LLL:EXT:reports/Resources/Private/Language/locallang_reports.xlf:status_imageprocessing_webp_available_and_configured');
            $value = $this->getLanguageService()->sL('LLL:EXT:reports/Resources/Private/Language/locallang_reports.xlf:status_enabled');
        }

        return new Status(
            $this->getLanguageService()->sL('LLL:EXT:reports/Resources/Private/Language/locallang_reports.xlf:status_imageprocessing_webp'),
            $value,
            $message,
            $severity
        );
    }

    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
