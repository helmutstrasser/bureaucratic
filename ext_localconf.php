<?php
declare(strict_types=1);

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;

defined('TYPO3') or die();

call_user_func(
    static function ($extKey) {
        /**
         * Add default User TsConfig
         *
         * @todo: TYPO3 13 only support: remove EMU::addUserTSConfig() since it gets loaded by default
         */
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig(
            '@import "EXT:' . $extKey . '/Configuration/user.tsconfig"'
        );
    },
    'bureaucratic'
);
