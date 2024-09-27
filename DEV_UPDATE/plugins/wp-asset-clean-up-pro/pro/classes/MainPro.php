<?php
namespace WpAssetCleanUpPro;

use WpAssetCleanUp\Main;
use WpAssetCleanUp\MainFront;
use WpAssetCleanUp\Menu;
use WpAssetCleanUp\Misc;
use WpAssetCleanUp\ObjectCache;
use WpAssetCleanUp\OptimiseAssets\OptimizeCommon;
use WpAssetCleanUp\Preloads;

/**
 * Class MainPro
 * @package WpAssetCleanUpPro
 */
class MainPro
{
	/**
     * These are special unloads that once applied, it could take effect on more than one page
     *
	 * @var array[]
	 */
	public static $unloads = array(
        // e.g. Unload CSS/JS if the URI matches a specific RegEx
        'regex' => array(
            // Values saved
            'styles'              => array(),
            'scripts'             => array(),
            '_set'                => false,

            // Any matches for the current URL? It will contain the list of handles
            'current_url_matches' => array( 'styles' => array(), 'scripts' => array() )
        ),

        // e.g. Unload CSS/JS on all post types that have a specific taxonomy (e.g. 'category')
        'post_type_via_tax' => array(
	        // Values saved
	        'styles'               => array(),
	        'scripts'              => array(),
            '_set'                 => false,

	        // Any matches for the current post? It will contain the list of handles
	        'current_post_matches' => array( 'styles' => array(), 'scripts' => array() )
        ),

        // e.g. Unload CSS/JS on all "category" pages, e.g. /category/food/
        'tax' => array(
            // Values saved
            'styles'               => array(),
            'scripts'              => array(),
            // '_set' is not relevant here as the function is called several times and returns different results
            //'_set'                 => false,
            // 'current_matches' --> this key is not needed as the values for the targeted taxonomy are built directly in "styles" and "scripts" (no extra check needed)
        ),

        // e.g. Unload CSS/JS on all author archive pages, e.g. /author/[any_author_here]/
        'author' => array(
            // Values saved
            'styles'               => array(),
            'scripts'              => array(),
            // '_set' is not relevant here as the function is called several times and returns different results
            //'_set'                 => false,
            // 'current_matches' --> this key is not needed as the values for the targeted taxonomy are built directly in "styles" and "scripts" (no extra check needed)
        ),
	);

	/**
     * These are special load exceptions that once applied, it could take effect
     * on more than one page by cancelling any unload rule set for that handle
     *
	 * @var array[]
	 */
	public static $loadExceptions = array(
        'regex' => array(
	        // Values saved
	        'styles'              => array(),
	        'scripts'             => array(),
            '_set'                => false,

	        // Any matches for the current URL? It will contain the list of handles
	        'current_url_matches' => array( 'styles' => array(), 'scripts' => array() )
        ),

        'post_type_via_tax' => array(
	        // Values saved
	        'styles'               => array(),
	        'scripts'              => array(),
            // '_set' is not relevant here as the function is called several times and returns different results
            //'_set'                 => false,

	        // Any matches for the current post? It will contain the list of handles
	        'current_post_matches' => array( 'styles' => array(), 'scripts' => array() )
        ),

        // e.g. a site-wide unloading rule could be set, but a load exception can be set
        // whenever a specific (e.g. 'category') taxonomy page is visited (e.g. URL could be like /category/food/)
        'tax' => array(
            // Values saved
            'styles'               => array(),
            'scripts'              => array(),
            // '_set' is not relevant here as the function is called several times and returns different results
            //'_set'                 => false,
            // 'current_matches' --> this key is not needed as the values for the targeted taxonomy are built directly in "styles" and "scripts" (no extra check needed)
        ),

        // e.g. a site-wide unloading rule could be set, but a load exception can be set
        // whenever any author archive page is visited, e.g. /author/[any_author_here]
        'author' => array(
            // Values saved
            'styles'               => array(),
            'scripts'              => array(),
            // '_set' is not relevant here as the function is called several times and returns different results
            //'_set'                 => false,
            // 'current_matches' --> this key is not needed as the values for the targeted taxonomy are built directly in "styles" and "scripts" (no extra check needed)
        ),
    );

    /**
     * @var array[]
     */
    public static $mediaQueryLoad = array('styles' => array(), 'scripts' => array());

	/**
	 * @var bool
	 */
	public $isTaxonomyEditPage = false;

	/**
	 * @var array
	 */
	public $asyncScripts = array();

	/**
	 * @var array
	 */
	public $deferScripts = array();

    /**
     * It refers to script attributes applied "on this page"
     *
     * @var array
     */
    public $onThisPageScriptsAttributes = array('async' => array(), 'defer' => array());

	/**
     * It refers to script attributes applied "everywhere"
     *
	 * @var array
	 */
	public $globalScriptsAttributes = array();

	/**
	 * @var bool
	 */
	public $scriptsAttributesChecked = false;

	/**
     * It refers to all script attributes that will be aplied on the current page (both "on this page" and "everywhere")
     *
	 * @var array
	 */
	public $scriptsAttrsToApplyOnCurrentPage = array('async' => array(), 'defer' => array());

	/**
     * "not here (exception)" option
	 * @var array
	 */
	public $onThisPageScriptsAttrsNoLoad = array('async' => array(), 'defer' => array());

	/**
	 *
	 */
	public function init()
	{
		$this->fallbacks();

		if ( ! wpacuIsDefinedConstant('WPACU_NO_POSITIONS_CHANGED_FOR_ASSETS') && ! is_admin() ) {
            $assetsPositions = self::getAssetsPositions();
            if ( ! empty($assetsPositions['styles']) || ! empty($assetsPositions['scripts'])) {
                if ( ! Main::instance()->isGetAssetsCall && ! is_admin()) {
                    add_action('init', static function () {
                        PositionsPro::setSignatures();
                    }, 20);
                }

                $positionsClass = new PositionsPro();
                $positionsClass->init();
            }
        }

		// "Per Page" Unloaded Assets
		add_filter('wpacu_pro_get_assets_unloaded', array($this, 'getAssetsUnloadedPageLevel'));
		add_filter('wpacu_pro_get_bulk_unloads',    array($this, 'getBulkUnloads'));

		// This filter appends to the existing "all unloaded" list, assets belonging to the is_tax(), is_author() etc. group
		// This way, they will PRINT to the list of unloaded assets for management
		add_filter('wpacu_filter_styles_list_unload',          array($this, 'filterAssets'));
		add_filter('wpacu_filter_styles_list_load_exception',  array($this, 'filterAssets'));

		add_filter('wpacu_filter_scripts_list_unload',         array($this, 'filterAssets'));
		add_filter('wpacu_filter_scripts_list_load_exception', array($this, 'filterAssets'));

		add_filter('wpacu_object_data',                        array($this, 'wpacuObjectData'));


		if (wpacuIsDefinedConstant('WPACU_ALLOW_ONLY_UNLOAD_RULES')) {
		    return; // stop here, do not do any alteration to the LINK/SCRIPT tags as only the unloading rules are allowed
        }

		// Only valid for front-end pages
		if (! is_admin()) {
			add_filter('style_loader_tag', array($this, 'styleLoaderTag'), 10, 2);

			// Add async, defer (if selected) for the loaded scripts
			add_filter('script_loader_tag', array($this, 'scriptLoaderTag'), 10, 2);
		}
	}

