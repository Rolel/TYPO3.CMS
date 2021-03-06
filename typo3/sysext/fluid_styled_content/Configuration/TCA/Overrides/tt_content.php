<?php
defined('TYPO3_MODE') or die();

call_user_func(function () {
    $languageFilePrefix = 'LLL:EXT:fluid_styled_content/Resources/Private/Language/Database.xlf:';
    $frontendLanguageFilePrefix = 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:';

    // Add the CType "textmedia"
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTcaSelectItem(
        'tt_content',
        'CType',
        [
            $languageFilePrefix . 'tt_content.CType.textmedia',
            'textmedia',
            'content-textmedia'
        ],
        'header',
        'after'
    );
    $GLOBALS['TCA']['tt_content']['columns']['CType']['config']['default'] = 'textmedia';

    $GLOBALS['TCA']['tt_content']['ctrl']['typeicon_classes']['textmedia'] = 'mimetypes-x-content-text-media';
    $GLOBALS['TCA']['tt_content']['palettes']['mediaAdjustments'] = [
        'showitem' => '
			imagewidth;' . $languageFilePrefix . 'tt_content.palette.textmedia.imagewidth,
			imageheight;' . $languageFilePrefix . 'tt_content.palette.textmedia.imageheight,
			imageborder;' . $languageFilePrefix . 'tt_content.palette.textmedia.imageborder
		'
    ];
    $GLOBALS['TCA']['tt_content']['palettes']['gallerySettings'] = [
        'showitem' => '
			imageorient;' . $frontendLanguageFilePrefix . 'imageorient_formlabel,
			imagecols;' . $frontendLanguageFilePrefix . 'imagecols_formlabel
		'
    ];
    $GLOBALS['TCA']['tt_content']['types']['textmedia'] = [
        'showitem' => '
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
                --palette--;' . $frontendLanguageFilePrefix . 'palette.general;general,
                --palette--;' . $frontendLanguageFilePrefix . 'palette.header;header,
                bodytext;' . $frontendLanguageFilePrefix . 'bodytext_formlabel,
            --div--;' . $frontendLanguageFilePrefix . 'tabs.media,
                assets,
                --palette--;' . $frontendLanguageFilePrefix . 'palette.imagelinks;imagelinks,
            --div--;' . $frontendLanguageFilePrefix . 'tabs.appearance,
                layout;' . $frontendLanguageFilePrefix . 'layout_formlabel,
                --palette--;' . $languageFilePrefix . 'tt_content.palette.mediaAdjustments;mediaAdjustments,
                --palette--;' . $languageFilePrefix . 'tt_content.palette.gallerySettings;gallerySettings,
                --palette--;' . $frontendLanguageFilePrefix . 'palette.appearanceLinks;appearanceLinks,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
                --palette--;;language,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
                --palette--;;hidden,
                --palette--;' . $frontendLanguageFilePrefix . 'palette.access;access,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:categories,
                categories,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,
                rowDescription,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended,
        ',
    ];

    if (!isset($GLOBALS['TCA']['tt_content']['types']['textmedia']['columnsOverrides']['bodytext']['config'])
        || !is_array($GLOBALS['TCA']['tt_content']['types']['textmedia']['columnsOverrides']['bodytext']['config'])
    ) {
        $GLOBALS['TCA']['tt_content']['types']['textmedia']['columnsOverrides']['bodytext']['config'] = [];
    }
    $GLOBALS['TCA']['tt_content']['types']['textmedia']['columnsOverrides']['bodytext']['config']['enableRichtext'] = true;
    $GLOBALS['TCA']['tt_content']['types']['textmedia']['columnsOverrides']['bodytext']['config']['richtextConfiguration'] = 'default';

    $GLOBALS['TCA']['tt_content']['columns']['bodytext']['config']['search']['andWhere'] .= ' OR CType=\'textmedia\'';

    // Add table wizard
    $GLOBALS['TCA']['tt_content']['types']['table']['columnsOverrides']['bodytext']['defaultExtras'] = 'nowrap:wizards[table]';

    // Add additional fields for bullets + upload CTypes
    $additionalColumns = [
        'bullets_type' => [
            'exclude' => true,
            'label' => $languageFilePrefix . 'tt_content.bullets_type',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [$languageFilePrefix . 'tt_content.bullets_type.0', 0],
                    [$languageFilePrefix . 'tt_content.bullets_type.1', 1],
                    [$languageFilePrefix . 'tt_content.bullets_type.2', 2]
                ],
                'default' => 0
            ]
        ],
        'uploads_description' => [
            'exclude' => true,
            'label' => $languageFilePrefix . 'tt_content.uploads_description',
            'config' => [
                'type' => 'check',
                'default' => 0,
                'items' => [
                    ['LLL:EXT:lang/Resources/Private/Language/locallang_core.xml:labels.enabled', 1]
                ]
            ]
        ],
        'uploads_type' => [
            'exclude' => true,
            'label' => $languageFilePrefix . 'tt_content.uploads_type',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [$languageFilePrefix . 'tt_content.uploads_type.0', 0],
                    [$languageFilePrefix . 'tt_content.uploads_type.1', 1],
                    [$languageFilePrefix . 'tt_content.uploads_type.2', 2]
                ],
                'default' => 0
            ]
        ],
        'assets' => [
            'label' => $languageFilePrefix . 'tt_content.asset_references',
            'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig('assets', [
                'appearance' => [
                    'createNewRelationLinkTitle' => $languageFilePrefix . 'tt_content.asset_references.addFileReference'
                ],
                // custom configuration for displaying fields in the overlay/reference table
                // behaves the same as the image field.
                'foreign_types' => $GLOBALS['TCA']['tt_content']['columns']['image']['config']['foreign_types']
            ], $GLOBALS['TYPO3_CONF_VARS']['SYS']['mediafile_ext'])
        ],
    ];

    $GLOBALS['TCA']['tt_content']['ctrl']['thumbnail'] = 'assets';

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_content', $additionalColumns);
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tt_content', 'bullets_type', 'bullets', 'after:layout');
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette('tt_content', 'uploadslayout', 'uploads_description,uploads_type');
});
