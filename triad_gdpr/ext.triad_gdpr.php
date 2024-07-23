<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Triad_gdpr_ext
{
    public $settings = [];

    public function __construct($settings = '')
    {
        $this->loadSetup();
        $this->settings = $settings;
    }

    public function activate_extension()
    {
        ee()->db->insert('extensions', [
            'class' => __CLASS__,
            'method' => 'cookieConsent',
            'hook' => 'set_cookie_end',
            'settings' => serialize([
                'gtm_gtag_id' => '',
                'consent_message' => 'Do you consent to this website placing cookies on your computer?',
                'revoke_message' => 'This website is now using cookies placed on your computer, click here to remove them.',
                'javascript' => '<!-- place any javascript snippets here, they will be inserted once consent has been acquired. -->',
                'consent_html' => '',
                'revoke_html' => '',
                'essential_cookies' => 'n',
                'style' => '
                    body {
                        padding-bottom: 90px;
                        position: relative;
                    }
                    .triad_gdpr {
                        background: black;
                        border: 1px solid white;
                        font-size: 16px;
                        padding: 30px;
                        display: flex;
                        justify-content: space-between;
                        align-items: middle;
                        width: calc(100% - 62px);
                        z-index: 10000;
                    }
                    .triad_gdpr button {
                        background: white;
                        border: none;
                        color: black;
                        padding: 8px 12px;
                        cursor: pointer;
                    }
                    .triad_gdpr p {
                        color: white;
                        margin: 0;
                    }
                    #triad_gdpr_consent {
                        bottom: 0;
                        position: fixed;
                    }
                    #triad_gdpr_revoke {
                        position: absolute;
                        bottom: 0;
                    }
                ',
            ]),
            'priority' => 1,
            'version' => $this->version,
            'enabled' => 'y',
        ]);
    }

    public function cookieConsent($data)
    {
        $cookiePrefix  = ee()->config->item('cookie_prefix');

        if (empty($cookiePrefix)) {
            $cookiePrefix = 'exp';
        }

        $essentialCookies = [
            'PHPSESSID',
            'triad_gdpr_consent',
            $cookiePrefix . '_anon',
            $cookiePrefix . '_cp_last_site_id',
            $cookiePrefix . '_csrf_token',
            $cookiePrefix . '_flash',
            $cookiePrefix . '_last_activity',
            $cookiePrefix . '_last_visit',
            $cookiePrefix . '_remember',
            $cookiePrefix . '_sessionid',
            $cookiePrefix . '_tracker',
            $cookiePrefix . '_visitor_consents',
            'triad_gdpr_consent'
        ];

        // consent isn't granted
        if (!isset($_COOKIE['triad_gdpr_consent']) || $_COOKIE['triad_gdpr_consent'] != 'yes') {
            // this isn't a control panel request
            if (REQ != 'CP') {
                // loop through current cookies and remove
                foreach ($_COOKIE as $key => $value) {
                    // unless 'essential' option is ticked
                    if ($this->settings['essential_cookies'] == 'y' && in_array($key, $essentialCookies)) {
                        continue;
                    }
                    setcookie($key, $value, time() - 3600, '/');
                }

                // void the current cookie attempt unless 'essential' option is ticked
                if ($this->settings['essential_cookies'] == 'n') {
                    $data['value'] = '';
                    $data['expire'] = 1;
                }

                return $data;
            } else {
                setcookie('triad_gdpr_consent', 'yes', 0, '/');
            }
        }

        return $data;
    }

    public function disable_extension()
    {
        ee()->db->where('class', __CLASS__);
        ee()->db->delete('extensions');
    }

    public function loadSetup() {
        $settings = include PATH_THIRD . 'triad_gdpr/addon.setup.php';

        foreach ($settings as $_key => $_setting) {
            $this->{$_key} = $_setting;
        }
    }

    public function loadSettings() {
        if (empty($this->settings)) {
            $query = ee()->db->select('settings')
                ->where('class', __CLASS__)
                ->limit(1)
                ->get('extensions');

            $this->settings = unserialize($query->row('settings'));
        }
    }

    public function settings()
    {
        $out = [
            'gtm_gtag_id' => ['i', '', ''],
            'consent_message' => ['t', ['rows' => '20'], ''],
            'revoke_message' => ['t', ['rows' => '20'], ''],
            'javascript' => ['t', ['rows' => '20'], ''],
            'style' => ['t', ['rows' => '20'], ''],
            'consent_html' => ['t', ['rows' => '20'], ''],
            'revoke_html' => ['t', ['rows' => '20'], ''],
            'essential_cookies' => ['r', ['y' => "Yes", 'n' => "No"], 'n']
        ];
        return $out;
    }
}
