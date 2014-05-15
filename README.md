PowerDnsBundle
==============

This Bundle provides an ReST API for the PowerDNS server written in PHP and integrated in the Symfony framework. It includes methods for creating and maintaining zone and domain records.

Installation
------------
To install you need to include this bundle in your composer.json:

```javascript
    require: {
         ....
         syseleven/powerdnsbundle: "1.*"
    }
```
Then run `php composer.phar update and activate the Bundle in your kernel and adapt your configuration.

```php
    $bundles = array(
        new SysElevenPowerDnsBundle();
    );
````

Configuration
-------------

There is not really much configuration for the bundle so far, the only you can do is to specify the entity manager you want to use for your PowerDNS installation.

```yaml

sys_eleven_power_dns:
    entity_manager: default

```

Then adapt your routing to load the routes:

```yaml

syseleven_power_dns:
    resource: "@SysElevenPowerDnsBundle/Resources/config/routing.yml"
    prefix: /

```

The bundle makes some changes to the database structure of PowerDNS but they won't affect the default behaviour.

```sh

php app/console doctrine:schema:update --dump-sql -em=<your_entity_manager>

```

will dump the changes to your current schema, please review them and adapt to your needs.


Third Party Bundles
-------------------

The integrates bundle with the NelmioApiDocBundle and exposes the documentation of the route parameters through its interface if you want to use this feature you have to activate and configure the Bundle. Please refer to the bundles homepage for more information.

Integration with the FosRestBundle, the bundle uses the View components of the FosRestBundle, if you have the FosRestBundle in use please check your configuration.



API Documentation
-----------------

You can find a documentation of the resources and its parameter here.

