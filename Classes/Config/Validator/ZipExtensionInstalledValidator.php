<?php
declare(strict_types=1);
namespace In2code\In2publishCore\Config\Validator;

/***************************************************************
 * Copyright notice
 *
 * (c) 2018 in2code.de and the following authors:
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

use In2code\In2publishCore\Config\ValidationContainer;

/**
 * Class ZipExtensionInstalledValidator
 */
class ZipExtensionInstalledValidator implements ValidatorInterface
{
    /**
     * @param ValidationContainer $container
     * @param string $value
     */
    public function validate(ValidationContainer $container, $value)
    {
        if ($value && !class_exists(\ZipArchive::class)) {
            $container->addError('Zip Backup is enabled but the PHP Extension zip does not seem to be installed');
        }
    }
}
