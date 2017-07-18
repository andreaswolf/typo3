<?php

namespace TYPO3\CMS\Frontend\Backend;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Frontend\Routing\SiteRepository;
use TYPO3\CMS\Frontend\Routing\UrlRepository;

class UrlManagementWidget
{

    /**
     * @var StandaloneView
     */
    private $view;


    public function __construct()
    {
        $this->view = GeneralUtility::makeInstance(StandaloneView::class);
        $this->view->setTemplateRootPaths([
            ExtensionManagementUtility::extPath('frontend') . '/Resources/Private/Templates/'
        ]);
    }


    public function render()
    {
        /** @var \TYPO3\CMS\Backend\Controller\PageLayoutController $pageLayoutController */
        $pageLayoutController = $GLOBALS['SOBE'];

        if ($pageLayoutController instanceof \TYPO3\CMS\Backend\Controller\PageLayoutController
            && (int) $pageLayoutController->id > 0
        ) {
            $language = (int)$pageLayoutController->current_sys_language;
            $sites = $this->getSiteRepository()->findByLanguage($language);
            $siteIds = array_map(function($site) {
                return (int)$site['uid'];
            }, $sites);

            $urls = $this->getUrlRepository()->findByTarget('pages', $pageLayoutController->id);
            $urls = array_map(function($url) use ($sites) {
                // replace the site ID by the full site record
                $url['site'] = $sites[$url['site']];
                $url['fullPath'] = rtrim($url['site']['basePath'], '/') . $url['path'];
                return $url;
            }, $urls);

            $urlsForCurrentLanguage = array_filter($urls, function($url) use ($siteIds) {
                return in_array((int)$url['site']['uid'], $siteIds);
            });

            $this->view->assign('urls', $urlsForCurrentLanguage);

            return $this->view->render('UrlList');
        }
    }

    private function getSiteRepository(): SiteRepository
    {
        return GeneralUtility::makeInstance(SiteRepository::class);
    }

    private function getUrlRepository(): UrlRepository
    {
        return GeneralUtility::makeInstance(UrlRepository::class);
    }
}