	/**
	 * @param $assetsRemoved
	 *
	 * @return mixed|string
	 */
	public function getAssetsUnloadedPageLevel($assetsRemoved)
	{
	    $bulkType = false;

	    /*
		 * NOTE: This list only includes assets that are unloaded on a page level
		 * A 404 page will have the same unloaded assets, as it returns a 404 response (no matter which URL is requested)
		*/

		/*
		 * [START] DASHBOARD VIEW ONLY
		 */
            if (isset($_REQUEST['tag_id']) && is_admin() && Main::instance()->settings['dashboard_show']) {
                // The "tag_id" value is sent to the AJAX call (it's not the same as 'tag_ID' from the URL of the page)
                $termId = (int)$_REQUEST['tag_id'];
                return get_term_meta($termId, '_' . WPACU_PLUGIN_ID . '_no_load', true);
            }
		/*
		 * [END] DASHBOARD VIEW ONLY
		 */

		/*
		 * [START] FRONT-END VIEW ONLY
		 */
		/*
		  *
		  * Possible pages:
		  *
		  * 404 Page: Not Found (applies to any non-existent request)
		  * Default WordPress Search Page: Applies to any search request
		  * Date Archive Page: Applies to any date
		 *
		*/
		if ( is_404() || Main::isWpDefaultSearchPage() || is_date() || self::isCustomPostTypeArchivePage() ) {
			$bulkUnloadJson = get_option( WPACU_PLUGIN_ID . '_bulk_unload' );

			@json_decode( $bulkUnloadJson );

			if ( empty( $bulkUnloadJson ) || ! (wpacuJsonLastError() === JSON_ERROR_NONE ) ) {
				return $assetsRemoved;
			}

			$bulkUnload = json_decode( $bulkUnloadJson, true );

			if (is_404()) {
				$bulkType = '404';     // 404 (Not Found) WordPress page (located in 404.php)
			} elseif (Main::isWpDefaultSearchPage()) {
				$bulkType = 'search';  // Default WordPress Search Page
			} elseif (is_date()) {
				$bulkType = 'date';    // Show posts by date page
			} elseif ($customPostTypeObj = self::isCustomPostTypeArchivePage()) {
			    $bulkType = 'custom_post_type_archive_' . $customPostTypeObj->name;
            }

			if (! $bulkType) {
				// Shouldn't reach this; it's added just in case there's any conditional missing above
				return $assetsRemoved;
			}

			return wp_json_encode( array(
				'styles'  => isset($bulkUnload['styles'][$bulkType])  ? $bulkUnload['styles'][$bulkType]  : array(),
				'scripts' => isset($bulkUnload['scripts'][$bulkType]) ? $bulkUnload['scripts'][$bulkType] : array()
			) );
		}

		// Taxonomy and Author pages check (Front-end View)
		if ( MainFront::isAnyTaxPage() || is_author() ) {
			global $wp_query;
			$object = $wp_query->get_queried_object();

            /*
             * Taxonomy page: Could be 'category' (Default WordPress taxonomy), 'product_cat', 'post_tag' (for the tag page) etc.
            */
			if ( isset( $object->taxonomy, $object->term_id ) || MainFront::isAnyTaxPage() ) {
				return get_term_meta($object->term_id, '_' . WPACU_PLUGIN_ID . '_no_load', true);
			}

            /*
             * Author page (individual, not for all authors)
             */
			if ( is_author() ) {
				$authorId = self::getAuthorIdOnAuthorArchivePage(__FILE__, __LINE__);

                if ($authorId !== null) {
	                return get_user_meta( $authorId, '_' . WPACU_PLUGIN_ID . '_no_load', true );
                }
			}
        }
		/*
		 * [END] FRONT-END VIEW ONLY
		 */

		return $assetsRemoved;
	}

	/**
	 * @param $fromFile
	 * @param $fromLine
	 *
	 * @return string|null
	 */
	public static function getAuthorIdOnAuthorArchivePage($fromFile, $fromLine)
    {
	    $authorId = null;

	    if ( is_author() ) {
		    global $wp_query;
		    $object = $wp_query->get_queried_object();

		    if (isset($object->data->ID) && $object->data->ID) {
			    $authorId = $object->data->ID;
		    } elseif (function_exists('get_the_author_meta')) {
			    $authorId = get_the_author_meta('ID');
		    }

		    if ($authorId === null) {
			    error_log(WPACU_PLUGIN_TITLE . ': Error detecting the author ID when visiting an author archive page (you can raise a ticket about this to the support team) / File: '.$fromFile.' / Line: '.$fromLine);
		    }
        }

        return $authorId;
    }

	/**
     * Get bulk unloads for taxonomy and author pages
     *
	 * @param array $data (possible values: "post_type_via_tax" or "tax_and_author")
	 *
	 * @return array
	 */
	public function getBulkUnloads($data = array())
	{
		if ( ! isset($data['fetch']) ) {
            $data['fetch'] = 'tax_and_author'; // default
        }

	    if ( $data['fetch'] === 'tax_and_author' ) {
		    global $wp_query;

		    $object = $wp_query->get_queried_object();

		    if ( isset( $object->taxonomy ) && ( ! is_admin() ) ) {
			    // Front-end View
			    $data['is_bulk_unloadable']        = true;
			    $data['bulk_unloaded']['taxonomy'] = Main::instance()->getBulkUnload( 'taxonomy', $object->taxonomy );
			    $data['bulk_unloaded_type']        = 'taxonomy';
		    } elseif ( isset( $_REQUEST['wpacu_taxonomy'] ) && Main::instance()->settings['dashboard_show'] && is_admin() ) {
			    // Dashboard View
			    $data['is_bulk_unloadable']        = true;
			    $data['bulk_unloaded']['taxonomy'] = Main::instance()->getBulkUnload( 'taxonomy', $_REQUEST['wpacu_taxonomy'] );
			    $data['bulk_unloaded_type']        = 'taxonomy';
		    } elseif ( is_author() ) {
			    // Only in front-end view
			    $data['is_bulk_unloadable']      = true;
			    $data['bulk_unloaded']['author'] = Main::instance()->getBulkUnload( 'author' );
			    $data['bulk_unloaded_type']      = 'author';
		    }
	    } elseif ( $data['fetch'] === 'post_type_via_tax' ) {
		    $data['is_bulk_unloadable']                 = true;
		    $data['bulk_unloaded']['post_type_via_tax'] = Main::instance()->getBulkUnload( 'post_type_via_tax', $data['post_type'] );
		    $data['bulk_unloaded_type']                 = 'post_type_via_tax';
        }

		return $data;
	}

	/**
	 * Case 1: UNLOAD style/script (based on the handle) for URLs matching a specified RegExp
	 * Case 2: LOAD (make an exception) style/script (based on the handle) for URLs matching a specified RegExp
	 *
	 * @param $for ("unloads" or "load_exceptions")
	 *
	 * @return array
	 */
	public static function getRegExRules($for)
	{
        if ($for === 'unloads' && self::$unloads['regex']['_set']) {
            return self::$unloads['regex'];
        }

        if ($for === 'load_exceptions' && self::$loadExceptions['regex']['_set']) {
			return self::$loadExceptions['regex'];
		}

		$regExes = array('styles' => array(), 'scripts' => array());

        // No RegEx rules set for any assets; stop here
        if (wpacuIsDefinedConstant('WPACU_NO_REGEX_RULES_SET_FOR_ASSETS')) {
            if ($for === 'unloads') {
                self::$unloads['regex']         = $regExes;
                self::$unloads['regex']['_set'] = true;

                return self::$unloads['regex'];
            }

            self::$loadExceptions['regex']         = $regExes;
            self::$loadExceptions['regex']['_set'] = true;

            return self::$loadExceptions['regex'];
        }

		// DB Key (how it's saved in the database)
		if ($for === 'load_exceptions') {
			$globalKey = 'load_regex';
		} else {
			$globalKey = 'unload_regex';
		}

        $regExDbList = wpacuGetGlobalData();

		if ( ! empty($regExDbList) ) {
			// Are there any load exceptions / unload RegExes?
			foreach (array('styles', 'scripts') as $assetKey) {
				if ( ! empty( $regExDbList[$assetKey][$globalKey] ) ) {
					$regExes[$assetKey] = $regExDbList[$assetKey][$globalKey];
				}
			}
		}

		if ($for === 'unloads') {
			self::$unloads['regex']         = $regExes;
			self::$unloads['regex']['_set'] = true;

			return self::$unloads['regex'];
		}

        // If not 'unloads', then 'load_exceptions'
        self::$loadExceptions['regex']         = $regExes;
        self::$loadExceptions['regex']['_set'] = true;

        return self::$loadExceptions['regex'];
	}

