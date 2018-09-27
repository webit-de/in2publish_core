<?php
declare(strict_types=1);
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

use TYPO3\CMS\Core\Resource\FolderInterface;

class FolderUtility
{
    public static function extractFolderInformation(FolderInterface $folder): array
    {
        return [
            'name' => $folder->getName(),
            'identifier' => $folder->getIdentifier(),
            'storage' => $folder->getStorage()->getUid(),
            'uid' => sprintf('%d:%s', $folder->getStorage()->getUid(), $folder->getIdentifier()),
        ];
    }

    public static function extractFoldersInformation(array $folders): array
    {
        foreach ($folders as $index => $folder) {
            $folders[$index] = static::extractFolderInformation($folder);
        }
        return $folders;
    }
}
