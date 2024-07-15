<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Triad_gdpr
{
    public function __construct($settings = '')
    {
        $this->settings = $settings;
        $this->loadSettings();
    }

    public function consent()
    {
        if (isset($_COOKIE['triad_gdpr_consent']) && $_COOKIE['triad_gdpr_consent'] == 'yes') {
            return 'yes';
        }

        return 'no';
    }

    public function consent_essential()
    {
        if ($this->settings['essential_cookies'] == 'y' || $this->consent() == 'yes') {
            return 'yes';
        }

        return 'no';
    }

    private function loadSettings()
    {
        $ext = new Triad_gdpr_ext();
        $ext->loadSettings();
        $this->settings = $ext->settings;
    }

    public function notification_dismissed()
    {
        if (isset($_COOKIE['triad_gdpr_dismiss']) && $_COOKIE['triad_gdpr_dismiss'] == 'yes')  {
            return 'yes';
        }

        return 'no';
    }

    public function script()
    {
        $this->settings['revoke_html'] = str_replace("\n", '', $this->settings['revoke_html']);
        $this->settings['revoke_html'] = str_replace("'", "\'", $this->settings['revoke_html']);

        $this->settings['consent_html'] = str_replace("\n", '', $this->settings['consent_html']);
        $this->settings['consent_html'] = str_replace("'", "\'", $this->settings['consent_html']);

        $this->settings['gtm_measurement_id'] = str_replace("'", "\'", $this->settings['gtm_measurement_id']);       
        return ee('View')->make('triad_gdpr:frontend')->render($this->settings);
    }

    public function noscript()
    {   
        $this->settings['gtm_measurement_id'] = str_replace("'", "\'", $this->settings['gtm_measurement_id']);       
        return ee('View')->make('triad_gdpr:body-frontend')->render($this->settings);
    }
}
