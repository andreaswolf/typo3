<?php
namespace TYPO3\CMS\Core\Tests\Functional\DataHandling\TranslationSorting;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */


/**
 * Local override for the ActionService provided by the testing framework, necessary to have a method for localizing
 * multiple records at once
 */
class ActionService extends \TYPO3\TestingFramework\Core\Functional\Framework\DataHandling\ActionService
{

    /**
     * @param array $records A map of table names to uid lists (as arrays)
     * @param int $languageId
     * @return array
     */
    public function localizeRecords(array $records, int $languageId): array
    {
        $commandMap = [];
        foreach ($records as $tableName => $uids) {
            foreach ((array)$uids as $uid) {
                $commandMap[$tableName][$uid] = [
                    'localize' => $languageId,
                ];
            }
        }
        $this->createDataHandler();
        $this->dataHandler->start([], $commandMap);
        $this->dataHandler->process_cmdmap();
        return $this->dataHandler->copyMappingArray;
    }

    public function synchronizeInlineLocalization(string $table, int $parentUid, string $field, int $languageId, array $childIds)
    {
        $commandMap = [
            $table => [
                $parentUid => [
                    'inlineLocalizeSynchronize' => [
                        'field' => $field,
                        'action' => 'synchronize',
                        'language' => $languageId,
                        'ids' => $childIds
                    ]
                ]
            ]
        ];
        $this->createDataHandler();
        $this->dataHandler->start([], $commandMap);
        $this->dataHandler->process_cmdmap();
        return $this->dataHandler->copyMappingArray;
    }

}
