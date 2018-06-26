<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Triad_gdpr_ext
{
    public $version = '0.0.3';
    public $settings = array();

    public function __construct($settings = '')
    {
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
                'javascript' => '<!-- place any javascript snippets here, they will be inserted once concent has been acquired. -->',
                'consent_html' => '',
                'revoke_html' => '',
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
            'version' => '0.0.3',
            'enabled' => 'y',
        ]);
        

        ee()->db->insert('extensions', [
            'class' => __CLASS__,
            'enabled' => 'y',
            'hook' => 'channel_entries_row',
            'method' => 'checkEntryRow',
            'priority' => 1,
            'settings' => '',
            'version' => '0.0.3',
            'enabled' => 'n'
        ]);
    }

    public function disable_extension()
    {
        ee()->db->where('class', __CLASS__);
        ee()->db->delete('extensions');
    }

    public function cookieConsent($data)
    {
        // is it a control panel request? if so, force consent.
        if (stripos($_SERVER['REQUEST_URI'], '/'.ee()->config->item('system_folder').'login') !== false) {
            setcookie('triad_gdpr_consent', 'yes', time() + (86400 * 365));
            return $data;
        }

        if (!isset($_COOKIE['triad_gdpr_consent']) || $_COOKIE['triad_gdpr_consent'] != 'yes') {
            foreach ($_COOKIE as $key => $value) {
                setcookie($key, $value, time() - 3600, '/');
            }
            ee()->extensions->end_script = true;
            return [];
        } else {
            return $data;
        }
    }
    
    public function checkEntryRow($channel_object, $row)
    {
        // only need to do this if no consent has been granted.
        if (isset($_COOKIE['triad_gdpr_consent']) && $_COOKIE['triad_gdpr_consent'] == 'yes') {
            return $row;
        }

        // only fields with 'none' formatting will be html.
        $target_fields = array_keys($row, 'none');

        foreach ($target_fields as $_f) {
            // check html fields only.
            if (strpos($_f, 'field_ft_') !== false) {
                $field = 'field_id_'.str_replace('field_ft_','',$_f);
                $data = $row[$field];

                // check for some buzzwords
                if (strpos($data, '.wistia.') !== false ||
                    stripos($data, '.youtube.') !== false ||
                    stripos($data, '.vimeo.') !== false) {
                    $row[$field] = '<p>You need to "Accept Cookies" before being able to view this content.</p>';
                }
            }
        }

        return $row;
    }

    public function loadSettings()
    {
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
            'consent_message' => ['t', ['rows' => '20'], ''],
            'revoke_message' => ['t', ['rows' => '20'], ''],
            'javascript' => ['t', ['rows' => '20'], ''],
            'style' => ['t', ['rows' => '20'], ''],
            'consent_html' => ['t', ['rows' => '20'], ''],
            'revoke_html' => ['t', ['rows' => '20'], ''],
        ];
        return $out;
    }
}
