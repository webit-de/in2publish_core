<?php
namespace In2code\In2publishCore\Service\Configuration;

/***************************************************************
 * Copyright notice
 *
 * (c) 2016 in2code.de and the following authors:
 * Oliver Eglseder <oliver.eglseder@in2code.de>
 *
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use In2code\In2publishCore\Utility\DatabaseUtility;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Lang\LanguageService;

/**
 * Class TableConfigurationArrayService
 */
class TcaService implements SingletonInterface
{
    /**
     * @var array[]
     */
    protected $tca = [];

    /**
     * @var array
     */
    protected $tableNames = [];

    /**
     * TcaService constructor.
     */
    public function __construct()
    {
        $this->tca = $this->getTca();
        $this->tableNames = array_keys($this->tca);
    }

    /**
     * @param array $exceptTableNames
     * @return array
     */
    public function getAllTableNamesAllowedOnRootLevel(array $exceptTableNames = [])
    {
        $rootLevelTables = [];
        foreach ($this->tca as $tableName => $tableConfiguration) {
            if (!in_array($tableName, $exceptTableNames)) {
                if (!empty($tableConfiguration['ctrl']['rootLevel'])) {
                    if (in_array($tableConfiguration['ctrl']['rootLevel'], [1, -1, true])) {
                        $rootLevelTables[] = $tableName;
                    }
                }
            }
        }

        // always add pages, even if they are excluded
        if (!in_array('pages', $rootLevelTables)) {
            $rootLevelTables[] = 'pages';
        }
        return $rootLevelTables;
    }

    /**
     * Get label field name from table
     *
     * @param string $tableName
     * @return string Field name of the configured label field or empty string if not set
     */
    public function getLabelFieldFromTable($tableName)
    {
        $labelField = '';
        if (!empty($this->tca[$tableName]['ctrl']['label'])) {
            $labelField = $this->tca[$tableName]['ctrl']['label'];
        }
        return $labelField;
    }

    /**
     * Get label_alt field name from table
     *
     * @param string $tableName
     * @return string Field name of the configured label_alt field or empty string if not set
     */
    public function getLabelAltFieldFromTable($tableName)
    {
        $labelAltField = '';
        if (!empty($this->tca[$tableName]['ctrl']['label_alt'])) {
            $labelAltField = $this->tca[$tableName]['ctrl']['label_alt'];
        }
        return $labelAltField;
    }

    /**
     * Get title field name from table
     *
     * @param string $tableName
     * @return string Field name of the configured title field or empty string if not set
     */
    public function getTitleFieldFromTable($tableName)
    {
        $titleField = '';
        if (!empty($this->tca[$tableName]['ctrl']['title'])) {
            $titleField = $this->tca[$tableName]['ctrl']['title'];
        }
        return $titleField;
    }

    /**
     * Get sorting field from TCA definition
     *
     * @param string $tableName
     * @return string
     */
    public function getSortingField($tableName)
    {
        $sortingField = '';
        if (!empty($this->tca[$tableName]['ctrl']['sortby'])) {
            $sortingField = $this->tca[$tableName]['ctrl']['sortby'];
        } elseif (!empty($this->tca[$tableName]['ctrl']['crdate'])) {
            $sortingField = $this->tca[$tableName]['ctrl']['crdate'];
        }
        return $sortingField;
    }

    /**
     * Get deleted field from TCA definition
     *
     * @param string $tableName
     * @return string
     */
    public function getDeletedField($tableName)
    {
        $deleteField = '';
        if (!empty($this->tca[$tableName]['ctrl']['delete'])) {
            $deleteField = $this->tca[$tableName]['ctrl']['delete'];
        }
        return $deleteField;
    }

    /**
     * Get the disabled field from TCA.
     * Records whose deleted field evaluate to true will not be shown in the frontend.
     *
     * @param string $tableName
     * @return string
     */
    public function getDisableField($tableName)
    {
        $deleteField = '';
        if (!empty($this->tca[$tableName]['ctrl']['enablecolumns']['disabled'])) {
            $deleteField = $this->tca[$tableName]['ctrl']['enablecolumns']['disabled'];
        }
        return $deleteField;
    }

    /**
     * Returns all table names that are not in the exclusion list and that have
     * a pid and uid field
     *
     * @param string[] $exceptTableNames
     * @return string[]
     */
    public function getAllTableNamesWithPidAndUidField(array $exceptTableNames = []): array
    {
        $result = [];

        $database = DatabaseUtility::buildLocalDatabaseConnection();
        if ($database) {
            foreach ($database->getSchemaManager()->listTables() as $table) {
                if (
                    $table->hasColumn('uid')
                    &&
                    $table->hasColumn('pid')
                    &&
                    !\in_array($table->getName(), $exceptTableNames, true)
                ) {
                    $result[] = $table->getName();
                }
            }
        }

        return $result;
    }

    /**
     * @param string $table
     * @return array|null
     */
    public function getConfigurationArrayForTable($table)
    {
        if (isset($this->tca[$table])) {
            return $this->tca[$table];
        }
        return null;
    }

    /**
     * @param string $table
     * @param string $column
     * @return array|null
     */
    public function getColumnConfigurationForTableColumn($table, $column)
    {
        if (isset($this->tca[$table]['columns'][$column])) {
            return $this->tca[$table]['columns'][$column];
        }
        return null;
    }

    /**
     * Returns all table names that are not in the exclusion list
     *
     * @param array $exceptTableNames
     * @return array
     */
    public function getAllTableNames(array $exceptTableNames = [])
    {
        if (!empty($exceptTableNames)) {
            return array_diff($this->tableNames, $exceptTableNames);
        }
        return $this->tableNames;
    }

    /**
     * Get table name from locallang and TCA definition
     *
     * @param string $tableName
     * @return string
     */
    public function getTableLabel($tableName)
    {
        $label = ucfirst($tableName);

        $titleField = $this->getTitleFieldFromTable($tableName);

        if ('' !== $titleField) {
            $localizedLabel = $this->localizeLabel($titleField);
            if (!empty($localizedLabel)) {
                $label = $localizedLabel;
            }
        }

        return $label;
    }

    /**
     * @param string $tableName
     * @return bool
     */
    public function isHiddenRootTable($tableName)
    {
        return isset($this->tca[$tableName]['ctrl']['hideTable'])
               && isset($this->tca[$tableName]['ctrl']['rootLevel'])
               && true === (bool)$this->tca[$tableName]['ctrl']['hideTable']
               && in_array($this->tca[$tableName]['ctrl']['rootLevel'], [1, -1]);
    }

    /**
     * @return array[]
     * @SuppressWarnings("PHPMD.Superglobals")
     */
    protected function getTca()
    {
        return isset($GLOBALS['TCA']) ? $GLOBALS['TCA'] : [];
    }

    /**
     * @param string $label
     * @return string
     * @SuppressWarnings("PHPMD.Superglobals")
     * @codeCoverageIgnore
     */
    protected function localizeLabel($label)
    {
        if ($GLOBALS['LANG'] instanceof LanguageService) {
            return $GLOBALS['LANG']->sL($label);
        }
        return '';
    }
}
