<?php
declare(strict_types=1);
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
use \TYPO3\CMS\Core\Localization\LanguageService;

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

    public function __construct()
    {
        $this->tca = $this->getTca();
        $this->tableNames = array_keys($this->tca);
    }

    /**
     * @param string[] $exceptTableNames
     * @return string[]
     */
    public function getAllTableNamesAllowedOnRootLevel(array $exceptTableNames = []): array
    {
        $rootLevelTables = [];
        foreach ($this->tca as $tableName => $tableConfiguration) {
            if (
                !empty($tableConfiguration['ctrl']['rootLevel'])
                &&
                !\in_array($tableName, $exceptTableNames, true)
                &&
                \in_array(
                    $tableConfiguration['ctrl']['rootLevel'],
                    [1, -1, true],
                    true
                )
            ) {
                $rootLevelTables[] = $tableName;
            }
        }

        // always add pages, even if they are excluded
        if (!\in_array('pages', $rootLevelTables, true)) {
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
     * @return string Field name of the configured label_alt field or empty string if not set
     */
    public function getLabelAltFieldFromTable(string $tableName): string
    {
        $labelAltField = '';
        if (!empty($this->tca[$tableName]['ctrl']['label_alt'])) {
            $labelAltField = $this->tca[$tableName]['ctrl']['label_alt'];
        }
        return $labelAltField;
    }

    /**
     * @return string Field name of the configured title field or empty string if not set
     */
    public function getTitleFieldFromTable(string $tableName): string
    {
        $titleField = '';
        if (!empty($this->tca[$tableName]['ctrl']['title'])) {
            $titleField = $this->tca[$tableName]['ctrl']['title'];
        }
        return $titleField;
    }

    public function getSortingField(string $tableName): string
    {
        $sortingField = '';
        if (!empty($this->tca[$tableName]['ctrl']['sortby'])) {
            $sortingField = $this->tca[$tableName]['ctrl']['sortby'];
        } elseif (!empty($this->tca[$tableName]['ctrl']['crdate'])) {
            $sortingField = $this->tca[$tableName]['ctrl']['crdate'];
        }
        return $sortingField;
    }

    public function getDeletedField(string $tableName): string
    {
        $deleteField = '';
        if (!empty($this->tca[$tableName]['ctrl']['delete'])) {
            $deleteField = $this->tca[$tableName]['ctrl']['delete'];
        }
        return $deleteField;
    }

    /**
     * Records whose deleted field evaluate to true will not be shown in the frontend.
     */
    public function getDisableField(string $tableName): string
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
     * @return array|null
     */
    public function getConfigurationArrayForTable(string $table)
    {
        if (isset($this->tca[$table])) {
            return $this->tca[$table];
        }
        return null;
    }

    /**
     * @return array|null
     */
    public function getColumnConfigurationForTableColumn(string $table, string $column)
    {
        if (isset($this->tca[$table]['columns'][$column])) {
            return $this->tca[$table]['columns'][$column];
        }
        return null;
    }

    /**
     * Returns all table names that are not in the exclusion list
     *
     * @param string[] $exceptTableNames
     */
    public function getAllTableNames(array $exceptTableNames = []): array
    {
        if (!empty($exceptTableNames)) {
            return array_diff($this->tableNames, $exceptTableNames);
        }
        return $this->tableNames;
    }

    /**
     * Get table name from locallang and TCA definition
     */
    public function getTableLabel(string $tableName): string
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

    public function isHiddenRootTable(string $tableName): bool
    {
        return isset($this->tca[$tableName]['ctrl']['hideTable'])
               && isset($this->tca[$tableName]['ctrl']['rootLevel'])
               && true === (bool)$this->tca[$tableName]['ctrl']['hideTable']
               && \in_array($this->tca[$tableName]['ctrl']['rootLevel'], [1, -1], true);
    }

    /**
     * @SuppressWarnings("PHPMD.Superglobals")
     */
    protected function getTca(): array
    {
        return isset($GLOBALS['TCA']) ? $GLOBALS['TCA'] : [];
    }

    /**
     * @SuppressWarnings("PHPMD.Superglobals")
     * @codeCoverageIgnore
     */
    protected function localizeLabel(string $label): string
    {
        if ($GLOBALS['LANG'] instanceof LanguageService) {
            return $GLOBALS['LANG']->sL($label);
        }
        return '';
    }
}
