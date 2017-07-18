<?php
declare(strict_types=1);
namespace TYPO3\CMS\Frontend\Routing;


class RoutingResult {

    /**
     * The database record of the site the request was routed to.
     *
     * @var array
     */
    private $site;

    /**
     * @var int
     */
    private $pageId;

    /**
     * @var int
     */
    private $languageId;

    /**
     * Hardcoded for now; I need to figure out a good concept for storing that info in the URL table without bloating it
     * too much…
     * @var int
     */
    private $type = 0;

    public function __construct(Site $site, int $pageId, int $languageId)
    {
        $this->site = $site;
        $this->pageId = $pageId;

        // leaving this in here as we have not yet finally decided if we keep the language coupled to the site
        $this->languageId = $languageId;
    }

    /**
     * @return array
     */
    public function getSite(): Site
    {
        return $this->site;
    }

    /**
     * @return int
     */
    public function getRootpageId(): int
    {
        return (int)$this->site->getRootpageId();
    }

    /**
     * @return int
     */
    public function getPageId(): int
    {
        return $this->pageId;
    }

    /**
     * @return int
     */
    public function getLanguageId(): int
    {
        return $this->languageId;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

}
