<?=!empty($style) ? "<style>$style</style>" : ''?>
<?php
    $cookiePrefix  = ee()->config->item('cookie_prefix');
    if (empty($cookiePrefix)) {
        $cookiePrefix = 'exp';
    }
?>
<script>
    // Backwards Compatability functions
    const deleteAllCookies = () => {
        const cookies = document.cookie.split(";");

        for (let i = 0; i < cookies.length; i++) {
            const cookie = cookies[i];
            const eqPos = cookie.indexOf("=");
            const name = eqPos > -1 ? cookie.substr(0, eqPos).trim() : cookie.trim();

            // Delete the cookie on all possible paths and domains
            document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/";
            document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/;domain=" + location.hostname;
            document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/;domain=." + location.hostname;

            // Attempt to delete third-party cookies
            const domainParts = location.hostname.split('.');
            for (let j = domainParts.length - 1; j >= 0; j--) {
                const domain = domainParts.slice(j).join('.');
                document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/;domain=." + domain;
            }

            // Attempt to delete cookies on common third-party domains
            const commonThirdPartyDomains = ['bing.com', 'google.com', 'facebook.com', 'youtube.com', 'twitter.com', 'linkedin.com'];
            for (const domain of commonThirdPartyDomains) {
                document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/;domain=." + domain;
            }
        }
    };

    const deleteCookie = (name) => {
        document.cookie = name + '=; expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/;';
    };

    const deleteNonEssentialCookies = () => {
        var optionalCookies = document.cookie.split(';');
        for (var i = 0; i < optionalCookies.length; i++) {
            var ck = optionalCookies[i].split('=')[0];
            if (essentialCookies.indexOf(ck.trim()) == -1) {
                deleteCookie(ck);
            }
        }
    };

    function getCookie(name) {
        var value = "; " + document.cookie;
        var parts = value.split("; " + name + "=");
        if (parts.length == 2) return parts.pop().split(";").shift();
    }

    const setCookie = (name, value, days) => {
        let expires = "";
        if (days) {
            const date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "") + expires + "; path=/";
    };

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
</script>
<?php if (!empty($gtm_key)) { ?>
<script>
    // Initialize dataLayer and gtag only if GTM code exists
    window.dataLayer = window.dataLayer || []; 
    function gtag() { dataLayer.push(arguments); }

    // Initialize consent mode, using previous choices
    const savedConsentMode = localStorage.getItem('consentMode');
    const defaultConsentMode = savedConsentMode ? JSON.parse(savedConsentMode) : {
        'ad_user_data': 'denied',
        'ad_personalization': 'denied',
        'ad_storage': 'denied',
        'analytics_storage': 'denied'
    };

    gtag('consent', 'default', defaultConsentMode);

    // Load GTM
    (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','<?=htmlspecialchars($gtm_key, ENT_QUOTES)?>');

    const necessaryCookiesAlwaysAllowed = <?=$necessary_cookies == 'y' ? 'true' : 'false'?>;
    const types = ['analytics', 'performance', 'necessary'];
    const CONSENT_STORAGE_KEY = 'triad_gdpr_consent';
    const DISMISS_STORAGE_KEY = 'triad_gdpr_dismiss';

    // Storage helper functions
    function setStorageItem(key, value, useSession = false) {
        const storage = useSession ? sessionStorage : localStorage;
        storage.setItem(key, JSON.stringify(value));
    }

    function getStorageItem(key, useSession = false) {
        const storage = useSession ? sessionStorage : localStorage;
        try {
            return JSON.parse(storage.getItem(key));
        } catch {
            return null;
        }
    }

    function getConsentStatus(type) {
        const consents = getStorageItem(CONSENT_STORAGE_KEY) || {};
        return consents[type] === true;
    }

    function setConsentStatus(type, value) {
        const consents = getStorageItem(CONSENT_STORAGE_KEY) || {};
        if (type === 'necessary' && necessaryCookiesAlwaysAllowed) {
            consents[type] = true;
        } else {
            consents[type] = value;
        }
        setStorageItem(CONSENT_STORAGE_KEY, consents);
        updateConsentMode();
    }

    // Update Consents
    function updateConsentMode() {
        const storedConsents = getStorageItem(CONSENT_STORAGE_KEY) || {};
        const consentMode = {
            'ad_user_data': storedConsents['analytics'] ? 'granted' : 'denied',
            'ad_personalization': storedConsents['analytics'] ? 'granted' : 'denied',
            'ad_storage': storedConsents['analytics'] ? 'granted' : 'denied',
            'analytics_storage': storedConsents['analytics'] ? 'granted' : 'denied'
        };

        gtag('consent', 'update', consentMode);
        localStorage.setItem('consentMode', JSON.stringify(consentMode));

        // Update UI toggle states
        document.querySelectorAll('[data-triad-gdpr-cookie]').forEach(toggle => {
            const type = toggle.dataset.triadGdprCookie.replace('triad_gdpr_consent_', '');
            toggle.checked = storedConsents[type] === true;

            if (type === 'necessary' && necessaryCookiesAlwaysAllowed) {
                toggle.disabled = true;
                toggle.checked = true;
            }
        });
    }

    function acceptConsent() {
        setConsentStatus('necessary', true);
        setConsentStatus('analytics', true);
        dismissConsent();
    }

    function revokeConsent() {
        setConsentStatus('necessary', false);
        setConsentStatus('analytics', false);
        deleteAllCookies();
        dismissConsent();
    }

    function savePreferences() {
        const consentTypes = ['analytics', 'performance', 'necessary'];
        const consents = {};

        consentTypes.forEach(type => {
            const toggle = document.querySelector(`[data-triad-gdpr-cookie="triad_gdpr_consent_${type}"]`);
            if (toggle) {
                consents[type] = toggle.checked;
            }
        });

        // If necessary cookies are disabled, delete all cookies
        if (!consents['necessary']) {
            deleteAllCookies();
        } else if (necessaryCookiesAlwaysAllowed) {
            // If only non-essential cookies are disabled
            deleteNonEssentialCookies();
        }

        setStorageItem(CONSENT_STORAGE_KEY, consents);
        updateConsentMode(); // Ensures UI updates after saving
        dismissConsent();
    }

    function removeConsentDismissal() {        
        // Re-insert consent HTML if not present
        if (!document.getElementById('cookies-popup')) {
            const consentHtml = `<?=str_replace(["\n", "\r"], '', addslashes($consent_html))?>`;
            document.body.insertAdjacentHTML('beforeend', consentHtml);
            
            // Reinitialize manage cookies functionality
            const manageButton = document.getElementById('manage-cookies-button');
            const initialSettings = document.getElementById('initial-cookies-settings');
            const manageSettings = document.getElementById('manage-cookies-settings');
            const backButton = document.getElementById('js-triad_gdpr_back');
            
            if (manageButton) {
                manageButton.addEventListener('click', () => {
                    initialSettings.style.display = 'none';
                    manageSettings.style.display = 'block';
                    backButton.parentElement.style.display = 'block';
                });
            }

            if (backButton) {
                backButton.addEventListener('click', () => {
                    initialSettings.style.display = 'block';
                    manageSettings.style.display = 'none';
                    backButton.parentElement.style.display = 'none';
                });
            }
        }
    }

    function dismissConsent() {
        setStorageItem(DISMISS_STORAGE_KEY, true);
        let consent = localStorage.getItem('triad_gdpr_consent');
        if (consent) {
            consent = JSON.parse(consent);
            if(consent.necessary){
                setCookie('triad_gdpr_consent_necessary', 'yes', 365);
            }
        }
        window.location.reload();
    }

    function updateConsentToggles() {
        const storedConsents = getStorageItem(CONSENT_STORAGE_KEY) || {}; 
        document.querySelectorAll('[data-triad-gdpr-cookie]').forEach(toggle => {
            const type = toggle.dataset.triadGdprCookie.replace('triad_gdpr_consent_', '');
            const consentGiven = storedConsents[type] === true;

            toggle.checked = consentGiven;

            if (type === 'necessary' && necessaryCookiesAlwaysAllowed) {
                toggle.disabled = true;
                toggle.checked = true;
            }

            if (toggle instanceof HTMLInputElement && toggle.type === 'checkbox') {
                try {
                    // Ensure straight quotes are used for 'change'
                    const event = new Event('change');
                    toggle.dispatchEvent(event);
                } catch (error) {
                    console.warn('Failed to dispatch change event:', error);
                }
            }
        });
    }

    function ensureElementsExist(callback) {
        requestAnimationFrame(() => {
            if (document.querySelector('[data-triad-gdpr-cookie]')) {
                callback();
            } else {
                ensureElementsExist(callback);
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        
        const storedConsents = getStorageItem(CONSENT_STORAGE_KEY) || {};
        const necessaryEnabled = storedConsents['necessary'] === true;
        document.querySelectorAll('.js-triad-gdpr-necessary-required').forEach(el => {
            if (!necessaryEnabled) {
                el.remove();
            }
        });
        document.querySelectorAll('.js-triad-gdpr-not-necessary-only').forEach(el => {
            if (necessaryEnabled) {
                el.remove();
            }
        });
        
        // Always enforce necessary cookies if required
        if (necessaryCookiesAlwaysAllowed) {
            setConsentStatus('necessary', true);
        }

        const hasDismissed = getStorageItem(DISMISS_STORAGE_KEY);
        if (!hasDismissed) {
            const consentHtml = `<?=str_replace(["\n", "\r"], '', addslashes($consent_html))?>`; 
            document.body.insertAdjacentHTML('beforeend', consentHtml);

            const manageButton = document.getElementById('manage-cookies-button');
            const initialSettings = document.getElementById('initial-cookies-settings');
            const manageSettings = document.getElementById('manage-cookies-settings');
            const backButton = document.getElementById('js-triad_gdpr_back');

            if (manageButton) {
                manageButton.addEventListener('click', () => {
                    initialSettings.style.display = 'none';
                    manageSettings.style.display = 'block';
                    backButton.parentElement.style.display = 'block';
                });
            }

            if (backButton) {
                backButton.addEventListener('click', () => {
                    initialSettings.style.display = 'block';
                    manageSettings.style.display = 'none';
                    backButton.parentElement.style.display = 'none';
                });
            }

                updateConsentMode();
        }

        ensureElementsExist(() => {
            updateConsentToggles();
        });
    });
</script>
<?php } else { ?>
<script>
    // hide/show content based on cookie status
    window.addEventListener('DOMContentLoaded', function(event) {
        <?php if (!empty($revoke_html)): ?>
        window.document.body.insertAdjacentHTML('beforeend', '<?=str_replace("'","\'",$revoke_html)?>');
        <?php else: ?>
        window.document.body.insertAdjacentHTML('beforeend', '<div class="triad_gdpr gdpr-consent-required" id="triad_gdpr_revoke"><p><button id="triad_gdpr_revoke_btn">Remove Cookies</button><?=$revoke_message?></p></div>');
        <?php endif;?>
        <?php if (!empty($consent_html)): ?>
        window.document.body.insertAdjacentHTML('beforeend', '<?=str_replace("'","\'",$consent_html)?>');
        <?php else: ?>
        window.document.body.insertAdjacentHTML('beforeend', '<div class="triad_gdpr gdpr-consent-message" id="triad_gdpr_consent"><p><button id="triad_gdpr_consent_btn">Allow Cookies</button><?=$consent_message?></p></div>');
        <?php endif;?>
    });

    document.addEventListener('click',function(event) {
        if (event.target.id == 'triad_gdpr_revoke_btn') {
            deleteCookie('triad_gdpr_consent');

            <?php if ($essential_cookies == 'y'): ?>
                deleteNonEssentialCookies();
            <?php else: ?>
                deleteAllCookies();
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