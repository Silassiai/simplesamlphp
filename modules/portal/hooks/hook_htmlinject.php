<?php

use Webmozart\Assert\Assert;

/**
 * Hook to inject HTML content into all pages...
 *
 * @param array &$hookinfo  hookinfo
 * @return void
 */
function portal_hook_htmlinject(array &$hookinfo)
{
    Assert::keyExists($hookinfo, 'pre');
    Assert::keyExists($hookinfo, 'post');
    Assert::keyExists($hookinfo, 'page');

    $links = ['links' => []];
    \SimpleSAML\Module::callHooks('frontpage', $links);

    Assert::isArray($links);

    $portalConfig = \SimpleSAML\Configuration::getOptionalConfig('module_portal.php');

    $allLinks = [];
    foreach ($links as $ls) {
        $allLinks = array_merge($allLinks, $ls);
    }

    $pagesets = $portalConfig->getValue('pagesets', [
        ['frontpage_welcome', 'frontpage_config', 'frontpage_auth', 'frontpage_federation'],
    ]);
    \SimpleSAML\Module::callHooks('portalextras', $pagesets);
    $portal = new \SimpleSAML\Module\portal\Portal($allLinks, $pagesets);

    if (!$portal->isPortalized($hookinfo['page'])) {
        return;
    }

    // Include jquery UI CSS files in header
    $hookinfo['jquery']['css'] = true;

    // Header
    $hookinfo['pre'][] = '<div id="portalmenu" class="ui-tabs ui-widget ui-widget-content ui-corner-all">' .
        $portal->getMenu($hookinfo['page']) .
        '<div id="portalcontent" class="ui-tabs-panel ui-widget-content ui-corner-bottom">';

    // Footer
    $hookinfo['post'][] = '</div></div>';
}
