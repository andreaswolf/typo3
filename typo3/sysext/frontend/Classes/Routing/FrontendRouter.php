<?php
declare(strict_types=1);
namespace TYPO3\CMS\Frontend\Routing;

use Psr\Http\Message\RequestInterface;
use TYPO3\CMS\Frontend\Exception;

class FrontendRouter
{

    /**
     * @var SiteResolver
     */
    private $siteResolver;

    /**
     * @var UrlRepository
     */
    private $urlRepository;

    public function __construct(SiteResolver $siteResolver, UrlRepository $urlRepository)
    {
        $this->siteResolver = $siteResolver;
        $this->urlRepository = $urlRepository;
    }

    public function route(RequestInterface $request): RoutingResult
    {
        $siteRecord = $this->siteResolver->resolveSite($request);
        // remove the base path of the site from the request path; re-add the leading / as the base path also ends
        // with a slash TODO this should be made more robust, and probably moved to a method in Site
        $pathWithinSite = '/' . substr($request->getUri()->getPath(), strlen($siteRecord['basePath']));

        $urls = $this->urlRepository->findBySiteAndPath($siteRecord['uid'], $pathWithinSite);

        switch (count($urls)) {
            case 0:
                throw new Exception('No URL found for path ' . $pathWithinSite);

            case 1:
                $site = new Site($siteRecord);
                return new RoutingResult($site, (int)$urls[0]['target_uid'], (int)$siteRecord['language']);

            case 2:
                throw new Exception('Multiple URLs found for path');
        }
    }

}