	/**
	 * @param $list
	 *
	 * @return array
	 */
	public function filterAssets($list)
	{
        $keyToCheck = 'pro_'.current_filter();

        if (isset($GLOBALS[$keyToCheck])) {
            return $GLOBALS[$keyToCheck];
        }

	    // [unload list]
	    if (current_filter() === 'wpacu_filter_styles_list_unload') {
		    $list = $this->filterAssetsUnloadList($list, 'styles', 'unload');
        }
		elseif (current_filter() === 'wpacu_filter_scripts_list_unload') {
			$list = $this->filterAssetsUnloadList($list, 'scripts', 'unload');
		}
		// [/unload list]

		// [load exception list]
        elseif (current_filter() === 'wpacu_filter_styles_list_load_exception') {
	        $list = $this->filterAssetsUnloadList($list, 'styles', 'load_exception');
		}
		elseif (current_filter() === 'wpacu_filter_scripts_list_load_exception') {
			$list = $this->filterAssetsUnloadList($list, 'scripts', 'load_exception');
		}
		// [/load exception list]

		$GLOBALS[$keyToCheck] = $list;
		return $list;
	}

	/**
	 * @return false|object
	 */
	public static function isCustomPostTypeArchivePage()
	{
		// There are exceptions here, when the archive page is connected to a page ID such as the WooCommerce Shop page
		if (Main::$vars['is_woo_shop_page']) {
			return false;
		}

		$wpacuQueriedObj = get_queried_object();

		$wpacuIsCustomPostTypeArchivePage = is_archive()
            && isset($wpacuQueriedObj->label, $wpacuQueriedObj->query_var, $wpacuQueriedObj->capability_type, $wpacuQueriedObj->name)
            && $wpacuQueriedObj->name && $wpacuQueriedObj->query_var
            && ( in_array($wpacuQueriedObj->capability_type, array('post', 'product'))
                 || (isset($wpacuQueriedObj->_edit_link) && $wpacuQueriedObj->_edit_link === 'post.php?post=%d') );

		if ($wpacuIsCustomPostTypeArchivePage) {
			return $wpacuQueriedObj;
		}

		return false;
	}

	/**
	 * @return bool
	 */
	public function isTaxonomyEditPage()
	{
		if (! $this->isTaxonomyEditPage) {
			$current_screen = \get_current_screen();

			if ( $current_screen->taxonomy !== null
			     && $current_screen->taxonomy
			     && ( strpos( $current_screen->id, 'edit' ) !== false ) ) {
				$this->isTaxonomyEditPage = true;
			}
		}

		return $this->isTaxonomyEditPage;
	}

	/**
	 * @param $pattern
	 * @param $subject
	 *
	 * @return bool
     *
     * @noinspection InvertedIfElseConstructsInspection
     */
	public static function isRegExMatch($pattern, $subject)
	{
		$regExMatches = false;

		$pattern = trim($pattern);
		$subject = trim($subject);

		if (! $pattern || ! $subject) {
		    return false;
        }

		if (PHP_VERSION_ID >= 70100) {
            if ( ! class_exists( '\TRegx\CleanRegex\PcrePattern' ) ) {
                wpacuLoadRegExFromVendor();
            }

		    $hasTregxPhpSevenOnePlus = class_exists( '\TRegx\CleanRegex\PcrePattern' )
               && method_exists( '\TRegx\CleanRegex\Pattern', 'delimited' );

            try {
                // One line (there aren't several lines in the textarea)
                if ( strpos( $pattern, "\n" ) === false ) {
                    if ( $hasTregxPhpSevenOnePlus && ($cleanRegexPattern = \TRegx\CleanRegex\PcrePattern::of($pattern)->delimited()) && \TRegx\CleanRegex\PcrePattern::of($cleanRegexPattern)->test($subject) ) {
                        $regExMatches = true;
                    }

                    if ( ! $hasTregxPhpSevenOnePlus && @preg_match( $pattern, $subject ) ) { // fallback
                        $regExMatches = true;
                    }
                } else {
                    // Multiple lines
                    foreach ( explode( "\n", $pattern ) as $patternRow ) {
                        $patternRow = trim( $patternRow );

                        if ( $hasTregxPhpSevenOnePlus && ($cleanRegexPattern = \TRegx\CleanRegex\PcrePattern::of($patternRow)->delimited()) && \TRegx\CleanRegex\PcrePattern::of($cleanRegexPattern)->test($subject) ) {
                            $regExMatches = true;
                            break;
                        }

                        if ( ! $hasTregxPhpSevenOnePlus && @preg_match( $patternRow, $subject ) ) { // fallback
                            $regExMatches = true;
                            break;
                        }
                    }
                }
            } catch ( \Exception $e ) {}
        } else {
            if ( ! class_exists( '\CleanRegex\Pattern' ) ) {
                wpacuLoadRegExFromVendor();
            }

			$hasTregxPhpFiveSixPlus = class_exists( '\CleanRegex\Pattern' )
              && class_exists( '\SafeRegex\preg' )
              && method_exists( '\CleanRegex\Pattern', 'delimitered' )
              && method_exists( '\SafeRegex\preg', 'match' );

            try {
                // One line (there aren't several lines in the textarea)
                if ( strpos( $pattern, "\n" ) === false ) {
                    if ( $hasTregxPhpFiveSixPlus && ($cleanRegexPattern = new \CleanRegex\Pattern( $pattern )) && \SafeRegex\preg::match( $cleanRegexPattern->delimitered(), $subject ) ) {
                        $regExMatches = true;
                    }

                    if ( ! $hasTregxPhpFiveSixPlus && @preg_match( $pattern, $subject ) ) { // fallback
                        $regExMatches = true;
                    }
                } else {
                    // Multiple lines
                    foreach ( explode( "\n", $pattern ) as $patternRow ) {
                        $patternRow = trim( $patternRow );

                        if ( $hasTregxPhpFiveSixPlus && ($cleanRegexPattern = new \CleanRegex\Pattern( $patternRow )) && \SafeRegex\preg::match( $cleanRegexPattern->delimitered(), $subject ) ) {
                            $regExMatches = true;
                            break;
                        }

                        if ( ! $hasTregxPhpFiveSixPlus && @preg_match( $patternRow, $subject ) ) { // fallback
                            $regExMatches = true;
                            break;
                        }
                    }
                }
            } catch ( \Exception $e ) {}
		}

		return $regExMatches;
	}

	/**
	 * @param $wpacuObjectData
	 *
	 * @return mixed
	 */
	public function wpacuObjectData($wpacuObjectData)
    {
	    if (is_admin() && $this->isTaxonomyEditPage() && Misc::getVar('get', 'tag_ID') && Misc::getVar('get', 'taxonomy')) {
		    $wpacuObjectData['tag_id']         = (int)Misc::getVar('get', 'tag_ID');
		    $wpacuObjectData['wpacu_taxonomy'] = Misc::getVar('get', 'taxonomy');
	    }

        if ( ! is_admin() ) {
            if ( MainFront::isAnyTaxPage() ) {
                $wpacuObjectData['is_tax_page'] = true;

                global $wp_query;
                $object = $wp_query->get_queried_object();

                $wpacuObjectData['tax_name'] = $object->taxonomy;
            } elseif ( is_author() ) {
                $wpacuObjectData['is_author_page'] = true;

                $authorData = get_userdata(get_query_var('author'));
                $wpacuObjectData['author_nice_name'] = $authorData->data->user_nicename;
            } elseif ( is_404() ) {
                $wpacuObjectData['is_404_page']    = true;
            } elseif ( is_search() ) {
                $wpacuObjectData['is_search_page'] = true;
            } elseif ( is_date() ) {
                $wpacuObjectData['is_date_page']   = true;
            }

            // Archive page (list of all posts belonging to a specific post type)
            $wpacuQueriedObjForCustomPostType = self::isCustomPostTypeArchivePage();

            if (isset($wpacuQueriedObjForCustomPostType->name)) {
                $wpacuObjectData['is_archive_page'] = true;
                $wpacuObjectData['archive_name']    = $wpacuQueriedObjForCustomPostType->name;
            }
        }

	    return $wpacuObjectData;
    }

