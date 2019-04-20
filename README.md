# WHMCS Name Server Tracker #

## Summary ##

This module automatically pulls all name servers across all hosting services into
a comprehensive report for easy reference and tracking purposes. It further allows
you to manually add name servers that do not already exist in the system and will
not be associated with 'sold' services in WHMCS.

Future expansion will include integrating with the appropriate registrar API to 
automatically handle the name server registration process.

## Minimum Requirements ##

This module was built to work with WHMCS 7.7.1 on PHP 7.2+ and may not work with
earlier versions of either WHMCS or PHP. We will not supply support for versions
less than these.

## Installation Instructions ##

1. Upload the 'name_server_tracker' folder to your modules/addons folder in WHMCS.
2. In the WHMCS admin, visit Setup > Addon Modules and Activate the module
3. Click the Configure button beside the module and add your name server domain. You can leave this blank if you don't wish to have the list of name servers only show *your* name servers (client name servers will show)
4. Ensure you check the appropriate Access Control boxes, including at least your Administrator role.
5. Visit Addons > Name Server Tracker to see your list of name servers. You may add new custom entries at the bottom of the list.

## Tests ##

None yet.
