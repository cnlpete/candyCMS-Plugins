<?php

/**
 * This plugin gives users the opportunity to comment without registration.
 *
 * NOTE: This plugin slows down your page rapidly by sending a request to facebook each load!
 * If you don't need it, keep it disabled.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 * @license MIT
 * @since 2.0
 * @todo docs
 *
 */

namespace CandyCMS\Plugins;

use CandyCMS\Core\Helpers\AdvancedException;
use CandyCMS\Core\Helpers\Helper;
use CandyCMS\Core\Helpers\SmartySingleton;
use Facebook;

require_once PATH_STANDARD . '/vendor/facebook/facebook.php';

final class FacebookCMS extends Facebook {

  /**
   * Identifier for Template Replacements
   */
  const IDENTIFIER = 'facebook';

	/**
	 *
	 * @param type $sKey
	 * @return type
	 *
	 */
	public final function getUserData($sKey = '') {
		if ($this->getAccessToken()) {
			try {
				$iUid = $this->getUser();
				$aApiCall = array(
						'method' => 'users.getinfo',
						'uids' => $iUid,
						'fields' => 'uid, first_name, last_name, profile_url, pic, pic_square_with_logo, locale, email, website'
				);

				$aData = $this->api($aApiCall);
				return !empty($sKey) ? $aData[$sKey] : $aData;
			}
			catch (AdvancedException $e) {
				die($e->getMessage());
			}
		}
	}

	/**
	 *
	 * @param type $sUids
	 * @return type
	 *
	 */
	public final function getUserAvatar($sUids) {
		try {
			$aApiCall = array(
					'method' => 'users.getinfo',
					'uids' => $sUids,
					'fields' => 'pic_square_with_logo, profile_url'
			);

			return $this->api($aApiCall);
		}
		catch (AdvancedException $e) {
			die($e->getMessage());
		}
	}

	public final function show(&$aRequest, &$aSession) {
    $sTemplateDir   = Helper::getPluginTemplateDir('facebook', 'show');
    $sTemplateFile  = Helper::getTemplateType($sTemplateDir, 'show');

    $oSmarty = SmartySingleton::getInstance();
    $oSmarty->setTemplateDir($sTemplateDir);
    $oSmarty->setCaching(SmartySingleton::CACHING_LIFETIME_SAVED);

    $sCacheId = WEBSITE_MODE . '|plugins|' . WEBSITE_LOCALE . '|facebook';
    if (!$oSmarty->isCached($sTemplateFile, $sCacheId)) {
      $oSmarty->assign('PLUGIN_FACEBOOK_APP_ID', defined('PLUGIN_FACEBOOK_APP_ID')? PLUGIN_FACEBOOK_APP_ID : '');
      $oSmarty->assign('WEBSITE_LOCALE', WEBSITE_LOCALE);
    }

    return $oSmarty->fetch($sTemplateFile, $sCacheId);
	}
}