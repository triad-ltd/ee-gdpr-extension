<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Triad_gdpr
{
    public $settings;

    private Triad_gdpr_ext $ext;

    public function __construct($settings = '')
    {
        $this->ext = new Triad_gdpr_ext();
        $this->ext->loadSettings();
        $this->settings = $this->ext->settings;
    }

    public function notification_dismissed()
    {
        return $this->checkCookie('triad_gdpr_dismiss');
    }

    public function necessary_cookies_allowed(): bool
    {
        return $this->settings['necessary_cookies'] == 'y' || $this->checkCookie('triad_gdpr_consent_necessary');
    }

    public function analytics_cookies_allowed(): bool
    {
        return $this->checkCookie('triad_gdpr_consent_analytics');
    }

    public function performance_cookies_allowed(): bool
    {
        return $this->checkCookie('triad_gdpr_consent_performance');
    }

    private function checkCookie(string $name): bool
    {
        return isset($_COOKIE[$name]) && $_COOKIE[$name] == 'yes';
    }

    public function script()
    {   
        $this->ext->loadSettings();  // Make sure settings are loaded
        $this->settings = array_merge($this->ext->settings, $this->settings);

        // Clean and sanitize HTML content
        $this->settings['consent_html'] = str_replace("\n", '', $this->settings['consent_html']);
        $this->settings['manage_html'] = str_replace("\n", '', $this->settings['manage_html']);

        // Get current site's shortname from the sites table
        $current_site_id = ee()->config->item('site_id');
        $current_site_name = 'default';
        
        if (ee()->config->item('multiple_sites_enabled') === 'y') {
            ee()->db->select('site_name');
            ee()->db->where('site_id', $current_site_id);
            $query = ee()->db->get('sites');
            if ($query->num_rows() > 0) {
                $current_site_name = $query->row('site_name');
            }
        }

        $gtm_key = ee()->config->item('multiple_sites_enabled') === 'y'
            ? 'gtm_gtag_id_' . $current_site_name
            : 'gtm_gtag_id';

        // Get the value from the settings array
        if (isset($this->settings[$gtm_key])) {
            $this->settings[$gtm_key] = str_replace("'", "\'", $this->settings[$gtm_key]);
        }

        $this->settings['necessaryCookies'] = $this->ext->necessaryCookies;
        $this->settings['gtm_key'] = $this->settings[$gtm_key];

        return ee('View')->make('triad_gdpr:frontend')->render($this->settings);
    }

    public function noscript()
    {   
        // Get current site's shortname from the sites table
        $current_site_id = ee()->config->item('site_id');
        $current_site_name = 'default';
        
        if (ee()->config->item('multiple_sites_enabled') === 'y') {
            ee()->db->select('site_name');
            ee()->db->where('site_id', $current_site_id);
            $query = ee()->db->get('sites');
            if ($query->num_rows() > 0) {
                $current_site_name = $query->row('site_name');
            }
        }

        $gtm_key = ee()->config->item('multiple_sites_enabled') === 'y'
            ? 'gtm_gtag_id_' . $current_site_name
            : 'gtm_gtag_id';

        // Get the value from the settings array
        if (isset($this->settings[$gtm_key])) {
            $this->settings[$gtm_key] = str_replace("'", "\'", $this->settings[$gtm_key]);
        }

        $this->settings['gtm_key'] = $this->settings[$gtm_key];

        return ee('View')->make('triad_gdpr:body-frontend')->render($this->settings);
    }
}