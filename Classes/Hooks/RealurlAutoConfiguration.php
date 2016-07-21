<?php
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with TYPO3 source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace Causal\Docstypo3org\Hooks;

/**
 * RealURL auto-configuration and segment decoder.
 *
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   Causal Sàrl
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class RealurlAutoConfiguration
{

    /**
     * Generates additional RealURL configuration and merges it with provided configuration.
     *
     * @param array $params
     * @param \tx_realurl_autoconfgen|object $pObj
     * @return array
     */
    public function registerDefaultConfiguration(array $params, $pObj)
    {
        $fixedPostVarsConfiguration = $this->getFixedPostVarsConfiguration();

        $defaultConfiguration = array_merge_recursive(
            $params['config'],
            $fixedPostVarsConfiguration,
            array(
                'postVarSets' => array(
                    '_DEFAULT' => array(
                        /*
                        'manual' => array(
                            array(
                                'GETvar' => 'tx_docstypo3org_manuals[controller]',
                                'noMatch' => 'bypass'
                            ),
                            array(
                                'GETvar' => 'tx_docstypo3org_manuals[action]',
                                'valueMap' => array(
                                    'Show' => 'show',
                                ),
                                'noMatch' => 'bypass'
                            ),
                            array(
                                'GETvar' => 'tx_docstypo3org_manuals[extension]',
                            ),
                            array(
                                'GETvar' => 'tx_docstypo3org_manuals[locale]',
                            ),
                            array(
                                'GETvar' => 'tx_docstypo3org_manuals[version]',
                            ),
                        ),
                        */
                    ),
                ),
            )
        );

        return $defaultConfiguration;
    }

    /**
     * This methods will "eat" every remaining segment in the URL to make it part
     * of the requested document.
     *
     * @param array $parameters
     * @return string
     */
    public function decodeSpURL_getSequence(array $parameters)
    {
        $value = $parameters['value'];

        if ((bool)$parameters['decodeAlias']) {
            if (!empty($parameters['pathParts'])) {
                // Eat every remaining segment
                $value .= '/' . implode('/', $parameters['pathParts']);
                $parameters['pathParts'] = [];
            }
        }

        return $value;
    }

    /**
     * Generates a default "fixedPostVars" configuration for RealURL
     * based on pages containing a restdoc plugin.
     *
     * @return array
     */
    protected function getFixedPostVarsConfiguration()
    {
        $fixedPostVarsConfiguration = array();

        // Search pages with a restdoc plugin
        $databaseConnection = $this->getDatabaseConnection();
        $pages = $databaseConnection->exec_SELECTgetRows(
            'DISTINCT pid',
            'tt_content',
            'list_type=' . $databaseConnection->fullQuoteStr('docstypo3org_manuals', 'tt_content') .
            ' AND deleted=0 AND hidden=0',
            '',
            '',
            '',
            'pid'
        );
        $pages = array_keys($pages);

        if (!empty($pages)) {
            $fixedPostVarsConfiguration['fixedPostVars'] = array_fill_keys($pages, 'docstypo3org_advanced_url');
            $fixedPostVarsConfiguration['fixedPostVars']['docstypo3org_advanced_url'] = array(
                array(
                    'GETvar' => 'tx_docstypo3org_manuals[controller]',
                    'noMatch' => 'bypass',
                ),
                array(
                    'GETvar' => 'tx_docstypo3org_manuals[action]',
                    'valueMap' => array(
                        'show' => 'show',
                    ),
                    'noMatch' => 'bypass',
                ),
                array(
                    'GETvar' => 'tx_docstypo3org_manuals[extension]',
                ),
                array(
                    'GETvar' => 'tx_docstypo3org_manuals[locale]',
                ),
                array(
                    'GETvar' => 'tx_docstypo3org_manuals[version]',
                ),
                array(
                    'GETvar' => 'tx_docstypo3org_manuals[document]',
                    'userFunc' => \Causal\Docstypo3org\Hooks\RealurlAutoConfiguration::class . '->decodeSpURL_getSequence',
                ),
            );
        }

        return $fixedPostVarsConfiguration;
    }

    /**
     * Returns the database connection.
     *
     * @return \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }

}
