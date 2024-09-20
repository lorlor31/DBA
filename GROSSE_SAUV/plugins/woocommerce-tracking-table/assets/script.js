/* Woocommerce Customer Tracking script */
jQuery( function( $ ) {

    $('a.envoie-avis').on('click', function(e) {
        e.preventDefault();
        let id = $(this).attr('data-id'),
        link = $(this),
        type = link.hasClass('alert-delay') ? 'delay' : 'review';
        $.ajax({
          type: 'GET',
          dataType: 'json',
          url: 'admin-ajax.php', // This is a variable that WordPress sets for you
          data: {
            action: 'envoi_commande_avis_button', // This is the name of the function you want to call
            id: id,  // Send the data-id attribute
            success: function(result, status) {
                if (status == 'success') {
                    let count = parseInt($('.' + type + '-' + result.data.id + ' .alert-count').html(), 10) + 1;
                    $('.' + type + '-' + result.data.id + ' .alert-count').html(count);
                    alert("L'avis a bien été envoyé actualiser la page pour le voir");
                }}
          }
        });
      });

      $('a.bloqued-review').on('click', function(e) {
        e.preventDefault();
        let id = $(this).attr('data-id'),
        link = $(this),
        type = link.hasClass('alert-delay') ? 'delay' : 'review';
        $.ajax({
          type: 'GET',
          dataType: 'json',
          url: 'admin-ajax.php', // This is a variable that WordPress sets for you
          data: {
            action: 'bloquer_commande_avis_button', // This is the name of the function you want to call
            id: id,  // Send the data-id attribute
            success: function(result, status) {
                if (status == 'success') {
                    let count = parseInt($('.' + type + '-' + result.data.id + ' .alert-count').html(), 10) + 1;
                    $('.' + type + '-' + result.data.id + ' .alert-count').html(count);
                    alert("L'avis a bien été bloqué actualiser la page pour le voir");
                }}
          }
        });
      });

    // Notification order
    $('a.button-alert').on('click', function(e) {
        e.preventDefault();
        let link = $(this),
            type = link.hasClass('alert-delay') ? 'delay' : 'review';
        if (!link.hasClass('disabled')) {
            let id = link.attr('data-id'),
                order = link.attr('data-order'),
                email = link.attr('data-email'),
                confirm = type == 'review' ? window.confirm("Souhaitez-vous générer un lien de demande d'avis pour la commande " + order + " ?") : true;
            if (confirm == true) {
                $('.wrap-loading').css('display', 'block');
                $.ajax({
                    url: 'admin-ajax.php',
                    type: 'GET',
                    data: 'action=tracking-alert&alert=' + type + '&order_id=' + id,
                    dataType: 'json',
                    success: function(result, status) {
                        if (status == 'success') {
                            let count = parseInt($('.' + type + '-' + result.data.id + ' .alert-count').html(), 10) + 1;
                            $('.' + type + '-' + result.data.id + ' .alert-count').html(count);
                            if (type == 'review') {
                                let body = '<div style="font-family:Arial,Helvetica,sans-serif;">' +
                                    '<p style="font-weight:bold;">Bonjour,</p>' +
                                    '<p>Nous venons aux nouvelles concernant la commande ' + order + ' sur armoireplus.fr</p>' +
                                    '<p>Votre retour d\'expérience est important pour nous aider à améliorer nos services.</p>' +
                                    '<p>Laissez nous un avis client de façon confidentielle sur notre plateforme * en suivant ce lien : <a href="' + result.data.url + '">' + result.data.url + '</a></p>' +
                                    '<p><small><i>* Votre adresse e-mail n’est jamais publiée et permet de vérifier l’authenticité de l’évaluation. Vous avez la possibilité d\'indiquer votre nom et votre lieu de résidence. Ces données sont optionnelles et peuvent être modifiées. Le nom de famille est toujours réduit à son initiale pour des raisons de confidentialité.</i></small></p>' +
                                    '</div>';
                                window.location.href = 'mailto:' + email +
                                    '?cc=suivi@armoireplus.fr' +
                                    '&subject=Demande d\'avis pour la commande ' + order + ' sur armoireplus.fr' +
                                    '&html-body=' + body;
                            } else {
                                let body = '<div style="font-family:Arial,Helvetica,sans-serif;">' +
                                '<p>Bonjour,</p>' +
                                '<p>Nous sommes désolés de devoir vous annoncer un retard pour la commande ' + order + ' sur armoireplus.fr</p>' +
                                '<p>Notre service commercial reste à votre disposition pour toute question relative à votre commande.</p>' +
                                '<p style="margin-bottom:0;">Cordialement, l\'équipe Armoire PLUS</p>' +
                                '</div>';
                                window.location.href = 'mailto:' + email +
                                    '?cc=suivi@armoireplus.fr' +
                                    '&subject=Signalement d\'un retard pour la commande ' + order + ' sur armoireplus.fr' +
                                    '&html-body=' + body;
                            }
                        } else {
                            window.alert(result.data.message);
                        }
                    },
                    complete: function(data, status) {
                        $('.wrap-loading').css('display', 'none');
                    }
                });
            }
        }
    });

    // Note order
    $('a.link-editor').on('click', function(e) {
        let link = $(this),
            id = link.attr('data-id'),
            content = link.parent().find('span.order-note').html().split('<br>').join('\n');
        link.hide();
        link.parent().find('span.order-note').hide();
        link.parent().append('<textarea type="text" class="text-editor" rows="3">' + content + '</textarea>');
        link.parent().append('<button type="button" class="save-editor" data-id="'+ id +'">OK</button>');
    });

    // Save note
    $('body').on('click', 'button.save-editor', function(e) {
        let button = $(this),
            id = button.attr('data-id'),
            content = button.parent().find('textarea.text-editor').val().split('\n').join('<br>');
        $('.wrap-loading').css('display', 'block');
        $.ajax({
            url: 'admin-ajax.php',
            type: 'GET',
            data: 'action=tracking-note&note=' + encodeURIComponent(content) + '&order_id=' + id,
            dataType: 'json',
            success: function(result, status) {
                if (status == 'success') {
                    button.parent().find('span.order-note').html(content).show().tipTip({ content: content });
                    button.parent().find('a.link-editor').show();
                    button.parent().find('textarea.text-editor').remove();
                    button.parent().find('button.save-editor').remove();
                    button.remove();
                } else {
                    window.alert(result.data.message);
                }
            },
            complete: function(data, status) {
                $('.wrap-loading').css('display', 'none');
            }
        });
    });

    // Delivery order
    $('a.button-delivery').on('click', function(e) {
        e.preventDefault();
        let link = $(this),
            id = link.attr('data-id'),
            delivery = link.parent().hasClass('is-delivery') ? 0 : 1;
        if (!link.hasClass('disabled')) {
            $('.wrap-loading').css('display', 'block');
            $.ajax({
                url: 'admin-ajax.php',
                type: 'GET',
                data: 'action=tracking-delivery&delivery=' + delivery + '&order_id=' + id,
                dataType: 'json',
                success: function(result, status) {
                    if (status == 'success') {
                        let src = link.find('img').attr('src');
                        if (delivery) {
                            link.parent().addClass('is-delivery');
                            link.tipTip({ content: 'Retirer la livraison' });
                            link.removeClass('alert-no-delivery').addClass('alert-delivery');
                            link.find('img').attr('src', src.replace('tracking-no-delivery', 'tracking-delivery'));
                        } else {
                            link.parent().removeClass('is-delivery');
                            link.tipTip({ content: 'Confirmer la livraison' });
                            link.removeClass('alert-delivery').addClass('alert-no-delivery');
                            link.find('img').attr('src', src.replace('tracking-delivery', 'tracking-no-delivery'));
                        }
                    } else {
                        window.alert(result.data.message);
                    }
                },
                complete: function(data, status) {
                    $('.wrap-loading').css('display', 'none');
                }
            });
        }
    });

    // Follow order
    $('a.button-follow').on('click', function(e) {
        e.preventDefault();
        let link = $(this),
            id = link.attr('data-id'),
            follow = 1;
        if (link.parent().hasClass('is-litigation')) {
            follow = 0;
        } else if (link.parent().hasClass('is-follow')) {
            follow = 2;
        }
        $('.wrap-loading').css('display', 'block');
        $.ajax({
            url: 'admin-ajax.php',
            type: 'GET',
            data: 'action=tracking-follow&follow=' + follow + '&order_id=' + id,
            dataType: 'json',
            success: function(result, status) {
                if (status == 'success') {
                    let src = link.find('img').attr('src');
                    if (follow == 2) {
                        link.parent().addClass('is-litigation');
                        link.closest('tr').addClass('tr-litigation');
                        link.find('img').attr('src', src.replace('tracking-follow', 'tracking-litigation'));
                    } else if (follow == 1) {
                        link.parent().addClass('is-follow');
                        link.closest('tr').addClass('tr-follow');
                        link.find('img').attr('src', src.replace('tracking-no-follow', 'tracking-follow'));
                    } else {
                        link.parent().removeClass('is-follow');
                        link.parent().removeClass('is-litigation');
                        link.closest('tr').removeClass('tr-follow');
                        link.closest('tr').removeClass('tr-litigation');
                        link.find('img').attr('src', src.replace('tracking-litigation', 'tracking-no-follow'));
                    }
                } else {
                    window.alert(result.data.message);
                }
            },
            complete: function(data, status) {
                $('.wrap-loading').css('display', 'none');
            }
        });
    });

    // Check follow line
    function checkFollow() {
        $('table.wp-list-table').find('tbody#the-list > tr').each(function(i) {
            if ($(this).find('td.detail .order-action > div').hasClass('is-follow')) {
                $(this).addClass('tr-follow');
            } else if ($(this).find('td.detail .order-action > div').hasClass('is-litigation')) {
                $(this).addClass('tr-litigation');
            }
        });
    }

    // Launch follow
    checkFollow();

});

