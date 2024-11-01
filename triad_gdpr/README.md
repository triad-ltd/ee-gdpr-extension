

# Triad GDPR Extension for ExpressionEngine
Produced by [https://triad.uk.com/](https://triad.uk.com/)
This extension will implement a GDPR/PECR compliant cookie policy, preventing EE from placing any cookies on the end user's computer until consent has been granted. Research the 'Planet 49' case in which the CJEU ruled that consent is required to place **any** cookies on the end-user's device.

>It does not matter whether the cookies constitute personal data or not - Article 5(3) of the e-Privacy Directive (i.e. the cookie consent rule) applies to any information installed or accessed from an individual's device.

## License
GNU General Public License v3.0, see the LICENSE file for full information.

## Support
Built and tested on EE 7.2.17 Please raise any issues via github
[https://github.com/triad-ltd/ee-gdpr-extension](https://github.com/triad-ltd/ee-gdpr-extension)

## Installation
- Copy all files into a folder named "triad_gdpr" in your user/addons folder.
- In the Expressionengine Add-On Manager locate 'Triad GDPR Extension' and click on 'Install'.
- You will need to 'Allow Cookies' via your site homepage to regain access to the control panel, as it requires cookies to work.

## Usage
Place the following tag inside the &lt;head&gt; of your site template:
`{exp:triad_gdpr:script}`


### Server-side implementation
Without consent, any forms using POST will fail as CSRF cannot function. You should also prevent third-party content such as video embeds from loading as they often place cookies immediately without requesting consent.
The following conditional is available in order that you can show/hide content based on the consent option of the visitor:
```
{if "{exp:triad_gdpr:consent}" == "yes"}
WE HAVE CONSENT
{if:else}
WE DON'T HAVE CONSENT
{/if}
```

A setting has been included to allow 'essential' (none of them truly are) [ExpressionEngine cookies](https://docs.expressionengine.com/latest/general/cookies.html#cookies). With this enabled POST forms will function. A further conditional is available to show/hide content based on this control panel setting.
```
{if "{exp:triad_gdpr:consent_essential}" == "yes"}
ESSENTIAL TOGGLE IS ON
{if:else}
ESSENTIAL TOGGLE IS OFF
{/if}
```

A further use case is that you may have decided to place cookies without consent, and your end user may wish to dismiss the notification which is displayed. Include a button in your notification with the id `triad_gdpr_dismiss_btn` to trigger this feature. This conditional allows you to show/hide content based on the dismissal status.
```
{if "{exp:triad_gdpr:notification_dismissed}" == "yes"}
ESSENTIAL TOGGLE IS ON
{if:else}
ESSENTIAL TOGGLE IS OFF
{/if}
```

### Client-side implementation
Javascript based, very useful for example when HTML is cached and static, but you still want to react to the consent status.

Elements with the class `gdpr-consent-required` will be removed from the DOM if consent **is not** present.

Elements with the class `gdpr-consent-message` will be removed from the DOM if consent **is** present.

A global javascript variable `gdpr_consent` is also available. Its value will be either `true` or `false`.

## Consent Mode Version When GTM tag ID provided

### Server-side implementation
As Above, Without consent, any forms using POST will fail as CSRF cannot function.
The following conditional is available also:
```
{if "{exp:triad_gdpr:consent}" == "yes"}
WE HAVE CONSENT - WORKS THE SAME
{if:else}
WE DON'T HAVE CONSENT AND BANNER NOW APPEARS AT THE BOTTOM TO ACCEPT AFTER DIMISSED
    - Include buttons here if required to make it easier to accept cookies as the banner only appears once scrolled down to the bottom.
    E.g. <p>Please accept cookies for the form to work. <a href="Javascript:;" id="triad_gdpr_consent_btn">Accept Cookies.</a></p>
{/if}
```

SHOULD ONLY NEED THIS CONDITIONAL FOR MAJORITY OF USE UNTIL FUTHER VERSIONS.

### Client-side implementation
Javascript based, very useful for example when HTML is cached and static, but you still want to react to the consent status.

Consent Mode is stored within LocalStorage inline with official documentation from Google - https://developers.google.com/tag-platform/security/guides/consent?consentmode=basic

Banner will automatically check for whether user has accepted / dismissed / revoked the consent and consentMode is updated on click and pushed to dataLayer.


## Changelog
0.4.0 - 2024-10-29
 - Update syntax to suppress deprecation errors

0.3.0 - 2024-07-22
 - New field 'GTM id (Gtag ID)' - When provided, will include code for Consent Mode compatibility, if not provided, fallback to existing GDPR code using cookies.

0.2.7 - 2023-02-06
 - 'essential' toggle wasn't working, EE source code did not match the docs expectation! plus javascript issues with this feature

0.2.6 - 2022-02-16
 - fixed setting and deleting cookies on urls ending with port numbers

0.2.5 - 2021-12-06
 - default consent message changed to use client-side implementation

0.2.4 - 2021-11-11
 - Corrected settings load path

0.2.3 - 2021-11-10
 - Swap use of remove() and arrow functions in javascript to IE compatible versions

0.2.2 - 2021-09-29
 - CP cookie didn't have the path set so some CP requests would delete the session after login

0.2.1 - 2021-06-29
 - frontend javascript to delete cookies when consent is revoked

0.2 - 2021-06-17
 - to avoid 'form expired' errors, hitting the control panel sets the consent cookie, we presume the site owner grants consent
 - typo in language file
 - client-side conditional content using JavaScript
