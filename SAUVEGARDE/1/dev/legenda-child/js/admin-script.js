/* Admin script */
"use strict";
jQuery(document).ready(function($) {

    // Modal order page
    if ($('#woocommerce-order-data.postbox').length) {
        let modal_style = `
        <style>
        .modal-alert {
          display: none;
          justify-content: center;
          align-items: center;
          position: fixed;
          z-index: 9999999;
          left: 0;
          top: 0;
          width: 100%;
          height: 100%;
          overflow: auto;
          background-color: rgb(0,0,0);
          background-color: rgba(0,0,0,0.4);
        }
        .modal-alert .modal-content {
          width: 80%;
          border-radius: 5px;
          border: 1px solid #888;
          background-color: #fbfbfb;
          box-shadow: 5px 5px 10px rgba(0, 0, 0, 0.3);
        }
        .modal-alert .modal-header {
          padding: 20px;
          background-color: #a40909;
          border-radius: 5px 5px 0 0;
        }
        .modal-alert .modal-body {
          padding: 20px;
        }
        .modal-alert h2 {
          position: relative;
          width: 90%;
          color: #fff;
          font-size: 1.5em;
          margin: 0 0 0 50px;
        }
        .modal-alert h2:before {
            position: absolute;
            content: "⚠";
            font-size: 1.5em;
            color: #a40909;
            top: -7px;
            left: -50px;
            background-color: #fff;
            padding: 7px 5px 5px;
            height: 25px;
            border-radius: 30px;
        }
        .modal-alert p {
          font-size: 1.2em;
          font-weight: bold;
          margin: 0;
        }
        .modal-alert ul {
          margin: 0 0 0 20px;
          list-style: disc;
        }
        .modal-alert p b {
          color: #a40909;
        }
        .modal-alert .close {
          color: #fff;
          float: right;
          font-size: 28px;
          font-weight: bold;
        }
        .modal-alert .close:hover,
        .modal-alert .close:focus {
          color: black;
          text-decoration: none;
          cursor: pointer;
        }
        </style>`;
        let modal_alert = `
        <div class="modal-alert">
          <div class="modal-content">
            <div class="modal-header">
              <span class="close">&times;</span>
              <h2 class="title"></h2>
            </div>
            <div class="modal-body">
              <p class="content"></p>
            </div>
          </div>
        </div>`;
        $('head').append($(modal_style));
        $('body').append($(modal_alert));
        $('.modal-alert').find('span.close').on('click', function() {
            $('.modal-alert').css('display', 'none');
            $('#order_status').select2('open');
        });
        $('body').on('click', function(e) {
            if (e.target.getAttribute('class') == 'modal-alert') {
                $('.modal-alert').css('display', 'none');
                $('#order_status').select2('open');
            }
        });
        $('#order_status ~ .select2-container').on('click', function(e) {
            if ($('#order_status').val() == 'wc-ywraq-pending' || $('#order_status').val() == 'wc-ywraq-expired') {
                $('.modal-alert').find('.title').html('Avant de passer ce devis en commande en cours');
                $('.modal-alert').find('.content').html('<ul><li><b>Générer un numéro de commande</b> dans "Actions de Commande"</li><li><b>Indiquer les conditions de règlement</b> dans "Facturation"</li></ul>');
                $('.modal-alert').css('display', 'flex');
            }
        });
    }

    // Alert order list
    if ($('.wc_actions .wc-action-button-complete').length) {
        $('.wc-action-button-complete').on('click', function(e) {
            e.preventDefault();
            let redirect = false;
            let url = $(e.target).attr('href');
            let parent = $(e.target).closest('tr.level-0');
            if (parent.find('td.bank_transfer span').length) {
                const total = Number(parent.find('td.order_total .amount').html().replace(/[^0-9.]/g, ''));
                const payment = Number(parent.find('td.bank_transfer span').html().replace(/,/, '.').replace(/[^0-9.]/g, ''));
                if (total !== payment) {
                    if (window.confirm('Vérifiez la facture en passant la commande en terminée.')) {
                        redirect = true;
                    }
                } else {
                    redirect = true;
                }
            } else {
                redirect = true;
            }
            if (redirect) {
                window.location.href = url;
            }
        });
    }

});
