<?php
use FilterEverything\Filter\Pro\Admin\SeoRules;
/**
 * BLOG
 */
function ajouter_contact_blog() {
    // Vérifiez si nous sommes sur une page d'article de blog individuel
    if ( is_singular( 'post' ) ) {
        $add_before_comments = false; // Initialisez la variable booléenne à false
        ob_start(); // Démarrez la temporisation de la mémoire tampon de sortie
        ?>
        <div id="contact-blog" style="display:flex;"><div class="span7">
        <h2 class="contact-form-title">Un Devis ? Une Info ?</h2>
        <div id="contactsMsgs"></div>
        <form action="https://www.armoireplus.fr/contact/" method="post" id="contact-form">
                <div class="row-fluid">
                    <div class="span6">
                        <p class="form-name">
                            <label for="name">Nom et Prénom <span class="required">*</span></label>
                            <input type="text" name="contact-name" class="required-field" id="contact-name">
                        </p>
                    </div>
                    <div class="span6">
                        <p class="form-name">
                            <label for="name">Email <span class="required">*</span></label>
                            <input type="text" name="contact-email" class="required-field" id="contact-email">
                        </p>
                    </div>
                </div>
                <p class="form-name hidden">
                    <label for="name">Site Web</label>
                    <input type="text" name="contact-website" id="contact-website">
                </p>
                <p class="form-textarea">
                    <label for="contact_msg">Message <span class="required">*</span></label>
                    <textarea name="contact-msg" id="contact-msg" class="required-field" cols="30" rows="7"></textarea>
                </p>
                <p class="contacts-privacy" style="margin-bottom: 20px;">En envoyant mon message, j&lsquo;affirme avoir pris connaissance et accepte que les informations saisies dans ce formulaire sont utilisées pour soutenir mon expérience sur ce site, pour traiter ma demande dans le cadre de la relation commerciale qui en découle, et pour les raisons décrites dans les <a href="https://www.armoireplus.fr/mention-legale/">Mentions Légales et Politique de Confidentialité</a></p>
                <input type="hidden" name="referal" value="" id="referal">
                <p class="a-right">
                    <input type="hidden" name="contact-submit" id="contact-submit" value="true" >
                    <span class="spinner">Envoi en cours ...</span>
                    <button class="button" id="submit" type="submit">Envoyer un message</button>
                </p>
            <div class="clear"></div>
        </form>
    </div></div>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                    //var headingElement = $('.wp-block-heading');
                    var headingElement = $('h2.wp-block-heading:has(span#Vous_pourriez_aussi_aimer)');
                    headingElement.attr('id', 'apres_contact');
                    // Cherche l'élément suivant avec la classe CSS "wp-block-uagb-post-grid"
                    var postGridElement = headingElement.next('.wp-block-uagb-post-grid');
                    // Vérifie si les deux éléments sont présents et se suivent
                    if (headingElement.length && postGridElement.length && postGridElement.prev().is(headingElement)) {
                    // Si oui, ajoutez votre block juste avant l'élément avec la classe CSS "wp-block-heading"
                    headingElement.attr('id', 'apres_contact');
                    $('#contact-blog').insertBefore('#apres_contact');
                   // headingElement.before($('#contact-blog'));
                    } else {
                    // Si non, ajoutez votre block à la fin du contenu de l'article
                    $('.entry-content').append($('#contact-blog'));
                    }
                }
            );
        </script>
        <?php
        $block_content = ob_get_clean(); // Récupérer le contenu de la mémoire tampon de sortie et arrêter la temporisation
        echo $block_content;
    }
}
add_action( 'wp_footer', 'ajouter_contact_blog' );

/*
*******  Afficher la date et l'auteur du dernier update d'un article de blog
*/
function show_last_updated( $content ) {

    $u_time = get_the_time('U');
    $u_modified_time = get_the_modified_time('U');
    $custom_content ='';
  if ($u_modified_time >= $u_time + 86400 && is_singular('post')) {
      $updated_date = get_the_modified_time('j F Y');
      $updated_time = get_the_modified_time('h:i a');
      $custom_content .= '<p class="last-updated-date">Article mis à jour le '. $updated_date . ' par ' . get_the_modified_author() . '</p>';
    }
    
    $custom_content .= $content ;
    return $custom_content;
  
  }
  add_filter( 'the_content', 'show_last_updated' );