	/**
	 * @param array $data
	 *
	 * @return array
	 */
	public function getScriptAttributesToApplyOnCurrentPage($data = array())
    {
        if ($this->scriptsAttributesChecked || OptimizeCommon::preventAnyFrontendOptimization() || Main::instance()->preventAssetsSettings()) {
            return array('async' => $this->asyncScripts, 'defer' => $this->deferScripts);
        }

	    // Could be front-end view or Dashboard view
        // Various conditionals are set below as this method would be trigger on Front-end view (no AJAX call)
        // and from AJAX calls when a post / page / taxonomy or home page are managed within the Dashboard
	    if (isset($data['post_id'])) {
		    // AJAX Call (within the Dashboard)
		    $postId = $data['post_id'];
	    } else {
	        // Regular view (either front-end edit mode or visitor accessing the page)
            // Either page, the ID is fetched in the same way
	        $postId = Main::instance()->getCurrentPostId();
        }

        // Any globally loaded attributes?
        if (wpacuIsDefinedConstant('WPACU_NO_SITE_WIDE_SCRIPT_ATTRS_SET')) {
            $scriptGlobalAttributes = $this->onThisPageScriptsAttributes;
        } else {
            $scriptGlobalAttributes = $this->getScriptGlobalAttributes();
        }

        $this->asyncScripts = $scriptGlobalAttributes['async'];
	    $this->deferScripts = $scriptGlobalAttributes['defer'];

        $taxID = false; // default

        $isForSingularPage = (Main::instance()->settings['dashboard_show'] && $postId > 1) || MainFront::isSingularPage();
        $isForFrontPage    = ! $isForSingularPage && ((isset($data['wpacu_type']) && $data['wpacu_type'] === 'front_page') || MainFront::isHomePage());

        if ($isForSingularPage) {
	        // Post, Page, Custom Post Type, Home page (static page selected as front page)
	        $list = get_post_meta($postId, '_' . WPACU_PLUGIN_ID . '_data', true);
        } elseif ($isForFrontPage) {
            // Home page (latest posts)
	        $list = get_option( WPACU_PLUGIN_ID . '_front_page_data');
        } elseif (is_404() || Main::isWpDefaultSearchPage() || is_date() || self::isCustomPostTypeArchivePage()) {
            // 404 Not Found, Search Results, Date archive page, Custom Post Type archive page
	        $list = get_option( WPACU_PLUGIN_ID . '_global_data');
        } elseif (is_author()) {
	        $authorId = self::getAuthorIdOnAuthorArchivePage(__FILE__, __LINE__);

	        if ($authorId !== null) {
		        // Author pages (e.g /author/author-name-here/)
		        $list = get_user_meta( $authorId, '_' . WPACU_PLUGIN_ID . '_data', true );
	        }
        } else {
            global $wp_query;
            $object = $wp_query->get_queried_object();

            if (isset($object->taxonomy)) {
                $taxID = $object->term_id;
            } elseif (isset($_REQUEST['tag_id']) && Main::instance()->settings['dashboard_show'] && is_admin()) {
                $taxID = $_REQUEST['tag_id'];
            }

            if ($taxID) {
                // Taxonomy page (e.g. category, tag pages)
                $list = get_term_meta($taxID, '_' . WPACU_PLUGIN_ID . '_data', true);
            }
        }

        if (! (isset($list) && $list)) {
	        return array('async' => $this->asyncScripts, 'defer' => $this->deferScripts);
        }

	    $list = json_decode($list, ARRAY_A);

	    if (wpacuJsonLastError() === JSON_ERROR_NONE) {
            $targetKeyNoLoads = 'scripts_attributes_no_load';

	        if ($isForSingularPage || $isForFrontPage || $taxID || is_author()) {
		        $targetLocation        = isset($list['scripts']) ? $list['scripts'] : array();
		        $targetLocationNoLoads = isset($list[$targetKeyNoLoads]) ? $list[$targetKeyNoLoads] : array();
            } elseif (is_404()) {
	            $targetLocation        = isset($list['scripts']['404']) ? $list['scripts']['404'] : array();
		        $targetLocationNoLoads = isset($list[$targetKeyNoLoads]['404']) ? $list[$targetKeyNoLoads]['404'] : array();
	        } elseif (Main::isWpDefaultSearchPage()) {
	            $targetLocation        = isset($list['scripts']['search']) ? $list['scripts']['search'] : array();
		        $targetLocationNoLoads = isset($list[$targetKeyNoLoads]['search']) ? $list[$targetKeyNoLoads]['search'] : array();
	        } elseif (is_date()) {
		        $targetLocation        = isset($list['scripts']['date']) ? $list['scripts']['date'] : array();
		        $targetLocationNoLoads = isset($list[$targetKeyNoLoads]['date']) ? $list[$targetKeyNoLoads]['date'] : array();
	        } elseif ($customPostTypeObj = self::isCustomPostTypeArchivePage()) {
	            $targetKey             = 'custom_post_type_archive_' . $customPostTypeObj->name;
                $targetLocation        = isset($list['scripts'][$targetKey]) ? $list['scripts'][$targetKey] : array();
		        $targetLocationNoLoads = isset($list[$targetKeyNoLoads][$targetKey]) ? $list[$targetKeyNoLoads][$targetKey] : array();
	        }

	        if ( ! empty($targetLocation) ) {
			    foreach ( $targetLocation as $asset => $values ) {
				    if ( ! empty( $values['attributes'] ) ) {
					    if ( in_array( 'async', $values['attributes'] ) ) {
						    $this->asyncScripts[] =
                            $this->onThisPageScriptsAttributes['async'][] =
                            $this->scriptsAttrsToApplyOnCurrentPage['async'][] = $asset;
					    }

					    if ( in_array( 'defer', $values['attributes'] ) ) {
						    $this->deferScripts[] =
                            $this->onThisPageScriptsAttributes['defer'][] =
                            $this->scriptsAttrsToApplyOnCurrentPage['defer'][] = $asset;
					    }
				    }
			    }
		    }
	    }

	    $this->scriptsAttributesChecked = true;

	    if ($wpacuLoadJsAsyncHandles = Misc::getVar('get', 'wpacu_js_async')) {
		    if (strpos($wpacuLoadJsAsyncHandles, ',') !== false) {
			    foreach (explode(',', $wpacuLoadJsAsyncHandles) as $wpacuLoadJsAsyncHandle) {
				    if (trim($wpacuLoadJsAsyncHandle)) {
					    $this->asyncScripts[] = $wpacuLoadJsAsyncHandle;
				    }
			    }
		    } else {
			    $this->asyncScripts[] = $wpacuLoadJsAsyncHandles;
		    }
	    }

	    if ($wpacuLoadJsDeferHandles = Misc::getVar('get', 'wpacu_js_defer')) {
	        if (strpos($wpacuLoadJsDeferHandles, ',') !== false) {
	            foreach (explode(',', $wpacuLoadJsDeferHandles) as $wpacuLoadJsDeferHandle) {
	                if (trim($wpacuLoadJsDeferHandle)) {
		                $this->deferScripts[] = $wpacuLoadJsDeferHandle;
	                }
                }
            } else {
		        $this->deferScripts[] = $wpacuLoadJsDeferHandles;
	        }
	    }

        // Any load exceptions? "not here (exception)" option
        // Trigger the code only if at least one rule is set ("async" or "defer")
        if ( (! empty($this->asyncScripts) || ! empty($this->deferScripts)) && ! empty($targetLocationNoLoads) ) {
            foreach ($targetLocationNoLoads as $handle => $values) {
                if (in_array('async', $values)) {
                    $this->onThisPageScriptsAttrsNoLoad['async'][] = $handle;
                }

                if (in_array('defer', $values)) {
                    $this->onThisPageScriptsAttrsNoLoad['defer'][] = $handle;
                }
            }
        }

	    return array('async' => $this->asyncScripts, 'defer' => $this->deferScripts);
    }

	/**
	 * @return array
	 */
	public function getScriptGlobalAttributes()
    {
        if (! empty($this->globalScriptsAttributes)) {
            return $this->globalScriptsAttributes;
        }

        $asyncGlobalScripts = $deferGlobalScripts = array();

	    $list = wpacuGetGlobalData();

	    // Empty list, no attributes to apply
	    if ( empty($list['scripts']['everywhere']) ) {
		    $this->globalScriptsAttributes = array('async' => $asyncGlobalScripts, 'defer' => $deferGlobalScripts);
		    return $this->globalScriptsAttributes;
        }

	    // Is it in a valid JSON format and global attributes (applied everywhere) are stored there?
        foreach ( $list['scripts']['everywhere'] as $asset => $values ) {
            if ( ! empty($values['attributes']) ) {
                if (in_array('async', $values['attributes'])) {
                    $asyncGlobalScripts[] = $asset;
                }

                if (in_array('defer', $values['attributes'])) {
                    $deferGlobalScripts[] = $asset;
                }
            }
        }

	    $this->globalScriptsAttributes = array('async' => $asyncGlobalScripts, 'defer' => $deferGlobalScripts);

	    return $this->globalScriptsAttributes;
    }

