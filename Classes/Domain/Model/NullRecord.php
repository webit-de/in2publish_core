<?php
declare(strict_types=1);
namespace In2code\In2publishCore\Domain\Model;

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

use In2code\In2publishCore\Domain\Service\TcaProcessingService;

/**
 * Representation of a (default: Page-) Record that does not exist, neither on Local nor on Foreign.
 * Does not need any constructor argument
 */
class NullRecord extends Record
{
    /**
     * @param string $tableName
     * @param array $localProperties
     * @param array $foreignProperties
     * @param array $tca
     * @param array $additionalProperties
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     */
    public function __construct(
        $tableName = 'pages',
        array $localProperties = [0 => false],
        array $foreignProperties = [0 => false],
        array $tca = [],
        array $additionalProperties = ['depth' => 1]
    ) {
        $this->tableName = $tableName;
        $this->additionalProperties = $additionalProperties;
        $this->localProperties = $localProperties;
        $this->foreignProperties = $foreignProperties;
        if (empty($tca)) {
            $tca = TcaProcessingService::getCompleteTcaForTable($tableName);
        }
        $this->tca = $tca;
        $this->dirtyProperties = [];
        $this->state = RecordInterface::RECORD_STATE_UNCHANGED;
    }
}
