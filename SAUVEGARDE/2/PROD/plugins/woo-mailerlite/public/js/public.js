jQuery(document).ready(function(a) {
    const email = document.querySelector('#billing_email');
    const first_name_field = document.querySelector('#billing_first_name');
    const last_name_field = document.querySelector('#billing_last_name');
    const signup = document.querySelector('#woo_ml_subscribe');

    if (email !== null) {
        email.addEventListener('blur', (event) => {
            validateMLSub();
        });
    }

    if (first_name_field !== null) {
        first_name_field.addEventListener('blur', (event) => {
            if(first_name_field.value.length > 0) {
                validateMLSub();
            }
        });
    }

    if (last_name_field !== null) {
        last_name_field.addEventListener('blur', (event) => {
            if(last_name_field.value.length > 0) {
                validateMLSub();
            }
        });
    }

    if (signup !== null) {
        signup.addEventListener('click', (event) => {
            validateMLSub();
        });
    }

    function validateMLSub() {
        if(email !== null && email.value.length > 0) {
            checkoutMLSub();
        }
    }

    function checkoutMLSub() {
        const accept_marketing = document.querySelector('#woo_ml_subscribe').checked;

        let first_name = '';
        let last_name = '';

        if (first_name_field !== null) {
            first_name = first_name_field.value;
        }

        if (last_name_field !== null) {
            last_name = last_name_field.value;
        }

        jQuery.ajax({
            url: woo_ml_public_post.ajax_url,
            type: "post",
            data: {
                action: "post_woo_ml_email_cookie",
                email: email.value,
                signup: accept_marketing,
                language: woo_ml_public_post.language,
                first_name: first_name,
                last_name: last_name,
            }
        })
    }
});