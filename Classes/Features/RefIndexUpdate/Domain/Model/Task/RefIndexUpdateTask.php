<?php
declare(strict_types=1);
namespace In2code\In2publishCore\Features\RefIndexUpdate\Domain\Model\Task;

/***************************************************************
 * Copyright notice
 *
 * (c) 2017 in2code.de and the following authors:
 * Holger Krämer <post@holgerkraemer.com>
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

use In2code\In2publishCore\Domain\Model\Task\AbstractTask;
use TYPO3\CMS\Core\Database\ReferenceIndex;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class RefIndexUpdateTask
 */
class RefIndexUpdateTask extends AbstractTask
{
    /**
     * Don't modify configuration
     *
     * @return void
     */
    public function modifyConfiguration()
    {
    }

    /**
     * Update sys_refindex for given records
     *
     *      expected:
     *      $this->configuration = [
     *          'tx_news_domain_model_news => [
     *              '2',
     *              '5',
     *          ]
     *          'tt_content' => [
     *              '13',
     *              '52',
     *          ]
     *      ]
     *
     * @return bool
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    protected function executeTask()
    {
        $count = 0;
        $refIndexObj = GeneralUtility::makeInstance(ReferenceIndex::class);
        foreach ($this->configuration as $table => $uidArray) {
            foreach ($uidArray as $uid) {
                $refIndexObj->updateRefIndexTable($table, $uid);
                $count++;
            }
        }
        $this->addMessage('Updated indices of ' . $count . ' records');
        return true;
    }
}
