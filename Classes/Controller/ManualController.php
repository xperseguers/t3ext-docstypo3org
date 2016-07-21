<?php
namespace Causal\Docstypo3org\Controller;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

class ManualController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

    protected $basePath = PATH_site . 'documents/extensions/';

    protected $extension;
    protected $version;
    protected $locale;

    /**
     * List action.
     *
     * @param string $selectedExtension
     * @param string $selectedLocale
     */
    public function listAction($selectedExtension = '', $selectedLocale = '')
    {
        $extensions = [];
        $isPartial = false;

        if (!empty($selectedExtension)) {
            $path = PathUtility::getCanonicalPath($this->basePath . $selectedExtension);
            if (GeneralUtility::isFirstPartOfStr($path, $this->basePath)) {
                if (is_dir($path)) {
                    $extensions = [
                        $selectedExtension => [],
                    ];
                    $isPartial = true;
                }
            }
        }
        if (empty($extensions)) {
            $extensions = array_flip(GeneralUtility::get_dirs($this->basePath));
        }

        foreach ($extensions as $extension => $_) {
            $extensionPath = $this->basePath . $extension . '/';
            $locales = GeneralUtility::get_dirs($extensionPath);
            if (!empty($selectedLocale) && in_array($selectedLocale, $locales)) {
                $locales = [
                    $selectedLocale
                ];
            }
            foreach ($locales as $locale) {
                $localePath = $extensionPath . $locale . '/';
                $versions = GeneralUtility::get_dirs($localePath);
                foreach ($versions as $version) {
                    if (!is_array($extensions[$extension])) {
                        $extensions[$extension] = [];
                    }
                    if (!isset($extensions[$extension][$version])) {
                        $extensions[$extension][$version] = [];
                    }
                    $extensions[$extension][$version][] = $locale;
                }
            }
        }

        $this->view->assignMultiple([
            'extensions' => $extensions,
            'partial' => $isPartial,
        ]);
    }

    /**
     * @param string $extension
     * @param string $version
     * @param string $locale
     * @param string $document
     */
    public function showAction($extension = '', $version = '', $locale = '', $document = '')
    {
        if (empty($extension)) {
            $this->redirect('list');
        }
        if (empty($version) || empty($locale)) {
            $this->forward('list', null, null, [
                'selectedExtension' => $extension,
                'selectedLocale' => $locale,
            ]);
        }

        $this->extension = $extension;
        $this->version = $version;
        $this->locale = $locale;

        /** @var \Causal\Restdoc\Reader\SphinxJson $sphinxReader */
        $sphinxReader = GeneralUtility::makeInstance(\Causal\Restdoc\Reader\SphinxJson::class);
        $sphinxReader
            ->setKeepPermanentLinks(false)
            ->setDefaultFile('Index')
            ->enableDefaultDocumentFallback();

        if (empty($document)) {
            $document = $sphinxReader->getDefaultFile();
        }

        $path = $this->basePath . $extension . '/' . $locale . '/' . $version . '/';
        $sphinxReader
            ->setPath($path)
            ->setDocument(rtrim($document, '/') . '/')
            ->load();

        /** @var \Causal\Sphinx\Domain\Model\Documentation $documentation */
        $documentation = GeneralUtility::makeInstance(\Causal\Docstypo3org\Domain\Model\Documentation::class, $sphinxReader);
        $documentation->setCallbackLinks(array($this, 'getLink'));
        $documentation->setCallbackImages(array($this, 'processImage'));

        $this->view->assignMultiple([
            'documentation' => $documentation
        ]);
    }

    /**
     * Generates a link to navigate within a reST documentation project.
     *
     * @param string $document Target document
     * @param boolean $absolute Whether absolute URI should be generated
     * @param integer $rootPage UID of the page showing the documentation
     * @return string
     * @throws \RuntimeException
     * @private This method is made public to be accessible from a lambda-function scope
     */
    public function getLink($document, $absolute = false, $rootPage = 0)
    {
        static $basePath = null;

        $anchor = '';
        if ($document !== '') {
            if (($pos = strrpos($document, '#')) !== false) {
                $anchor = substr($document, $pos + 1);
                $document = substr($document, 0, $pos);
            }
        }
        $link = $this->uriBuilder->uriFor(
            'show',
            array(
                'extension' => $this->extension,
                'version' => $this->version,
                'locale' => $this->locale,
                'document' => $document
            )
        );

        // Forward slash is used as separator
        // it is encoded as %2F and should be decoded
        $link = rtrim(str_replace('%2F', '/', $link), '/') . '/';

        switch (true) {
            case $anchor !== '':
                $link .= '#' . $anchor;
                break;
            case substr($document, 0, 11) === '_downloads/':
            case substr($document, 0, 8) === '_images/':
            case substr($document, 0, 9) === '_sources/':
                $link = 'not yet supported';
                break;
        }
        return $link;
    }

    /**
     * Processes an image.
     *
     * @param array $data Image information
     * @return string HTML image tag
     * @private This method is made public to be accessible from a lambda-function scope
     */
    public function processImage(array $data)
    {
        $fixedHeight = !empty($data['style']) && preg_match('/height/', $data['style']);
        if (!$fixedHeight) {
            $image = GeneralUtility::getFileAbsFileName($data['src']);
            if (is_file($image)) {
                $info = getimagesize($image);
                $data['style'] = 'max-width:' . $info[0] . 'px;' . (!empty($data['style']) ? $data['style'] : '');
            }
        }

        $tag = '<img src="/' . htmlspecialchars($data['src']) . '"';
        $tag .= ' alt="' . (!empty($data['alt']) ? htmlspecialchars($data['alt']) : '') . '"';

        // Styling
        $classes = array();
        if (!empty($data['class'])) {
            $classes = explode(' ', $data['class']);
        }
        if (!$fixedHeight) {
            $classes[] = 'img-responsive';    // From standard TYPO3 theme
        }
        if (count($classes) > 0) {
            $tag .= ' class="' . htmlspecialchars(implode(' ', array_unique($classes))) . '"';
        }
        //if (!empty($data['style'])) {
        //    $tag .= ' style="' . htmlspecialchars($data['style']) . '"';
        //}

        $tag .= ' />';
        return $tag;
    }

}