	/**
	 * @return array
	 */
	public static function getMediaQueriesLoad()
	{
	    if ($handleData = ObjectCache::wpacu_cache_get('wpacu_media_queries_load')) {
	        return $handleData;
	    }

		$handleData = self::$mediaQueryLoad;

		$globalKey = 'media_queries_load';

		$handleDataList = wpacuGetGlobalData();

		if ( ! empty($handleDataList) ) {
			// Are new positions set for styles and scripts?
			foreach ( array('styles', 'scripts') as $assetKey ) {
				if ( ! empty( $handleDataList[$assetKey][$globalKey] ) ) {
					$handleData[$assetKey] = $handleDataList[$assetKey][$globalKey];
				}
			}
		}

		ObjectCache::wpacu_cache_add('wpacu_media_queries_load', $handleData);

		return $handleData;
	}

    /**
     * This function is used first, and in case there are entries, call /pro/classes/MatchMediaLoadPro.php
     * This helps reduce resources as calling too many classes/methods/functions adds up to the total load time of the plugin
     *
     * @param $htmlSource
     * @param $assetType
     *
     * @return array|\string[][]
     */
    public static function anyMediaQueryLoadAssetsFor($htmlSource, $assetType)
    {
        if (strpos($htmlSource, 'data-wpacu-apply-media-query') === false) {
            return array();
        }

        if ($assetType === 'styles') {
            if ( isset($_GET['wpacu_no_media_query_load_for_css']) ) {
                return array();
            }

            preg_match_all('#<link[^>]*(data-wpacu-apply-media-query)[^>]*(>)#Umi', $htmlSource, $matchesSourcesFromTags, PREG_SET_ORDER);

            return $matchesSourcesFromTags;
        }

        if ($assetType === 'scripts') {
            if ( isset($_GET['wpacu_no_media_query_load_for_js']) ) {
                return array();
            }

            preg_match_all(
                '#(<script[^>]*(data-wpacu-apply-media-query)(|\s+)=(|\s+)[^>]*>)|(<link[^>]*(as(\s+|)=(\s+|)(|"|\')script(|"|\'))(.*)data-wpacu-apply-media-query(.*)[^>]*>|<link[^>]*(.*)data-wpacu-apply-media-query(.*)(as(\s+|)=(\s+|)(|"|\')script(|"|\'))[^>]*>)#Umi',
                $htmlSource,
                $matchesSourcesFromTags,
                PREG_SET_ORDER
            );

            return $matchesSourcesFromTags;
        }

        return array();
    }

	/**
	 * @param $tag
	 * @param $handle
	 * @return mixed
	 */
	public function styleLoaderTag($tag, $handle)
	{
		/* [wpacu_timing] */ $wpacuTimingName = 'style_loader_tag_pro_changes'; Misc::scriptExecTimer( $wpacuTimingName ); /* [/wpacu_timing] */
        if ( ! wpacuIsDefinedConstant('WPACU_NO_MEDIA_QUERIES_LOAD_RULES_SET_FOR_ASSETS') ) {
            $mediaQueriesLoad = self::getMediaQueriesLoad();

            $enableStatus          = isset($mediaQueriesLoad['styles'][$handle]['enable']) ? (int)$mediaQueriesLoad['styles'][$handle]['enable'] : 0;
            $mediaQueryCustomValue = isset($mediaQueriesLoad['styles'][$handle]['value']) ? $mediaQueriesLoad['styles'][$handle]['value'] : '';

            $reps = array();

            // Case 1: Make the browser download the file only if this media query is matched: $mediaQueryCustomValue
            if ($enableStatus === 1 && $mediaQueryCustomValue !== '') {
                $reps = array('<link ' => '<link data-wpacu-apply-media-query=\'' . esc_attr($mediaQueriesLoad['styles'][$handle]['value']) . '\' ');
            }

            // Case 2: Make the browser download the file only if its current media query is matched
            // The LINK tag already has a "media" attribute different from "all"
            if ($enableStatus === 2) {
                $mediaAttrValue = Misc::getValueFromTag($tag, 'media');

                if ($mediaAttrValue !== 'all') {
                    $reps = array('<link ' => '<link data-wpacu-apply-media-query=\'' . esc_attr($mediaAttrValue) . '\' ');
                }
            }


            if ( ! empty($reps)) {
                // Perform the replacement
                $tag = str_replace(array_keys($reps), array_values($reps), $tag);

                if (strpos($tag, 'data-wpacu-style-handle') === false) {
                    // This is for a hardcoded LINK with "href"
                    $reps = array('<link ' => '<link data-wpacu-style-handle=\'' . $handle . '\' ');
                    $tag  = str_replace(array_keys($reps), array_values($reps), $tag);
                }

                ObjectCache::wpacu_cache_add_to_array('wpacu_css_media_queries_load_current_page', $handle);
            }
        }

		/* [wpacu_timing] */ Misc::scriptExecTimer( $wpacuTimingName, 'end' ); /* [/wpacu_timing] */
		return $tag;
	}

	/**
	 * @param $tag
	 * @param $handle
     *
	 * @return mixed
	 */
	public function scriptLoaderTag($tag, $handle)
	{
		/* [wpacu_timing] */ $wpacuTimingName = 'script_loader_tag_pro_changes'; Misc::scriptExecTimer( $wpacuTimingName ); /* [/wpacu_timing] */

		$applyAsyncOrDeferFromSetRules = true;

		// Prevent adding both 'async' and 'defer' attributes for debugging purposes
		if ( ! empty($_REQUEST) && array_key_exists('wpacu_no_async', $_REQUEST) && array_key_exists('wpacu_no_defer', $_REQUEST) ) {
			$applyAsyncOrDeferFromSetRules = false;
		}

        if ($applyAsyncOrDeferFromSetRules) {
	        $attrs = $this->getScriptAttributesToApplyOnCurrentPage();

	        foreach ( array( 'async', 'defer' ) as $attrType ) {
		        if ( ! empty( $_REQUEST ) && array_key_exists( 'wpacu_no_' . $attrType, $_REQUEST ) ) {
			        continue; // prevent adding any async/defer attributes for debugging purposes
		        }

		        if ( in_array( $handle, $attrs[ $attrType ] ) && ( ! in_array( $handle, $this->onThisPageScriptsAttrsNoLoad[ $attrType ] ) ) ) {
			        $tag = str_replace( ' src=', ' ' . $attrType . '=\'' . $attrType . '\' src=', $tag );
		        }
	        }
        }

        if ( ! wpacuIsDefinedConstant('WPACU_NO_MEDIA_QUERIES_LOAD_RULES_SET_FOR_ASSETS') ) {
            $mediaQueriesLoad = self::getMediaQueriesLoad();

            if ( isset($mediaQueriesLoad['scripts'][$handle]['enable'], $mediaQueriesLoad['scripts'][$handle]['value']) &&
                $mediaQueriesLoad['scripts'][$handle]['enable'] && $mediaQueriesLoad['scripts'][$handle]['value'] ) {
                $reps = array( '<script ' => '<script data-wpacu-apply-media-query=\'' . esc_attr($mediaQueriesLoad['scripts'][$handle]['value']) . '\' ' );
                $tag = str_replace( array_keys( $reps ), array_values( $reps ), $tag );

                if (strpos($tag, 'data-wpacu-script-handle') === false) {
                    $reps = array( '<script ' => '<script data-wpacu-script-handle=\'' . $handle . '\' ' );
                    $tag = str_replace( array_keys( $reps ), array_values( $reps ), $tag );

                    ObjectCache::wpacu_cache_add_to_array( 'wpacu_js_media_queries_load_current_page', $handle );
                }
            }
        }

		/* [wpacu_timing] */ Misc::scriptExecTimer( $wpacuTimingName, 'end' ); /* [/wpacu_timing] */
		return $tag;
	}

