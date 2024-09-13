<?php
use FilterEverything\Filter\Pro\Admin\SeoRules;

/**
    * Fonction qui sert a vérifier si un élément correspond au début d'une chaine de caractere pour la fonction modify_filter_html
    */
    function isStartsWith($needle, $haystack) {
        foreach($haystack as $value) {
        if (substr(trim($needle), 0, strlen($value)) === $value) {
            return true;
        }
        }
        return false;
    }

    /*
    * Pour l'outil de filtre permet de passer les balises <button> en <a> quand c'est souhaité
    */
    function modify_filter_html( $html, $attributes, $term, $filter ) {
		if (class_exists('FilterEverything\Filter\Pro\Admin\SeoRules') && is_product_category()) {
			$urlA = array();
			$allRules = SeoRules::getAllInstances();
			$rules = $allRules[0]->getRules();
			if ($rules  != false){
			$i = 0;
			while ( $i < count( $rules ) ) {
				$post_excerpt = $rules[$i]['post_excerpt'];
				if (preg_match('/"rule_seo_title";s:(\d+):"(.*?)";/', $post_excerpt, $matches)) {
					$rule_seo_title_value = $matches[2];
				}
				$post_id = $rules[$i]['ID'];
				$title = get_the_title($post_id);
				$debutUrl = 'href="https://www.armoireplus.fr';
				$newUrl = $debutUrl . substr($title, 7). '/"';
				$urlA[] = $newUrl;
				$i ++;
			}
		}
		if (strpos($attributes, "?_gl") != "0") {
			$attributes = substr($attributes, 0, strpos($attributes, "?_gl")) . '"';
		}
		if (in_array(trim($attributes), $urlA)  ) {
			$html = '<a' . $attributes . '>' . $term->name . '</a>';
		} else {
			$html = "<button onclick='window.location. " . ltrim($attributes) . "'>" . $term->name . '</button>';
		}
		return $html;
		}
    }
    add_filter( 'wpc_filters_checkbox_term_html', 'modify_filter_html', 10, 4 );

    function mon_code_a_ajouter(){
		if (class_exists('FilterEverything\Filter\Pro\Admin\SeoRules') && is_product_category()) {
			$lien_page = 0;
			echo '<ul>';
			$allRules = SeoRules::getAllInstances();

				$rules = $allRules[0]->getRules();
				if ($rules  != false){
				$i = 0;
				while ( $i < count( $rules ) ) {
					$post_excerpt = $rules[$i]['post_excerpt'];
					if (preg_match('/"rule_seo_title";s:(\d+):"(.*?)";/', $post_excerpt, $matches)) {
						$rule_seo_title_value = $matches[2];
					}
					$post_id = $rules[$i]['ID'];
					$title = get_the_title($post_id);
					$debutUrl = 'https://www.armoireplus.fr';
					$newUrl = $debutUrl . substr($title, 7);
					$url = "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
					$parts1 = parse_url($url);
					$path1 = explode('/', $parts1['path']);
					$category_actu = $path1[2];
					$parts = parse_url($newUrl);
					$path = explode('/', $parts['path']);
					$category = $path[2];
					if (strpos($rule_seo_title_value,'| Armoire')!= 0){
						$rule_seo_title_value = substr($rule_seo_title_value ,0,strpos($rule_seo_title_value,'| Armoire'));
					}
					if ($category == $category_actu) {
						echo '<li> <a href="' . $newUrl . '/">' . $rule_seo_title_value . '</a> </li>'; 
						$lien_page ++;
					}
					$i ++;
				}
			}                                                                                                                                                                     
			if ($lien_page == 0){
				echo'<script>
					var element = document.getElementById("block-17");
					element.style.display = "none";
				</script>';
			}
			echo'</ul>';
		}
    }
    //permet l'affichage des critère de tri SEO grâce a SEO filter
    function custom_pages_widget_shortcode() {
        ob_start();
        mon_code_a_ajouter();
        $output = ob_get_clean();
        return $output;
    }

    add_action( 'widgets_init', 'register_custom_pages_widget' );

    //permet l'affichage des critère de tri SEO grâce a SEO filter
    function register_custom_pages_widget() {
        register_widget( 'Custom_Pages_Widget' );
    }
    //permet l'affichage des critère de tri SEO grâce a SEO filter
    class Custom_Pages_Widget extends WP_Widget {
        public function __construct() {
            parent::__construct(
                'custom_pages_widget',
                __( 'Custom Pages Widget', 'your-textdomain' ),
                array(
                    'description' => __( 'Displays a list of pages related to the current category', 'your-textdomain' ),
                )
            );
        }

        public function widget( $args, $instance ) {
            custom_pages_widget();
        }
        
        public function shortcode() {
            custom_pages_widget_shortcode();
        }
    }

    add_shortcode( 'custom_pages_widget', 'custom_pages_widget_shortcode' );

        
    function afficher_description_categorie() {
		if (class_exists('FilterEverything\Filter\Pro\Admin\SeoRules') && is_product_category()) {
			$allRules = SeoRules::getAllInstances();
			$rules = $allRules[0]->getRules();
			$is_rule = false;
			if ($rules  != false){
				$i = 0;
				while ( $i < count( $rules ) ) {
					$post_excerpt = $rules[$i]['post_excerpt'];
					if (preg_match('/"rule_seo_title";s:(\d+):"(.*?)";/', $post_excerpt, $matches)) {
						$rule_seo_title_value = $matches[2];
					}
					$post_id = $rules[$i]['ID'];
					$title = get_the_title($post_id);
					$debutUrl = 'https://www.armoireplus.fr';
					$newUrl = $debutUrl . substr($title, 7) . '/';
					$newUrl2 = $debutUrl . substr($title, 7) . '';
					$page_actuel = "https://www.armoireplus.fr" . $_SERVER["REQUEST_URI"];
					if (strpos($page_actuel, "?_gl") != "0"){
						$page_actuel = substr($page_actuel, 0, strpos($page_actuel, "?_gl")) . '';
					}
					if ($newUrl == $page_actuel){
						$is_rule = true;
						break;
					}elseif ( $newUrl2 == $page_actuel){
						$is_rule = true;
						break;
					}
					$i ++;
				}
			}   
			if (!$is_rule) {
				do_action( 'woocommerce_archive_description' );
			}
		}
    }

       
