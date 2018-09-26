<?php
namespace In2code\In2publishCore\Domain\Service;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 in2code.de
 *  Alex Kellner <alexander.kellner@in2code.de>,
 *  Oliver Eglseder <oliver.eglseder@in2code.de>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use In2code\In2publishCore\Domain\Model\RecordInterface;
use In2code\In2publishCore\Utility\DatabaseUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Replace markers in TCA definition
 */
class ReplaceMarkersService
{
    /**
     * @var Logger
     */
    protected $logger = null;

    /**
     * ReplaceMarkersService constructor.
     */
    public function __construct()
    {
        $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(static::class);
    }

    /**
     * replaces ###MARKER### where possible. It's missing
     * a lot of Markers support due to lack of documentation
     * If a Marker could not be replaced a Log is written.
     * This should, however, not be needed.
     *
     * @param RecordInterface $record
     * @param string $string
     * @return string
     */
    public function replaceMarkers(RecordInterface $record, $string)
    {
        if (strpos($string, '#') !== false) {
            $string = $this->replaceRecFieldMarker($record, $string);
            $string = $this->replaceGeneralMarkers($record, $string);
            $this->checkForMarkersAndErrors($string);
        }
        return $string;
    }

    /**
     * Replace ###REC_FIELD_fieldname### with it's value
     *
     * @param RecordInterface $record
     * @param string $string
     * @return string
     */
    protected function replaceRecFieldMarker(RecordInterface $record, $string)
    {
        if (strstr($string, '###REC_FIELD_')) {
            $string = preg_replace_callback(
                '~###REC_FIELD_(.*)###~',
                function ($matches) use ($record) {
                    $propertyName = $matches[1];
                    $propertyValue = $record->getLocalProperty($propertyName);
                    if ($propertyValue === null) {
                        $propertyValue = $record->getForeignProperty($propertyName);
                    }
                    return DatabaseUtility::quoteString($propertyValue);
                },
                $string
            );
        }
        return $string;
    }

    /**
     * Replace default marker names
     *
     * @param RecordInterface $record
     * @param $string
     * @return mixed
     */
    protected function replaceGeneralMarkers(RecordInterface $record, $string)
    {
        if (false !== strpos($string, '###CURRENT_PID###')) {
            if (null !== ($currentPid = $this->getCurrentRecordPageId($record))) {
                $string = str_replace('###CURRENT_PID###', $currentPid, $string);
            }
        }
        if (false !== strpos($string, '###THIS_UID###')) {
            if (null !== ($identifier = $record->getIdentifier())) {
                $string = str_replace('###THIS_UID###', $identifier, $string);
            }
        }
        if (false !== strpos($string, '###STORAGE_PID###')) {
            if (null !== ($storagePid = $this->getStoragePidFromPage($this->getCurrentRecordPageId($record)))) {
                $string = str_replace('###STORAGE_PID###', $storagePid, $string);
            }
        }
        $string = str_replace(
            [
                '###THIS_CID###',
                '###SITEROOT###',
                '###PAGE_TSCONFIG_ID###',
                '###PAGE_TSCONFIG_IDLIST###',
                '###PAGE_TSCONFIG_STR###',
            ],
            [
                0,
                '#_SITEROOT',
                '#PAGE_TSCONFIG_ID',
                '#PAGE_TSCONFIG_IDLIST',
                '#PAGE_TSCONFIG_STR',
            ],
            $string
        );
        return $string;
    }

    /**
     * Log if markers are not substituted or if there are errors
     *
     * @param $string
     * @return void
     */
    protected function checkForMarkersAndErrors($string)
    {
        if (strpos($string, '###') !== false) {
            $this->logger->error('Could not replace marker', ['string' => $string]);
        } elseif (strpos($string, '#') !== false) {
            $this->logger->warning('Marker replacement not implemented', ['string' => $string]);
        }
    }

    /**
     * @param int $pageId
     *
     * @return int
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    protected function getStoragePidFromPage($pageId)
    {
        $rootLine = BackendUtility::BEgetRootLine($pageId);
        foreach ($rootLine as $page) {
            if (!empty($page['storage_pid'])) {
                return (int)$page['storage_pid'];
            }
        }
        return 0;
    }

    /**
     * @param RecordInterface $record
     * @return mixed
     */
    protected function getCurrentRecordPageId(RecordInterface $record)
    {
        return ($record->hasLocalProperty('pid')
            ? $record->getLocalProperty('pid')
            : $record->getForeignProperty('pid'));
    }
}
