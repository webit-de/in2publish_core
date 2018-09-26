<?php
namespace In2code\In2publishCore\Utility;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 in2code.de
 *  Alex Kellner <alexander.kellner@in2code.de>,
 *  Oliver Eglseder <oliver.eglseder@in2code.de>
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

use TYPO3\CMS\Backend\Utility\BackendUtility as BackendUtilityCore;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * Class BackendUtility
 */
class BackendUtility
{
    /**
     * Get current page uid (normally from ?id=123)
     *
     * @param mixed $identifier
     * @param string $table
     *
     * @return int|string Returns the page ID or the folder ID when navigating in the file list
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public static function getPageIdentifier($identifier = null, $table = null)
    {
        // get id from given identifier
        if ('pages' === $table && is_numeric($identifier)) {
            return (int)$identifier;
        }

        // get id from ?id=123
        if (null !== ($getId = GeneralUtility::_GP('id'))) {
            return is_numeric($getId) ? (int)$getId : $getId;
        }

        // get id from AJAX request
        if (null !== ($pageId = GeneralUtility::_GP('pageId'))) {
            return (int)$pageId;
        }

        // get id from ?cmd[pages][123][delete]=1
        if (null !== ($cmd = GeneralUtility::_GP('cmd'))) {
            if (isset($cmd['pages']) && is_array($cmd['pages'])) {
                foreach (array_keys($cmd['pages']) as $pid) {
                    return (int)$pid;
                }
            }
        }

        // get id from ?popViewId=123
        if (null !== ($popViewId = GeneralUtility::_GP('popViewId'))) {
            return (int)$popViewId;
        }

        // get id from ?redirect=script.php?param1=a&id=123&param2=2
        if (null !== ($redirect = GeneralUtility::_GP('redirect'))) {
            $urlParts = parse_url($redirect);
            if (!empty($urlParts['query']) && stristr($urlParts['query'], 'id=')) {
                parse_str($urlParts['query'], $parameters);
                if (!empty($parameters['id'])) {
                    return (int)$parameters['id'];
                }
            }
        }

        // get id from record ?data[tt_content][13]=foo
        if (null !== ($data = GeneralUtility::_GP('data')) && is_array($data) && 'upload' !== key($data)) {
            $table = key($data);
            $result = DatabaseUtility
                ::buildLocalDatabaseConnection()
                ->select(
                    ['pid'],
                    $table,
                    ['uid' => (int)key($data[$table])]
                )
                ->fetch();
            if (false !== $result && isset($result['pid'])) {
                return (int)$result['pid'];
            }
        }

        // get id from rollback ?element=tt_content:42
        if (null !== ($rollbackFields = GeneralUtility::_GP('element')) && is_string($rollbackFields)) {
            $rollbackData = explode(':', $rollbackFields);
            if (count($rollbackData) > 1) {
                if ($rollbackData[0] === 'pages') {
                    return (int)$rollbackData[1];
                } else {
                    $result = DatabaseUtility
                        ::buildLocalDatabaseConnection()
                        ->select(
                            ['pid'],
                            $rollbackData[0],
                            ['uid' => (int)$rollbackData[1]]
                        )
                        ->fetch();
                    if (false !== $result && isset($result['pid'])) {
                        return (int)$result['pid'];
                    }
                }
            }
        }

        // Assume the record has been imported via DataHandler on the CLI
        // Also, this is the last fallback strategy
        if (!empty($table) && MathUtility::canBeInterpretedAsInteger($identifier)) {
            $row = DatabaseUtility
                ::buildLocalDatabaseConnection()
                ->select(
                    ['pid'],
                    $rollbackData[0],
                    ['uid' => (int)$identifier]
                )
                ->fetch();
            if (isset($row['pid'])) {
                return (int)$row['pid'];
            }
        }

        return 0;
    }

    /**
     * Create an URI to edit a record
     *
     * @param string $tableName
     * @param int $identifier
     * @return string
     */
    public static function buildEditUri($tableName, $identifier)
    {
        $uriParameters = [
            'edit' => [
                $tableName => [
                    $identifier => 'edit',
                ],
            ],
            'returnUrl' => BackendUtilityCore::getModuleUrl('web_In2publishCoreM1'),
        ];
        $editUri = BackendUtilityCore::getModuleUrl('record_edit', $uriParameters);
        return $editUri;
    }

    /**
     * Create an URI to undo a record
     *
     * @param string $table
     * @param int $identifier
     * @return string
     */
    public static function buildUndoUri($table, $identifier)
    {
        $route = GeneralUtility::_GP('route') ?: GeneralUtility::_GP('M');

        $returnParameters = [
            'id' => GeneralUtility::_GP('id'),
        ];
        foreach (GeneralUtility::_GET() as $name => $value) {
            if (is_array($value) && false !== strpos(strtolower($name), strtolower($route))) {
                $returnParameters[$name] = $value;
            }
        }

        $uriParameters = [
            'element' => $table . ':' . $identifier,
            'returnUrl' => BackendUtilityCore::getModuleUrl($route, $returnParameters),
        ];

        return BackendUtilityCore::getModuleUrl('record_history', $uriParameters);
    }
}
