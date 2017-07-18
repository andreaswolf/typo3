<?php
declare(strict_types=1);
namespace TYPO3\CMS\Frontend\Routing;

class Site
{

    /**
     * @var array
     */
    private $record;

    public function __construct($record)
    {
        $this->record = $record;
    }

    public function getRootpageId()
    {
        return (int)$this->record['pid'];
    }

}
