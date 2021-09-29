<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Triad_gdpr_ext
{
    public $settings = [];

    public function __construct($settings = '')
    {
        $this->loadSettings();
        $this->settings = $settings;
    }

    public function activate_extension()
    {
        ee()->db->insert('extensions', [
            'class' => __CLASS__,
            'method' => 'cookieConsent',
            'hook' => 'set_cookie_end',
            'settings' => serialize([
                'consent_message' => 'Do you consent to this website placing cookies on your computer?',
                'revoke_message' => 'This website is now using cookies placed on your computer, click here to remove them.',
                'javascript' => '<!-- place any javascript snippets here, they will be inserted once consent has been acquired. -->',
                'consent_html' => '',
                'revoke_html' => '',
                'essential_cookies' => 'n',
                'style' => '
.triad_gdpr {
    background: black;
    border: 1px solid white;
    font-size: 10px;
    padding: 8px;
    position: fixed;
    width: 100%;
    z-index: 999;
}
.triad_gdpr button {
    background: white;
    border: none;
    color: black;
    float: right;
    padding: 8px 12px;
}
.triad_gdpr p {
    color: white;
}
#triad_gdpr_consent {
    top: 0;
}
#triad_gdpr_revoke {
    bottom: 0;
}',
            ]),
            'priority' => 1,
            'version' => $this->version,
            'enabled' => 'y',
        ]);
    }

    public function cookieConsent($data)
    {
        $cookiePrefix  = ee()->config->item('cookie_prefix');
        if(empty($cookiePrefix)){
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

        if (!isset($_COOKIE['triad_gdpr_consent']) || $_COOKIE['triad_gdpr_consent'] != 'yes') {
            if (REQ != 'CP') {
                foreach ($_COOKIE as $key => $value) {
                    if($this->settings['essential_cookies'] == 'y' && in_array($key, $essentialCookies)){
                        continue;
                    }
                    setcookie($key, $value, time() - 3600, '/');
                    header("HTTP/1.1 412");
                }
                ee()->extensions->end_script = true;
                return [];
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

    private function loadSettings() {
        $settings = include PATH_THIRD . 'echelon/addon.setup.php';

        foreach ($settings as $_key => $_setting) {
            $this->{$_key} = $_setting;
        }
    }

    public function settings()
    {
        $out = [
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