	/**
     * Get the list of any position changes for the assets
     * If there are any values returned, then trigger the /classes/pro/PositionsPro.php class and its methods
     *
     * @param $filtered bool
     * If set to false, it will return the settings from the database as they are
     * The filtered version might have styles removed in case "Optimize CSS Delivery" from WP Rocket is enabled
     *
     * @return array
     */
    public static function getAssetsPositions($filtered = true)
    {
        $cacheKeyToCheck = $filtered ? '_filtered' : '_non_filtered';

        if ( $newPositionsAssets = ObjectCache::wpacu_cache_get('wpacu_assets_positions' . $cacheKeyToCheck) ) {
            return $newPositionsAssets;
        }

        $newPositionsAssets = array('styles' => array(), 'scripts' => array());

        $newPositionsList = wpacuGetGlobalData();

        if ( ! empty($newPositionsList) ) {
            if ($filtered) {
                $newPositionsList = apply_filters('wpacu_pro_new_positions_assets', $newPositionsList);
            }

            // Are new positions set for styles and scripts?
            foreach (array('styles', 'scripts') as $assetKey) {
                if ( ! empty( $newPositionsList[$assetKey]['positions'] ) ) {
                    $newPositionsAssets[$assetKey] = $newPositionsList[$assetKey]['positions'];
                }
            }
        }

        /*
         * On page request, for testing purposes CSS/JS can be moved from HEAD to BODY and vice-versa
           e.g. /?wpacu_css_move_to_body=handle-here,another-handle | /?wpacu_js_move_to_body=handle-here,another-handle
                /?wpacu_css_move_to_head=handle-here,another-handle | /?wpacu_js_move_to_head=handle-here,another-handle
            * Note: A single handle can be used; Multiple handle names are separated by comma
        */
        foreach (array('head', 'body') as $wpacuChosenPosition) {
            foreach (array('css', 'js') as $assetExt) {
                if ($wpacuCssMoveToNewPositionHandles = Misc::getVar('get', 'wpacu_'.$assetExt.'_move_to_' . $wpacuChosenPosition)) {
                    $assetType = ($assetExt === 'css') ? 'styles' : 'scripts';

                    if (strpos($wpacuCssMoveToNewPositionHandles, ',') !== false) {
                        foreach (explode(',', $wpacuCssMoveToNewPositionHandles) as $wpacuCssMoveToBodyHandle) {
                            if (trim($wpacuCssMoveToBodyHandle)) {
                                $newPositionsAssets[$assetType][$wpacuCssMoveToBodyHandle] = $wpacuChosenPosition;
                            }
                        }
                    } else {
                        $newPositionsAssets[$assetType][$wpacuCssMoveToNewPositionHandles] = $wpacuChosenPosition;
                    }
                }
            }
        }

        ObjectCache::wpacu_cache_set('wpacu_assets_positions' . $cacheKeyToCheck, $newPositionsAssets);

        return $newPositionsAssets;
    }

    // [START HARDCODED RELATED METHODS]
    // Thess methods are called first to determine if it's worth loading the following classes:
    // -- /classes/HardcodedAssets.php
    // -- /pro/classes/HardcodedAssetsPro.php
    /**
     * @return bool
     */
    public static function triggerDifferentHtmlAlterationForFrontendView()
    {
        if ( is_admin() ) {
            return false;
        }

        // If "Smart Slider 3" plugin is not enabled, then this triggering method won't take effect
        if ( ! defined('SMARTSLIDER3_LIBRARY_PATH') ) {
            return false;
        }

        // Do not do continue if "Test Mode" is Enabled and the user is a guest
        if ( Main::instance()->settings['test_mode'] && ! Menu::userCanAccessAssetCleanUp() ) {
            return false;
        }

        return true;
    }

    /**
     * The '_has_hardcoded_rule' key is set to true when at least one hardcoded rule is in the list
     *
     * @return array[]
     */
    public static function getHardcodedRules()
    {
        $rules = array(
            'unload' => self::getHardcodedUnloadList(),
        );

        $allHardcodedUnloadHandles = array();

        if ( ! empty($rules['unload']) ) {
            foreach ( $rules['unload'] as $generatedHandles ) {
                if ( ! empty($generatedHandles) ) {
                    foreach ( $generatedHandles as $generatedHandle ) {
                        if ( strpos($generatedHandle, 'wpacu_hardcoded_') !== false ) {
                            $rules['_has_hardcoded_rule'] = true;
                            $allHardcodedUnloadHandles[] = $generatedHandle;
                        }
                    }
                }
            }
        }

        // [Positions]
        if ( ! wpacuIsDefinedConstant('WPACU_NO_POSITIONS_CHANGED_FOR_ASSETS') ) {
            $assetsPositions = self::getAssetsPositions();

            foreach ($assetsPositions as $assetType => $list) {
                if ( empty($list) ) {
                    unset($assetsPositions[$assetType]);
                    continue;
                }

                foreach (array_keys($list) as $dbHandle) {
                    if (strpos($dbHandle, 'wpacu_hardcoded_') === false || in_array($dbHandle, $allHardcodedUnloadHandles)) {
                        unset($assetsPositions[$assetType][$dbHandle]);

                        if (empty($assetsPositions[$assetType])) {
                            unset($assetsPositions[$assetType]);
                        }

                        continue;
                    }

                    $rules['_has_hardcoded_rule'] = true;
                }
            }

            if ( ! empty($assetsPositions) ) {
                $rules['positions'] = $assetsPositions;
            }
        }
        // [/Positions]

        // [Preloads]
        if ( ! wpacuIsDefinedConstant('WPACU_NO_ASSETS_PRELOADED') ) {
            $preloads = Preloads::instance()->getPreloads();

            foreach ($preloads as $assetType => $preloadList) {
                if ( empty($preloadList) ) {
                    unset($preloads[$assetType]);
                    continue;
                }

                foreach (array_keys($preloadList) as $dbHandle) {
                    if (strpos($dbHandle, 'wpacu_hardcoded_') === false || in_array($dbHandle, $allHardcodedUnloadHandles)) {
                        unset($preloads[$assetType][$dbHandle]);

                        if (empty($preloads[$assetType])) {
                            unset($preloads[$assetType]);
                        }

                        continue;
                    }

                    $rules['_has_hardcoded_rule'] = true;
                }
            }

            if ( ! empty($preloads) ) {
                $rules['preloads'] = $preloads;
            }
        }
        // [/Preloads]

        // [Media Queries Load]
        if ( ! wpacuIsDefinedConstant('WPACU_NO_MEDIA_QUERIES_LOAD_RULES_SET_FOR_ASSETS') ) {
            $mediaQueriesLoad = self::getMediaQueriesLoad();

            foreach ($mediaQueriesLoad as $assetType => $loadList) {
                if ( empty($loadList) ) {
                    unset($mediaQueriesLoad[$assetType]);
                    continue;
                }

                foreach ($loadList as $dbHandle => $loadValuesPerHandle) {
                    if ((empty($loadValuesPerHandle['enable']) || empty($loadValuesPerHandle['value'])) ||
                        strpos($dbHandle, 'wpacu_hardcoded_') === false ||
                        in_array($dbHandle, $allHardcodedUnloadHandles)) {
                        unset ($mediaQueriesLoad[$assetType][$dbHandle]);

                        if (empty($mediaQueriesLoad[$assetType])) {
                            unset($mediaQueriesLoad[$assetType]);
                        }

                        continue;
                    }

                    $rules['_has_hardcoded_rule'] = true;
                }
            }

            if ( ! empty($mediaQueriesLoad) ) {
                $rules['media_queries_load'] = $mediaQueriesLoad;
            }
        }
        // [/Media Queries Load]

        $applyAsyncOrDeferFromSetRules = true;

        // Prevent adding both 'async' and 'defer' attributes for debugging purposes
        if ( ! empty($_REQUEST) && array_key_exists('wpacu_no_async', $_REQUEST) && array_key_exists('wpacu_no_defer', $_REQUEST) ) {
            $applyAsyncOrDeferFromSetRules = false;
        }

        if ($applyAsyncOrDeferFromSetRules) {
            global $wpacuMainPro;

            if ( ! (is_object($wpacuMainPro) && method_exists($wpacuMainPro, 'getScriptAttributesToApplyOnCurrentPage')) ) {
                $wpacuMainPro = new self();
            }

            $scriptAttrs = $wpacuMainPro->getScriptAttributesToApplyOnCurrentPage();

            foreach ($scriptAttrs as $attrType => $attrsList) {
                if ( empty($attrsList) ) {
                    unset($scriptAttrs[$attrType]);
                    continue;
                }

                foreach ( $attrsList as $dbHandleKey => $dbHandle ) {
                    if (strpos($dbHandle, 'wpacu_hardcoded_') === false || in_array($dbHandle, $allHardcodedUnloadHandles)) {
                        unset($scriptAttrs[$attrType][$dbHandleKey]);

                        if (empty($scriptAttrs[$attrType])) {
                            unset($scriptAttrs[$attrType]);
                        }

                        continue;
                    }

                    $rules['_has_hardcoded_rule'] = true;
                }
            }

            if ( ! empty($scriptAttrs) ) {
                $rules['script_attrs'] = $scriptAttrs;
            }
        }

        return $rules;
    }

