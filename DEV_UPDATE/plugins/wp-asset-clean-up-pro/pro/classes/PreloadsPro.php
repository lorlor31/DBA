<?php
namespace WpAssetCleanUpPro;

use WpAssetCleanUp\Preloads;

/**
 * Class PreloadsPro
 * @package WpAssetCleanUpPro
 */
class PreloadsPro
{
	/**
	 *
	 */
	public function init()
	{
	    add_filter('wpacu_wpfc_update_deferred_css_links', array($this, 'wpfcUpdateDeferredCssLinks'));
	}

	/**
     * In case "Minify CSS" is enabled in WP Fastest Cache,
     * make sure the deferred Asset CleanUp Pro's CSS links (from BODY)
     * are also updated with the minified version
     *
	 * @param $buffer
	 *
	 * @return mixed
	 */
	public static function wpfcUpdateDeferredCssLinks($buffer)
    {
    	if (Preloads::preventPreload()) {
    		return $buffer;
	    }

	    preg_match_all('#<link[^>]*preload[^>]*' . 'data-href-before=([\'"])(.*)([\'"])'.'*'. 'href=([\'"])(.*)([\'"])' . '.*(>)#Usmi', $buffer, $matchesSourcesFromLinkTags, PREG_SET_ORDER);

	    if (! empty($matchesSourcesFromLinkTags)) {
		    $toLaterClear = array();

		    foreach ($matchesSourcesFromLinkTags as $linkTagArray) {
			    $linkHrefBefore = isset($linkTagArray[2]) ? trim($linkTagArray[2], '"\' ') : false;
			    $linkHref = isset($linkTagArray[5]) ? trim($linkTagArray[5]) : false;

			    // Do the replacement for the deferred CSS
			    $buffer = str_replace("'".$linkHrefBefore."';", "'".$linkHref."';", $buffer);

			    $toLaterClear[] = "data-href-before='".$linkHrefBefore."'";
		    }

		    $buffer = str_replace($toLaterClear, '', $buffer);
	    }

	    return $buffer;
    }
}
