<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\WAIPortal;

use Piwik\Filesystem;
use Piwik\Option;
use Piwik\Plugin;
use Piwik\Plugins\CoreAdminHome\CustomLogo;
use Piwik\View;

/**
 * WAI Portal theme plugin.
 */
class WAIPortal extends Plugin
{
    /**
     * The base plugin path.
     *
     * @var string the path
     */
    const PLUGIN_PATH = 'plugins/WAIPortal';

    /**
     * Socials list
     *
     * @var mixed the list
     */
    private $socials;

    /**
     * Footer list
     *
     * @var mixed the list
     */
    private $footerLinks;

    /**
     * WAIPortal constructor.
     * @param string|bool $pluginName A plugin name to force. If not supplied, it is set
     *                                to the last part of the class name.
     * @throws \Exception If plugin metadata is defined in both the getInformation() method
     *                    and the **plugin.json** file.
     */
    public function __construct($pluginName = false)
    {
        parent::__construct($pluginName);
        $this->socials = json_decode(@file_get_contents(self::PLUGIN_PATH . '/data/socials.json'), true);
        $this->footerLinks = json_decode(@file_get_contents(self::PLUGIN_PATH . '/data/footer_links.json'), true);
    }

    /**
     * Register observer to events.
     *
     * @return array the list of events with associated observer
     */
    public function registerEvents() {
        return array(
            'Template.beforeTopBar' => 'handleAddTopBar',
            'Template.header' => 'handleTemplateHeader',
            'Template.pageFooter' => 'handleTemplatePageFooter',
            'Template.loginNav' => 'handleLoginNav',
        );
    }

    /**
     * Handle "before adding top bar" event.
     *
     * @param string $outstring the HTML string to modify
     * @param string $page the current location
     * @param string|null $userLogin the current user login
     * @param string|null $topMenu the top menu
     */
    public function handleAddTopBar(&$outstring, $page, $userLogin = null, $topMenu = null)
    {
        if ('login' === $page) {
            $outstring .= $this->handleTemplateHeader($outstring);
        } else {
            $outstring .= (new View('@WAIPortal/itSiteName'))->render();
        }
    }

    /**
     * Handle "add header" event.
     *
     * @param string $outstring the HTML string to modify
     */
    public function handleTemplateHeader(&$outstring)
    {
        $view = new View('@WAIPortal/itHeader');
        $view->pluginPath = self::PLUGIN_PATH;
        $settings = new SystemSettings();
        $view->waiUrl = $settings->waiUrl->getValue();
        $outstring .= $view->render();
    }

    /**
     * Handle "add page page footer" event.
     *
     * @param string $outstring the HTML string to modify
     */
    public function handleTemplatePageFooter(&$outstring)
    {
        $view = new View('@WAIPortal/itFooter');
        $view->socials = $this->socials['links'];
        $view->links = $this->footerLinks['links'];
        $settings = new SystemSettings();
        $view->waiUrl = $settings->waiUrl->getValue();
        $view->pluginPath = self::PLUGIN_PATH;
        $outstring .= $view->render();
    }

    /**
     * Handle "add navigation bar to login page" event.
     *
     * @param string $outstring the HTML string to modify
     * @param string $position "top" to signal initial inserting before default content,
     *                         "bottom" ti signal inserting after default content
     */
    public function handleLoginNav(&$outstring, $position)
    {
        if ('bottom' === $position) {
            $outstring .= '</div>';

            return;
        }

        $outstring .= '<div class="hide">';
    }

    /**
     * Manage plugin activation.
     */
    public function activate() {
        @copy(PIWIK_DOCUMENT_ROOT . '/plugins/WAIPortal/images/logo.png', PIWIK_DOCUMENT_ROOT . '/' . CustomLogo::getPathUserLogo());
        @copy(PIWIK_DOCUMENT_ROOT . '/plugins/WAIPortal/svg/logo.svg', PIWIK_DOCUMENT_ROOT . '/' . CustomLogo::getPathUserSvgLogo());
        @copy(PIWIK_DOCUMENT_ROOT . '/plugins/WAIPortal/icons/favicon-32x32.png', PIWIK_DOCUMENT_ROOT . '/' . CustomLogo::getPathUserFavicon());
        Option::set('branding_use_custom_logo', '1', true);
    }

    /**
     * Manage plugin deactivation.
     */
    public function deactivate() {
        Filesystem::remove(PIWIK_DOCUMENT_ROOT . '/' . CustomLogo::getPathUserLogo());
        Filesystem::remove(PIWIK_DOCUMENT_ROOT . '/' .CustomLogo::getPathUserSvgLogo());
        Filesystem::remove(PIWIK_DOCUMENT_ROOT . '/' .CustomLogo::getPathUserFavicon());
        Option::set('branding_use_custom_logo', '0', true);
    }
}
