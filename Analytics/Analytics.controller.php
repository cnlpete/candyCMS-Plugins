<?php

/**
 * Cache analytics code.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 * @license MIT
 * @since 2.0
 *
 */

namespace candyCMS\Plugins;

use candyCMS\Core\Helpers\SmartySingleton as Smarty;

final class Analytics {

  /**
   * Identifier for Template Replacements
   *
   * @var constant
   *
   */
  const IDENTIFIER = 'Analytics';

  /**
   * @var array
   * @access protected
   *
   */
  protected $_aRequest;

  /**
   * @var array
   * @access protected
   *
   */
  protected $_aSession;

  /**
   * Initialize the plugin and register all needed events.
   *
   * @access public
   * @param array $aRequest alias for the combination of $_GET and $_POST
   * @param array $aSession alias for $_SESSION
   * @param object $oPlugins the PluginManager
   *
   */
  public function __construct(&$aRequest, &$aSession, &$oPlugins) {
    $this->_aRequest  = & $aRequest;
    $this->_aSession  = & $aSession;

    # now register some events with the pluginmanager
    $oPlugins->registerSimplePlugin($this);
  }

  /**
   * @final
   * @access public
   * @return string HTML content
   *
   */
  public final function show() {
    $oTemplate = Smarty::getTemplate(self::IDENTIFIER, 'show', true);

    $oSmarty = Smarty::getInstance();
    $oSmarty->setTemplateDir($oTemplate);
    $oSmarty->setCaching(Smarty::CACHING_LIFETIME_SAVED);

    $sCacheId = WEBSITE_MODE . '|' . WEBSITE_LOCALE . '|plugins|' . self::IDENTIFIER;
    if (!$oSmarty->isCached($oTemplate, $sCacheId)) {
      $oSmarty->assign('WEBSITE_MODE', WEBSITE_MODE);
      $oSmarty->assign('PLUGIN_ANALYTICS_TRACKING_CODE', PLUGIN_ANALYTICS_TRACKING_CODE);
    }

    return $oSmarty->fetch($oTemplate, $sCacheId);
  }
}
