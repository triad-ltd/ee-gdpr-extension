<?=!empty($style) ? "<style>$style</style>" : ''?>
<?php
$cookiePrefix  = ee()->config->item('cookie_prefix');
if (empty($cookiePrefix)) {
    $cookiePrefix = 'exp';
}
?>
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

<?= $javascript ?>
