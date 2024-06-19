<?php
defined('TYPO3') || die('Access denied.');

call_user_func(
    function ($extKey, $table) {
        $tca = [
            'columns' => [
                'realName' => [
                    'config' => [
                        'eval' => 'trim,required',
                    ],
                ],
                'email' => [
                    'config' => [
                        'eval' => 'trim,email,required,unique',
                    ],
                ],
            ],
        ];
        $GLOBALS['TCA'][$table] = array_replace_recursive($GLOBALS['TCA'][$table], $tca);

        $additionalColumns = [];
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns($table, $additionalColumns);

    },
    'bureaucratic',
    'be_users'
);