    /**
     * @return array
     */
    public static function getHardcodedUnloadList()
    {
        $hardcodedUnloadList['wpacu_hardcoded_links']                    = ObjectCache::wpacu_cache_get('wpacu_hardcoded_links')  ?: array();
        $hardcodedUnloadList['wpacu_hardcoded_styles']                   = ObjectCache::wpacu_cache_get('wpacu_hardcoded_styles') ?: array();
        $hardcodedUnloadList['wpacu_hardcoded_scripts_src']              = ObjectCache::wpacu_cache_get('wpacu_hardcoded_scripts_src') ?: array();
        $hardcodedUnloadList['wpacu_hardcoded_scripts_noscripts_inline'] = ObjectCache::wpacu_cache_get('wpacu_hardcoded_scripts_noscripts_inline') ?: array();

        return Misc::filterList($hardcodedUnloadList);
    }
    // [END HARDCODED RELATED METHODS]

    /**
     * e.g. When an asset is unloaded, site-wide
     * * exceptions to the rule can be added, for the asset to load on all archive pages of any author
     * * You might need a CSS/JS to be unloaded site-wide, but on /author/[any_author_title_slug_here]/
     * * you can make an exception, and have the CSS/JS loaded
     *
     * @return array|array[]
     */
    public static function getLoadExceptionsViaAuthorType()
    {
        $exceptionsListDefault = array( 'styles' => array(), 'scripts' => array() );

        $exceptionsListJson = get_option(WPACU_PLUGIN_ID . '_author_type_load_exceptions');

        $exceptionsList = @json_decode($exceptionsListJson, true);

        // Issues with decoding the JSON file? Return an empty list
        if (wpacuJsonLastError() !== JSON_ERROR_NONE) {
            return $exceptionsListDefault;
        }

        if ( is_array($exceptionsList) && ! empty($exceptionsList) ) {
            return $exceptionsList;
        }

        return $exceptionsListDefault;
    }

    /**
     * e.g. When an asset is unloaded, site-wide
     * exceptions to the rule can be added, for the asset to load on all pages of [taxonomy] type
     * You might need a CSS/JS to be unloaded site-wide, but on /category/food/, /category/other/
     * you can make an exception, and have the CSS/JS loaded
     *
     * @param $taxonomy (optional, if it's not there, all load exceptions will load for all taxonomies)
     *
     * @return array|array[]|mixed
     */
    public static function getLoadExceptionsViaTaxType($taxonomy = '')
    {
        if ($taxonomy) {
            // Default for all results for this $taxonomy
            $exceptionsListDefault = array( $taxonomy => array( 'styles' => array(), 'scripts' => array() ) );
        } else {
            // Default for the asset list for the specific $taxonomy ("styles" / "scripts")
            $exceptionsListDefault = array();
        }

        $exceptionsListJson = get_option(WPACU_PLUGIN_ID . '_tax_type_load_exceptions');

        $exceptionsList = @json_decode($exceptionsListJson, true);

        // Issues with decoding the JSON file? Return an empty list
        if (wpacuJsonLastError() !== JSON_ERROR_NONE) {
            return $exceptionsListDefault;
        }

        // Return any handles added as load exceptions for the requested $taxonomy
        if ( $taxonomy !== '' && isset($exceptionsList[$taxonomy]) ) {
            return $exceptionsList[$taxonomy];
        }

        if ( is_array($exceptionsList) && ! empty($exceptionsList) ) {
            return $exceptionsList;
        }

        return $exceptionsListDefault;
    }

	/**
	 * @param $postId
	 *
	 * @return array
	 */
	public static function getTaxonomyTermIdsAssocToPost($postId)
    {
        $postTaxonomies = get_post_taxonomies($postId);

        if (in_array('post_format', $postTaxonomies)) {
            $unsetKey = array_search('post_format', $postTaxonomies);
            unset($postTaxonomies[$unsetKey]);
        }

        // All terms associated to all taxonomies
        $allTermsIds = array();

        foreach ($postTaxonomies as $postTaxonomy) {
	        $allPostTerms = get_the_terms($postId, $postTaxonomy);

	        if (empty($allPostTerms)) {
	            continue;
	        }

	        foreach ($allPostTerms as $postTermData) {
	            $allTermsIds[] = $postTermData->term_id;
	        }
        }

        return $allTermsIds;
    }

	/**
	 * @param $postType
	 * @param $assetType
	 * @param $handle
	 *
	 * @return array|mixed
	 */
	public static function getTaxonomyValuesAssocToPostType($postType, $assetType = '', $handle = '')
    {
	    $existingListAllJson = get_option( WPACU_PLUGIN_ID . '_bulk_unload' );

	    if ( ! $existingListAllJson || ! $postType ) {
		    return array();
	    }

	    $existingListAll = json_decode( $existingListAllJson, true );

	    if (wpacuJsonLastError() !== JSON_ERROR_NONE ) {
		    return array();
	    }

	    if ( ! empty( $existingListAll[ $assetType ]['post_type_via_tax'][ $postType ] [ $handle ] ['values'] ) ) {
            /*
             * Fetch for a certain handle (either a CSS or a JS)
             */
            return $existingListAll[ $assetType ]['post_type_via_tax'][ $postType ] [ $handle ] ['values'];
        }

	    $finalList = array(); // default

	    if ( $assetType === '' && $handle === '' ) {
            /*
             * Fetch all CSS/JS that have rules for this post type
             */
            foreach ( array('styles', 'scripts') as $assetTypeTwo ) {
                if ( ! empty($existingListAll[ $assetTypeTwo ]['post_type_via_tax'][ $postType ]) ) {
                    $finalList[$assetTypeTwo] = $existingListAll[ $assetTypeTwo ]['post_type_via_tax'][ $postType ];
                }
            }

		    return $finalList;
        }

	    return array();
    }

	/**
     * Case 1: If $postType is not mentioned, it will get all post types
     * Case 2: If $postType is set and $assetType & $handle are not set, it will get all rules for $postType
     * Case 3: If all parameters are set, it will get any terms set for the CSS/JS handle loaded within $postType pages
     *
	 * @param string $postType
	 * @param string $assetType
	 * @param string $handle
	 *
	 * @return array|\array[][]|mixed
	 */
	public static function getTaxonomyValuesAssocToPostTypeLoadExceptions($postType = '', $assetType = '', $handle = '')
	{
		$exceptionsListDefault = array();

	    if ($postType) {
	        if ($assetType === '' && $handle === '') {
	            // Default for all results for this $postType
		        $exceptionsListDefault = array( $postType => array( 'styles' => array(), 'scripts' => array() ) );
	        } else {
	            // Default for the terms list for the specific $handle of $assetType ("styles" or "scripts")
                $exceptionsListDefault = array();
	        }
	    }

		$exceptionsListJson = get_option(WPACU_PLUGIN_ID . '_post_type_via_tax_load_exceptions');
		$exceptionsList = @json_decode($exceptionsListJson, true);

		// Issues with decoding the JSON file? Return an empty list
		if (wpacuJsonLastError() !== JSON_ERROR_NONE) {
			return $exceptionsListDefault;
		}

		// Return any handles added as load exceptions for the requested $postType
		if ($postType !== '' && isset($exceptionsList[$postType])) {
			/*
			 * Fetch load exceptions for a certain handle (either a CSS or a JS)
			 */
		    if ( ! empty($exceptionsList[$postType][$assetType][$handle]['values']) ) {
			    return $exceptionsList[ $postType ] [$assetType] [ $handle ] ['values'];
		    }

			if ( $assetType === '' && $handle === '' ) {
			    /*
				 * Fetch all load exceptions (CSS & JS)
				 */
			    return $exceptionsList[$postType];
		    }
		} elseif (is_array($exceptionsList) && ! empty($exceptionsList)) {
		    return $exceptionsList;
		}

		return $exceptionsListDefault;
	}

