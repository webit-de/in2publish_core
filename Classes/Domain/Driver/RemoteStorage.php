<?php
declare(strict_types=1);
namespace In2code\In2publishCore\Domain\Driver;

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

use In2code\In2publishCore\Command\RpcCommandController;
use In2code\In2publishCore\Communication\RemoteCommandExecution\RemoteCommandDispatcher;
use In2code\In2publishCore\Communication\RemoteCommandExecution\RemoteCommandRequest;
use In2code\In2publishCore\Communication\RemoteProcedureCall\Envelope;
use In2code\In2publishCore\Communication\RemoteProcedureCall\EnvelopeDispatcher;
use In2code\In2publishCore\Communication\RemoteProcedureCall\Letterbox;
use In2code\In2publishCore\In2publishCoreException;
use TYPO3\CMS\Core\Resource\ResourceStorageInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class RemoteStorage
 */
class RemoteStorage implements ResourceStorageInterface
{
    const SUB_FOLDERS_KEY = 'subFolders';
    const FILES_KEY = 'files';
    const HAS_FOLDER_KEY = 'hasFolder';

    /**
     * @var Letterbox
     */
    protected $letterbox = null;

    /**
     * @var array
     */
    protected static $cache = [];

    /**
     * RemoteStorage constructor.
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function __construct()
    {
        $this->letterbox = GeneralUtility::makeInstance(Letterbox::class);
    }

    /**
     * @param int $storage
     * @param string $identifier
     * @return bool
     */
    public function hasFolder($storage, $identifier): bool
    {
        if (!isset(static::$cache[$storage][$identifier][static::HAS_FOLDER_KEY])) {
            $result = $this->executeEnvelope(
                EnvelopeDispatcher::CMD_STORAGE_HAS_FOLDER,
                ['storage' => $storage, 'identifier' => $identifier]
            );

            static::$cache[$storage][$identifier][static::HAS_FOLDER_KEY] = $result[static::HAS_FOLDER_KEY];
            static::$cache[$storage][$identifier][static::SUB_FOLDERS_KEY] = $result[static::SUB_FOLDERS_KEY];
            static::$cache[$storage][$identifier][static::FILES_KEY] = $result[static::FILES_KEY];
        }

        return static::$cache[$storage][$identifier][static::HAS_FOLDER_KEY];
    }

    /**
     * @param int $storage
     * @param string $identifier
     * @return array
     */
    public function getFoldersInFolder($storage, $identifier): array
    {
        if (!isset(static::$cache[$storage][$identifier][static::SUB_FOLDERS_KEY])) {
            $result = $this->executeEnvelope(
                EnvelopeDispatcher::CMD_STORAGE_GET_FOLDERS_IN_FOLDER,
                ['storage' => $storage, 'identifier' => $identifier]
            );

            static::$cache[$storage][$identifier][static::SUB_FOLDERS_KEY] = $result;
        }
        return static::$cache[$storage][$identifier][static::SUB_FOLDERS_KEY];
    }

    /**
     * @param int $storage
     * @param string $identifier
     * @return array
     */
    public function getFilesInFolder($storage, $identifier): array
    {
        if (!isset(static::$cache[$storage][$identifier][static::FILES_KEY])) {
            $result = $this->executeEnvelope(
                EnvelopeDispatcher::CMD_STORAGE_GET_FILES_IN_FOLDER,
                ['storage' => $storage, 'identifier' => $identifier]
            );

            static::$cache[$storage][$identifier][static::FILES_KEY] = $result;
        }
        return static::$cache[$storage][$identifier][static::FILES_KEY];
    }

    /**
     * @param int $storage
     * @param string $identifier
     * @return array
     */
    public function getFile($storage, $identifier): array
    {
        if (!isset(static::$cache[$storage][$identifier][static::FILES_KEY][$identifier])) {
            $result = $this->executeEnvelope(
                EnvelopeDispatcher::CMD_STORAGE_GET_FILE,
                ['storage' => $storage, 'identifier' => $identifier]
            );

            static::$cache[$storage][$identifier][static::FILES_KEY][$identifier] = $result;
        }
        return static::$cache[$storage][$identifier][static::FILES_KEY][$identifier];
    }

    /**
     * @param string $command
     * @param array $arguments
     *
     * @return mixed
     *
     * @throws \Exception
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    protected function executeEnvelope($command, array $arguments = [])
    {
        $envelope = new Envelope($command, $arguments);
        $uid = $this->letterbox->sendEnvelope($envelope);
        if (false === $uid) {
            throw new In2publishCoreException(
                'Could not send ' . $envelope->getCommand() . ' request to remote system',
                1490708190
            );
        }

        $request = GeneralUtility::makeInstance(
            RemoteCommandRequest::class,
            RpcCommandController::EXECUTE_COMMAND,
            [],
            [$uid]
        );
        $response = GeneralUtility::makeInstance(RemoteCommandDispatcher::class)->dispatch($request);

        if (!$response->isSuccessful()) {
            throw new \RuntimeException(
                'Could not execute RPC [' . $uid . ']. An error occurred on foreign: ' . $response->getErrorsString(),
                1476281965
            );
        }
        $envelope = $this->letterbox->receiveEnvelope($uid);
        if (false === $envelope) {
            throw new In2publishCoreException('Could not receive envelope [' . $uid . ']', 1490708194);
        }
        return $envelope->getResponse();
    }
}
