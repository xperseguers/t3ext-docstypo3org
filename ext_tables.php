<?php
defined('TYPO3_MODE') || die();

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'Causal.' . $_EXTKEY,
    'Manuals',
    'List of TER extension manuals'
);
