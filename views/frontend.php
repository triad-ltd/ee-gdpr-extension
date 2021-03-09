<?=!empty($style) ? "<style>$style</style>" : ''?>
<?php 
$cookiePrefix  = ee()->config->item('cookie_prefix'); 
if(empty($cookiePrefix)){
    $cookiePrefix = 'exp';
}
?>

<script>

    var essentialCookies = [
        'PHPSESSID',
        'triad_gdpr_consent',
        '<?= $cookiePrefix?>_csrf_token',
        '<?= $cookiePrefix?>_last_activity',
        '<?= $cookiePrefix?>_last_visit',
        '<?= $cookiePrefix?>_tracker',
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

    <?php if (isset($_COOKIE['triad_gdpr_consent']) && $_COOKIE['triad_gdpr_consent'] == 'yes'): ?>
        <?php if (!empty($revoke_html)): ?>
        document.write('<?=$revoke_html?>');
        <?php else: ?>
        document.write('<div class="triad_gdpr" id="triad_gdpr_revoke"><p><button id="triad_gdpr_revoke_btn">Remove Cookies</button><?=$revoke_message?></p></div>');
        <?php endif;?>
        document.addEventListener('click',function(event) {
            if (event.target.id == 'triad_gdpr_revoke_btn') {
                deleteCookie('triad_gdpr_consent');
                <?php if ($essential_cookies == 'y'): ?>
                    var optionalCookies = document.cookie.split(';');                
                    for (var i = 0; i < optionalCookies.length; i++){
                        var ck = optionalCookies[i].split('=')[0];
                        if(essentialCookies.indexOf(ck) == -1){
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
    <?php else: ?>
        <?php if (!empty($consent_html)): ?>
        document.write('<?=$consent_html?>');
        <?php else: ?>
        document.write('<div class="triad_gdpr" id="triad_gdpr_consent"><p><button id="triad_gdpr_consent_btn">Allow Cookies</button><?=$consent_message?></p></div>');
        <?php endif;?>
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
    <?php endif;?>

</script>
<?php if (isset($_COOKIE['triad_gdpr_consent']) && $_COOKIE['triad_gdpr_consent'] == 'yes'): ?>
<?=$javascript?>
<?php endif;?>
