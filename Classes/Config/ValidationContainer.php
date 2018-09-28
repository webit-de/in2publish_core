<?php
declare(strict_types=1);
namespace In2code\In2publishCore\Config;

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

use In2code\In2publishCore\Config\Node\Node;

/**
 * Class ValidationContainer
 */
class ValidationContainer
{
    /**
     * @var string[]
     */
    protected $path = [];

    /**
     * @var string[]
     */
    protected $errors = [];

    /**
     * @param Node $node
     * @param mixed $value
     */
    public function validate(Node $node, $value)
    {
        array_push($this->path, $node->getName());
        $node->validate($this, $value);
        array_pop($this->path);
    }

    /**
     * @param string $error
     */
    public function addError($error)
    {
        $this->errors[] = '[' . implode('.', $this->path) . ']: ' . $error;
    }

    /**
     * @return string[]
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