	/**
	 * @param $list
	 * @param $assetType
	 * @param $filterType
	 *
	 * @return mixed
	 */
	public function filterAssetsUnloadList($list, $assetType, $filterType)
    {
	    $currentPost = Main::instance()->getCurrentPost();

        if ($filterType === 'unload') {
            if ( ! wpacuIsDefinedConstant('WPACU_NO_REGEX_RULES_SET_FOR_ASSETS') ) {
                self::$unloads['regex'] = self::getRegExRules('unloads');
            }

            // Page type: Any URL that mighty have its URI match any of the rules
	        if ( ! empty( self::$unloads['regex'][ $assetType ] ) ) {
		        foreach ( self::$unloads['regex'][ $assetType ] as $handle => $handleValues ) {
			        if ( isset( $handleValues['enable'], $handleValues['value'] ) && $handleValues['enable'] ) {
				        // We want to make sure the RegEx rules will be working fine if certain characters (e.g. Thai ones) are used
				        $requestUriAsItIs = rawurldecode( $_SERVER['REQUEST_URI'] );

				        if ( self::isRegExMatch( $handleValues['value'], $requestUriAsItIs ) ) {
					        $list[] = $handle;
					        self::$unloads['regex']['current_url_matches'][ $assetType ][] = $handle;
				        }
			        }
		        }
	        }

	        // Page type: All posts that might have taxonomies (e.g. category) associated to it
            if (isset($currentPost->post_type) && $currentPost->post_type) {
	            self::$unloads['post_type_via_tax'] = self::getTaxonomyValuesAssocToPostType( $currentPost->post_type );

                if ( ! empty( self::$unloads['post_type_via_tax'][ $assetType ] ) && isset( $currentPost->ID ) && $currentPost->ID ) {
		            $currentPostTerms = self::getTaxonomyTermIdsAssocToPost( $currentPost->ID );

		            foreach ( self::$unloads['post_type_via_tax'][ $assetType ] as $assetHandle => $assetData ) {
			            if ( isset( $assetData['enable'] ) && $assetData['enable'] && ! empty( $assetData['values'] ) ) {
				            // Go through the terms set and check if the current post ID is having the taxonomy value associated with it
				            foreach ( $assetData['values'] as $termId ) {
					            if ( in_array( $termId, $currentPostTerms ) ) {
						            // At least one match found; Stop here and add the asset to the unloading list
						            $list[] = $assetHandle;
						            self::$unloads['post_type_via_tax']['current_post_matches'][$assetType ][] = $assetHandle;
						            break;
					            }
				            }
			            }
		            }
	            }
            }

            global $wp_query;
            $object = $wp_query->get_queried_object();

            if ( isset( $object->taxonomy ) && $object->taxonomy ) {
                self::$unloads['tax'] = Main::instance()->getBulkUnload('taxonomy', $object->taxonomy);
            } elseif (is_author()) {
                self::$unloads['author'] = Main::instance()->getBulkUnload('author');
            }

	        // Page type: A taxonomy page (e.g. category page), author page
            // e.g. "Unload on All Pages of category taxonomy type * bulk unload"
	        $bulkUnloads = apply_filters('wpacu_pro_get_bulk_unloads', array());

            foreach (array('taxonomy', 'author') as $bulkType) {
                if ( ! empty( $bulkUnloads['bulk_unloaded'][$bulkType][ $assetType ] ) ) {
                    foreach ( $bulkUnloads['bulk_unloaded'][$bulkType][ $assetType ] as $assetHandle ) {
                        $list[] = $assetHandle;
                    }
                }
            }

	        }

        if ($filterType === 'load_exception') {
            if ( ! wpacuIsDefinedConstant('WPACU_NO_REGEX_RULES_SET_FOR_ASSETS') ) {
                self::$loadExceptions['regex'] = self::getRegExRules('load_exceptions');
            }

	        if ( ! empty( self::$loadExceptions['regex'][ $assetType ] ) ) {
		        foreach ( $list as $handleKey => $handle ) {
			        if ( isset( self::$loadExceptions['regex'][ $assetType ][ $handle ]['enable'], self::$loadExceptions['regex'][ $assetType ][ $handle ]['value'] )
			             && self::$loadExceptions['regex'][ $assetType ][ $handle ]['enable'] ) { // Needs to be marked as enabled
				        // We want to make sure the RegEx rules will be working fine if certain characters (e.g. Thai ones) are used
				        $requestUriAsItIs = rawurldecode( $_SERVER['REQUEST_URI'] );

				        if ( self::isRegExMatch( self::$loadExceptions['regex'][ $assetType ][ $handle ]['value'], $requestUriAsItIs ) ) {
					        unset( $list[ $handleKey ] );
					        self::$loadExceptions['regex']['current_url_matches'][ $assetType ][] = $handle;

					        // Are there any unloading rules via RegEx? Clean them up as the load exception takes priority
					        if ( isset( self::$unloads['regex'][ $assetType ][ $handle ] ) ) {
						        unset( self::$unloads['regex'][ $assetType ][ $handle ] );
					        }

					        if ( isset( self::$unloads['regex']['current_url_matches'][ $assetType ] )
					             && is_array( self::$unloads['regex']['current_url_matches'][ $assetType ] )
					             && in_array( $handle, self::$unloads['regex']['current_url_matches'][ $assetType ] ) ) {
						        $targetKey = array_search( $handle, self::$unloads['regex']['current_url_matches'][ $assetType ] );
						        unset( self::$unloads['regex']['current_url_matches'][ $assetType ][ $targetKey ] );
					        }
				        }
			        }
		        }
	        }

            if (isset($currentPost->post_type) && $currentPost->post_type) {
	            self::$loadExceptions['post_type_via_tax'] = self::getTaxonomyValuesAssocToPostTypeLoadExceptions( $currentPost->post_type );

	            if ( ( ! empty( self::$loadExceptions['post_type_via_tax'][ $assetType ] ) ) && isset( $currentPost->ID ) && $currentPost->ID ) {
		            $currentPostTerms = self::getTaxonomyTermIdsAssocToPost( $currentPost->ID );

		            foreach ( self::$loadExceptions['post_type_via_tax'][ $assetType ] as $assetHandle => $assetData ) {
			            if ( isset( $assetData['enable'] ) && $assetData['enable'] && ! empty( $assetData['values'] ) ) {
				            // Go through the terms set and check if the current post ID is having the taxonomy value associated with it
				            foreach ( $assetData['values'] as $termId ) {
					            if ( in_array( $termId, $currentPostTerms ) && in_array( $assetHandle, $list ) ) {
						            // At least one match found; Stop here and remove the asset to the unloading list
						            $handleKey = array_search( $assetHandle, $list );
						            unset( $list[ $handleKey ] );
						            break;
					            }
				            }
			            }
		            }
	            }
            }

            if ( ! isset($object) ) {
                global $wp_query;
                $object = $wp_query->get_queried_object();
            }

            if (isset($object->taxonomy) && $object->taxonomy) {
                self::$loadExceptions['tax'] = self::getLoadExceptionsViaTaxType($object->taxonomy);

                if ( ! empty(self::$loadExceptions['tax'][$assetType]) ) {
                    foreach (self::$loadExceptions['tax'][$assetType] as $assetHandle) {
                        if (in_array($assetHandle, $list)) {
                            $handleKey = array_search($assetHandle, $list);
                            unset($list[$handleKey]);
                        }
                    }
                }
            } elseif (is_author()) {
                self::$loadExceptions['author'] = self::getLoadExceptionsViaAuthorType();

                if ( ! empty(self::$loadExceptions['author'][$assetType]) ) {
                    foreach (self::$loadExceptions['author'][$assetType] as $assetHandle) {
                        if (in_array($assetHandle, $list)) {
                            $handleKey = array_search($assetHandle, $list);
                            unset($list[$handleKey]);
                        }
                    }
                }
            }
        }

	    return $list;
    }

	/**
	 *
	 */
	public function fallbacks()
	{
		// Fallback for the old filters
		add_filter('wpacu_pro_get_assets_unloaded_page_level', function ($list) { return apply_filters('wpacu_pro_get_assets_unloaded', $list); });
	}
}
