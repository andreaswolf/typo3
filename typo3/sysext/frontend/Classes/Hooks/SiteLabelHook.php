<?php

namespace TYPO3\CMS\Frontend\Hooks;

class SiteLabelHook
{

    public function renderLabel(&$params, $pObj)
    {
        $params['title'] = $params['row']['domainName'] . $params['row']['basePath'];
    }

}
