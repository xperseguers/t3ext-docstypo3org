<?php
namespace Causal\Docstypo3org\Hooks;

class Restdoc
{

    /**
     * Post-processes the body to add dynamic links to TSref.
     *
     * @param array $params
     */
    public function postProcessBody(array $params)
    {
        $remoteUrl = 'https://docs.typo3.org/typo3cms/TyposcriptReference/';
        $references = \Causal\Sphinx\Utility\MiscUtility::getIntersphinxReferences(
            'typo3cms.references.t3tsref',
            '',
            $remoteUrl
        );
        $keywords = [];
        foreach ($references as $chapter => $anchors) {
            foreach ($anchors as $anchor) {
                if ($anchor['type'] === 'std:label') {
                    $keywords[$anchor['title']] = $remoteUrl . str_replace('#$', '#' . $anchor['name'], $anchor['link']);
                }
            }
        }

        $replacements = [];
        foreach ($keywords as $keyword => $link) {
            if (in_array($keyword, ['TEMPLATE', 'TEXT', 'typolink'])) {
                $replacements[htmlspecialchars($keyword)] = '<a href="' . $link . '">' . htmlspecialchars($keyword) . '</a>';
            }
        }

        $params['content'] = str_replace(
            array_keys($replacements),
            array_values($replacements),
            $params['content']
        );
    }

}
