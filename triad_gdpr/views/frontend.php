<?=!empty($style) ? "<style>$style</style>" : ''?>
<?php
$cookiePrefix  = ee()->config->item('cookie_prefix');
if (empty($cookiePrefix)) {
    $cookiePrefix = 'exp';
}
?>

<!-- if GTM value set then run code for  consent mode. 
Make sure to add specific consents in the lists below if required based on GTM requirements-->
<?php if (!empty($gtm_measurement_id)) { ?>
    <!-- Google Tag Manager -->
    <script defer>
    
        (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','<?=$gtm_measurement_id; ?>');
        
   
        window.dataLayer = window.dataLayer || []; function gtag() { dataLayer.push(arguments); }
        if(localStorage.getItem('consentMode') === null) {
            gtag('consent', 'default', {
                'ad_user_data': 'denied',
                'ad_personalization': 'denied',
                'ad_storage': 'denied',
                'analytics_storage': 'denied',
                'wait_for_update': 500,
            });
        } else {
            gtag('consent', 'default', JSON.parse(localStorage.getItem('consentMode')));
        }	
        
        function setConsent(consent){
            const consentMode = {
                'ad_user_data': consent.necessary ? 'granted' : 'denied',
                'ad_personalization': consent.necessary ? 'granted' : 'denied',
                'ad_storage': consent.marketing ? 'granted' : 'denied',
                'analytics_storage': consent.analytics ? 'granted' : 'denied',
            }
            gtag('consent', 'update', consentMode);
            localStorage.setItem('consentMode', JSON.stringify(consentMode));
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            const consentMode = localStorage.getItem('consentMode');

            if (consentMode) {
                const consentData = JSON.parse(consentMode);
                const values = Object.values(consentData);
                const allDenied = values.every(value => value === 'denied');

                if (!allDenied) {
                    // At least one value is not 'denied'
                    <?php if (!empty($revoke_html)): ?>
                        window.document.body.insertAdjacentHTML('beforeend', '<?=$revoke_html?>');
                    <?php else: ?>
                        window.document.body.insertAdjacentHTML('beforeend', '<div class="triad_gdpr" id="triad_gdpr_revoke"><p><?=$revoke_message?></p><button id="triad_gdpr_revoke_btn">Remove Cookies</button></div>');
                    <?php endif;?>
                } else {
                    // All values are 'denied'
                    <?php if (!empty($consent_html)): ?>
                        window.document.body.insertAdjacentHTML('beforeend', '<?=$consent_html?>');
                    <?php else: ?>
                        window.document.body.insertAdjacentHTML('beforeend', '<div class="triad_gdpr gdpr-consent-message" id="triad_gdpr_consent"><p><?=$consent_message?></p><button id="triad_gdpr_consent_btn">Allow Cookies</button></div>');
                    <?php endif;?>
                }
            } else {
                <?php if (!empty($consent_html)): ?>
                    window.document.body.insertAdjacentHTML('beforeend', '<?=$consent_html?>');
                <?php else: ?>
                    window.document.body.insertAdjacentHTML('beforeend', '<div class="triad_gdpr gdpr-consent-message" id="triad_gdpr_consent"><p><?=$consent_message?></p><button id="triad_gdpr_consent_btn">Allow Cookies</button></div>');
                <?php endif;?>
            }

            const grantedButton = document.getElementById('triad_gdpr_consent_btn');
            const revokedButton = document.getElementById('triad_gdpr_revoke_btn');
            const banner = document.getElementById("triad_gdpr");

            if (grantedButton) {
                grantedButton.addEventListener("click", function() {
                    setConsent({
                        necessary: true,
                        marketing: true,
                        analytics: true,
                    });
                });
            }

            if (revokedButton) {
                revokedButton.addEventListener("click", function() {
                    setConsent({
                        necessary: false,
                        marketing: false,
                        analytics: false,
                    });
                });
            }
        });
        
    </script>
    <!-- End Google Tag Manager -->
<?php } else { ?>
<script>
    var gdpr_consent = false;

    var essentialCookies = [
        'PHPSESSID',
        'triad_gdpr_consent',
        '<?= $cookiePrefix ?>_anon',
        '<?= $cookiePrefix ?>_cp_last_site_id',
        '<?= $cookiePrefix ?>_csrf_token',
        '<?= $cookiePrefix ?>_flash',
        '<?= $cookiePrefix ?>_last_activity',
        '<?= $cookiePrefix ?>_last_visit',
        '<?= $cookiePrefix ?>_remember',
        '<?= $cookiePrefix ?>_sessionid',
        '<?= $cookiePrefix ?>_tracker',
        '<?= $cookiePrefix ?>_visitor_consents',
        'triad_gdpr_consent'
    ];

    function setCookie(cname, cvalue, exdays) {
        var d = new Date();
        d.setTime(d.getTime() + (exdays*24*60*60*1000));
        var expires = "expires="+ d.toUTCString();
        document.cookie = cname + "=" + cvalue + ";" + expires + "; domain=" + window.location.host + "; path=/;";
    }
    function deleteCookie(cname) {
        document.cookie = cname + '=no; expires=Thu, 01 Jan 1970 00:00:01 GMT; domain=' + window.location.host + '; path=/;';
    }
    function getCookie(name) {
        var value = "; " + document.cookie;
        var parts = value.split("; " + name + "=");
        if (parts.length == 2) return parts.pop().split(";").shift();
    }
    if (getCookie('triad_gdpr_consent') == 'yes') {
        gdpr_consent = true;
    }

    // hide/show content based on cookie status
    window.addEventListener('DOMContentLoaded', function(event) {
        if (gdpr_consent) {
            var elements = document.getElementsByClassName("gdpr-consent-message");
            while (elements.length > 0) {
                elements[0].parentNode.removeChild(elements[0]);
            }
        } else {
            <?php if ($essential_cookies == 'y'): ?>
                var optionalCookies = document.cookie.split(';');
                for (var i = 0; i < optionalCookies.length; i++) {
                    var ck = optionalCookies[i].split('=')[0];
                    if (essentialCookies.indexOf(ck.trim()) == -1) {
                        console.log(ck);
                        deleteCookie(ck);
                    }
                }
            <?php else: ?>
                var cookies = document.cookie.split(";");
                for (var i = 0; i < cookies.length; i++) {
                    deleteCookie(cookies[i].split("=")[0]);
                }
            <?php endif;?>

            var elements = document.getElementsByClassName("gdpr-consent-required");
            while (elements.length > 0) {
                elements[0].parentNode.removeChild(elements[0]);
            }
        }
        <?php if (!empty($revoke_html)): ?>
        window.document.body.insertAdjacentHTML('beforeend', '<?=$revoke_html?>');
        <?php else: ?>
        window.document.body.insertAdjacentHTML('beforeend', '<div class="triad_gdpr" id="triad_gdpr_revoke"><p><button id="triad_gdpr_revoke_btn">Remove Cookies</button><?=$revoke_message?></p></div>');
        <?php endif;?>
        <?php if (!empty($consent_html)): ?>
        window.document.body.insertAdjacentHTML('beforeend', '<?=$consent_html?>');
        <?php else: ?>
        window.document.body.insertAdjacentHTML('beforeend', '<div class="triad_gdpr gdpr-consent-message" id="triad_gdpr_consent"><p><button id="triad_gdpr_consent_btn">Allow Cookies</button><?=$consent_message?></p></div>');
        <?php endif;?>
    });

    document.addEventListener('click',function(event) {
        if (event.target.id == 'triad_gdpr_revoke_btn') {
            deleteCookie('triad_gdpr_consent');

            <?php if ($essential_cookies == 'y'): ?>
                var optionalCookies = document.cookie.split(';');
                for (var i = 0; i < optionalCookies.length; i++) {
                    var ck = optionalCookies[i].split('=')[0];
                    if (essentialCookies.indexOf(ck.trim()) == -1) {
                    deleteCookie(ck);
                    }
                }
            <?php else: ?>
                var cookies = document.cookie.split(";");
                for (var i = 0; i < cookies.length; i++) {
                    deleteCookie(cookies[i].split("=")[0]);
                }
            <?php endif;?>

            location.reload();
        }
    });

    document.addEventListener('click',function(event) {
        if (event.target.id == 'triad_gdpr_consent_btn') {
            setCookie('triad_gdpr_consent', 'yes', 365);
            location.reload();
        }
        if (event.target.id == 'triad_gdpr_dismiss_btn') {
            setCookie('triad_gdpr_dismiss', 'yes', 365);
            location.reload();
        }
    });
</script>
<?php } ?>
<?= $javascript ?>
