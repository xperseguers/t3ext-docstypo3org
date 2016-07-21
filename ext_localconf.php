<?php
defined('TYPO3_MODE') || die();

// Caching problem if the hook is configured in ext_tables.php instead!
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restdoc']['postProcessBody'][] = \Causal\Docstypo3org\Hooks\Restdoc::class . '->postProcessBody';

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Causal.' . $_EXTKEY,
    'Manuals',
    array('Manual' => 'list,show'),
    []
);

if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('realurl')) {
    // RealURL auto-configuration
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/realurl/class.tx_realurl_autoconfgen.php']['extensionConfiguration'][$_EXTKEY] =
        \Causal\Docstypo3org\Hooks\RealurlAutoConfiguration::class . '->registerDefaultConfiguration';
}
