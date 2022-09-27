<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function ($extKey, $table) {
        $tca = [
            'columns' => [
                'header' => [
                    'config' => [
                        'eval' => 'trim,required',
                    ],
                ],
            ],
        ];
        $GLOBALS['TCA'][$table] = array_replace_recursive($GLOBALS['TCA'][$table], $tca);

        $additionalColumns = [];
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns($table, $additionalColumns);

    },
    'bureaucratic',
    'tt_content'
);