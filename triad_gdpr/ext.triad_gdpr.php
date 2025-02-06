<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Triad_gdpr_ext
{
    public $settings = [];
    public string $version;
    public string $cookiePrefix;
    public array $necessaryCookies = [];
    public $name = '';
    public $author = '';
    public $author_url = '';
    public $description = '';
    public $namespace = '';
    public $settings_exist = 'y';

    public function __construct($settings = '')
    {
        $this->loadSetup();
        $this->settings = $settings;

        $this->cookiePrefix = ee()->config->item('cookie_prefix') ?: 'exp';

        $this->necessaryCookies = [
            'PHPSESSID',
            $this->cookiePrefix . '_anon',
            $this->cookiePrefix . '_cp_last_site_id',
            $this->cookiePrefix . '_csrf_token',
            $this->cookiePrefix . '_flash',
            $this->cookiePrefix . '_last_activity',
            $this->cookiePrefix . '_last_visit',
            $this->cookiePrefix . '_remember',
            $this->cookiePrefix . '_sessionid',
            $this->cookiePrefix . '_tracker',
            $this->cookiePrefix . '_visitor_consents',
            $this->cookiePrefix . '_viewtype',
            'triad_gdpr_consent_necessary',
        ];
    }

    public function activate_extension()
    {
        // Get all sites if MSM is enabled
        $sites = [];
        if (ee()->config->item('multiple_sites_enabled') === 'y') {
            ee()->db->select('site_id, site_label');
            $query = ee()->db->get('sites');
            foreach ($query->result_array() as $row) {
                $sites[$row['site_id']] = ['gtm_code' => ''];
            }
        } else {
            $sites[1] = ['gtm_code' => '']; // Default site
        }

        ee()->db->insert('extensions', [
            'class' => __CLASS__,
            'method' => 'cookieConsent',
            'hook' => 'set_cookie_end',
            'settings' => serialize([
                'consent_html' => file_get_contents(__DIR__ . '/defaults/consent.html'),
                'manage_html' => file_get_contents(__DIR__ . '/defaults/manage.html'),
                'necessary_cookies' => 'n',
                'style' => file_get_contents(__DIR__ . '/defaults/styles.css'),
                'sites' => $sites
            ]),
            'priority' => 1,
            'version' => $this->version,
            'enabled' => 'y',
        ]);
    }

    public function cookieConsent($data)
    {
        if(!array_key_exists('deletedCookies', $_REQUEST)) {
            $_REQUEST['deletedCookies'] = false;
        }

        // consent isn't granted
        if (($_COOKIE['triad_gdpr_consent_necessary'] ?? 'no') != 'yes') {
            // this isn't a control panel request
            if (REQ != 'CP') {
                if(!$_REQUEST['deletedCookies']) {
                    foreach($this->necessaryCookies as $_name) {
                        setcookie($_name, '', time() - 3600, '/',  $_SERVER['SERVER_NAME']);
                        $_REQUEST['deletedCookies'] = true;
                    }
                }

                if(in_array($this->cookiePrefix . '_' . $data['name'], $this->necessaryCookies) || in_array($data['name'], $this->necessaryCookies)) {
                    $data['value'] = '';
                    $data['expire'] = 1;
                }

                return $data;
            } else {
                setcookie('triad_gdpr_consent_necessary', 'yes', time() + (365*24*60*60), '/', $_SERVER['SERVER_NAME']);
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
        ee()->lang->loadfile('triad_gdpr');
        
        $settings = [
            'style' => ['t', ['rows' => '20'], ''],
            'javascript' => ['t', ['rows' => '20'], ''],
            'consent_html' => ['t', ['rows' => '20'], ''],
            'manage_html' => ['t', ['rows' => '20'], ''],
            'necessary_cookies' => ['r', ['y' => "Yes", 'n' => "No"], 'n']
        ];

        // Add GTM fields for each site if MSM is enabled
        if (ee()->config->item('multiple_sites_enabled') === 'y') {
            ee()->db->select('site_id, site_label, site_name');
            $query = ee()->db->get('sites');
            foreach ($query->result_array() as $row) {
                $settings['gtm_gtag_id_' . $row['site_name']] = [
                    'i',
                    '',
                    '',
                    sprintf(lang('gtm_gtag_id_format'), ucfirst($row['site_name']))
                ];
            }
        } else {
            // Single site mode - just one GTM field
            $settings['gtm_gtag_id'] = ['i', '', '', lang('gtm_gtag_id')];
        }

        return $settings;
    }
}
