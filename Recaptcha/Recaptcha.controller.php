<?php

/**
 * Recaptcha Plugin.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Hauke Schade <http://hauke-schade.de>
 * @license MIT
 * @since 2.0
 *
 */

namespace CandyCMS\Plugins;

use CandyCMS\Core\Helpers\Helper;
use CandyCMS\Core\Helpers\SmartySingleton;
use CandyCMS\Core\Helpers\I18n;

if (!defined('SHOW_CAPTCHA'))
  define('SHOW_CAPTCHA', MOBILE === false && WEBSITE_MODE !== 'test');

final class Recaptcha {

  /**
   * ReCaptcha public key.
   *
   * @access protected
   * @var string
   * @see app/config/Plugins.inc.php
   *
   */
  protected $_sPublicKey = PLUGIN_RECAPTCHA_PUBLIC_KEY;

  /**
   * ReCaptcha private key.
   *
   * @access protected
   * @var string
   * @see app/config/Plugins.inc.php
   *
   */
  protected $_sPrivateKey = PLUGIN_RECAPTCHA_PRIVATE_KEY;

  /**
   * ReCaptcha object.
   *
   * @var object
   * @access protected
   *
   */
  protected $_oResponse = '';

  /**
   * Provided ReCaptcha error message.
   *
   * @var string
   * @access protected
   *
   */
  protected $_sError = '';

  /**
   * Identifier for template replacements
   *
   * @var constant
   *
   */
  const IDENTIFIER = 'recaptcha';

  /**
   *
   * @var static
   * @access private
   *
   */
  private static $_oInstance = null;

  /**
   * Error Message of last captcha check
   *
   * @var string
   * @access private
   */
  private $_sErrorMessage = '';

  /**
   * Get the Smarty instance
   *
   * @static
   * @access public
   * @return object self::$_oInstance Recaptcha instance that was found or generated
   *
   */
  public static function getInstance() {
    if (self::$_oInstance === null)
      self::$_oInstance = new self();

    return self::$_oInstance;
  }

  /**
   * Include the needed lib.
   *
   * @access public
   *
   */
  public function __construct() {
    require PATH_STANDARD . '/vendor/recaptcha/recaptchalib.php';
  }

  /**
   * Get the HTML-Code for the Recaptcha form.
   *
   * @final
   * @access public
   * @param array $aRequest
   * @param array $aSession
   * @return string HTML
   *
   */
  public final function show(&$aRequest, &$aSession) {
    $sTemplateDir   = Helper::getPluginTemplateDir('recaptcha', 'recaptcha');
    $sTemplateFile  = Helper::getTemplateType($sTemplateDir, 'recaptcha');

    $oSmarty = SmartySingleton::getInstance();
    $oSmarty->addTemplateDir($sTemplateDir);

    # No caching for this very dynamic form
    $oSmarty->setCaching(SmartySingleton::CACHING_OFF);

    $oSmarty->assign('WEBSITE_MODE', WEBSITE_MODE);
    $oSmarty->assign('MOBILE', MOBILE);
    $oSmarty->assign('_captcha_', recaptcha_get_html($this->_sPublicKey, $this->_sError));

    if ($this->_sErrorMessage)
      $oSmarty->assign('_error_', $this->_sErrorMessage);

    return $oSmarty->fetch($sTemplateFile);
  }

  /**
   * Check if the entered captcha is correct.
   *
   * @final
   * @access public
   * @param array $aRequest
   * @return boolean status of recpatcha check
   *
   */
  public final function checkCaptcha(&$aRequest) {
    if (isset($aRequest['recaptcha_response_field'])) {
      $this->_oRecaptchaResponse = recaptcha_check_answer (
              $this->_sPrivateKey,
              $_SERVER['REMOTE_ADDR'],
              $aRequest['recaptcha_challenge_field'],
              $aRequest['recaptcha_response_field']);

      if ($this->_oRecaptchaResponse->is_valid) {
        $this->_sErrorMessage = '';
        return true;
      }

      else {
        $this->_sErrorMessage = I18n::get('error.captcha.incorrect');
        return Helper::errorMessage(I18n::get('error.captcha.incorrect'));
      }
    }
    else {
      $this->_sErrorMessage = I18n::get('error.captcha.loading');
      return Helper::errorMessage(I18n::get('error.captcha.loading'));
    }
  }
}