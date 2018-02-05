<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Triad_gdpr
{
    public function consent()
    {
        if (isset($_COOKIE['triad_gdpr_consent']) && $_COOKIE['triad_gdpr_consent'] == 'yes') {
            return 'yes';
        }
        return 'no';
    }

    public function script()
    {
        $ext = new Triad_gdpr_ext();
        $ext->loadSettings();

        $ext->settings['revoke_html'] = str_replace("\n", '', $ext->settings['revoke_html']);
        $ext->settings['revoke_html'] = str_replace("'", "\'", $ext->settings['revoke_html']);

        $ext->settings['consent_html'] = str_replace("\n", '', $ext->settings['consent_html']);
        $ext->settings['consent_html'] = str_replace("'", "\'", $ext->settings['consent_html']);

        return ee('View')->make('triad_gdpr:frontend')->render($ext->settings);
    }
}
