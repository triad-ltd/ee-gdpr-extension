# About
Produced by [https://triad.uk.com/](https://triad.uk.com/)
This extension will implement a GDPR compliant cookie policy, preventing EE from placing any cookies on the end user's computer
until consent has been granted.

# License
GNU General Public License v3.0, see the LICENSE file for full information.

# Support
Built and tested on EE 4.0.8. Please raise any issues via github
[https://github.com/triad-ltd/ee-gdpr-extension](https://github.com/triad-ltd/ee-gdpr-extension)

# Installation
- Copy all files into a folder named "triad_gdpr" in your user/addons folder.
- In the Expressionengine Add-On Manager locate 'Triad GDPR Extension' and click on 'Install'.
- You will need to 'Allow Cookies' via your site homepage to regain access to the control panel, as it requires cookies to work.

# Usage

Place the following tag inside the &lt;head&gt; of your site template:
```
{exp:triad_gdpr:script}
```

Keep in mind that without consent, any forms using POST will fail as CSRF cannot function, unless the 'essiential' setting is turned on.
To that end the following tag is available in order that you can show/hide content based on the consent option of the visitor:

```
{if "{exp:triad_gdpr:consentEssential}" == "yes"}
WE HAVE CONSENT
{if:else}
WE DON'T HAVE CONSENT
{/if}
```
