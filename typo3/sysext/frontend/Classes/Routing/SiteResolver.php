<?php
declare(strict_types=1);
namespace TYPO3\CMS\Frontend\Routing;

use Psr\Http\Message\RequestInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\StringUtility;
use TYPO3\CMS\Frontend\Exception;

class SiteResolver
{

    /**
     * @var SiteRepository
     */
    private $siteRepository;

    public function __construct()
    {
        $this->siteRepository = GeneralUtility::makeInstance(SiteRepository::class);
    }

    public function resolveSite(RequestInterface $request): array
    {
        $hostname = $request->getUri()->getHost();

        $candidateSites = $this->siteRepository->findByHostname($hostname);

        $sitesWithCorrectBasePath = array_filter($candidateSites, function($siteRecord) use ($request) {
            $sitePathPrefix = $siteRecord['basePath'];

            return StringUtility::beginsWith($request->getUri()->getPath(), $sitePathPrefix);
        });

        switch (count($sitesWithCorrectBasePath)) {
            case 0:
                throw new Exception('No site found for URL ' . $request->getUri()->__toString());

            case 1:
                return $sitesWithCorrectBasePath[0];

            default:
                throw new Exception('Found more than one site for URL ' . $request->getUri()->__toString());
        }
    }

}
