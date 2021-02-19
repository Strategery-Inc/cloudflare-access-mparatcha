# CloudFlare Access

Create a M2 module that will authorize CloudFlare Access requests. The documentation for CloudFlare Access request validation is linked further down below.

The module should:

1. Intercept all HTTP requests to Magento frontend controllers.
2. If the request doesn't have a `CF_Authorization` cookie, return a 403 response with a message reading `missing required cf authorization token`.
1. If the cookie is present, then allow the request to pass through.
2. A store configuration setting should determine if the module is Enabled / Disabled. The default value should be `false`.

## Documentation Links
* [CloudFlare Access Request Validation](https://developers.cloudflare.com/cloudflare-one/identity/users/validating-json#python-example)
* [Routing](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/routing.html)
* [Plugins](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/plugins.html)
* [Observers](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/events-and-observers.html) and [M2 Event Cheatsheet](https://www.mageplaza.com/magento-2-module-development/magento-2-events.html)

## Guidelines & Tips

1. Place all your code inside the `src` directory.
2. You can install Magento inside the `magento` folder.
   1. `composer install`
   2. Then run `bin/magento install ...`
   3. You can symlink your module into a folder inside `app/code` in order to test it. 
   4. Don't forget to add it to the modules list in `app/etc/config.php` and run `bin/magento setup:upgrade`.
3. If you already know how to, you can use unit tests to check and iterate over your work more quickly without the need of testing manually inside Magento. Place the tests inside the `tests` folder. WRITING UNIT TESTS IS NOT A REQUIREMENT unless explicitly mentioned in the assignment.