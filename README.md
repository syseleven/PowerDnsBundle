PowerDnsBundle
==============

[![Build Status](https://travis-ci.org/syseleven/PowerDnsBundle.svg?branch=master)](https://travis-ci.org/syseleven/PowerDnsBundle)

This Bundle provides an ReST API for the PowerDNS server written in PHP and integrated in the Symfony framework. It includes methods for creating and maintaining zone and domain records.

Installation
------------
To install you need to include this bundle in your composer.json:

```javascript
    require: {
         ....
         "syseleven/powerdnsbundle": "dev-master"
    }
```
Then run `php composer.phar update and activate the Bundle in your kernel and adapt your configuration.

```php
    $bundles = array(
        ...
        new SysEleven\PowerDnsBundle\SysElevenPowerDnsBundle(),
    );
````
You also need to register JmsSerializer and the FosRestBundle

```php
    $bundles = array(
        ...
        new FOS\RestBundle\FOSRestBundle(),
        new JMS\SerializerBundle\JMSSerializerBundle($this),
    );
````

The bundle makes some changes to the database structure of PowerDNS but they won't affect the default behaviour.

```sh

php app/console doctrine:schema:update --dump-sql -em=<your_entity_manager>

```

will dump the changes to your current schema, please review them and adapt to your needs.

Configuration
-------------

There is not really much configuration for the bundle so far. You can set the entity manager to use with:

```yaml

sys_eleven_power_dns:
    entity_manager: default

```

Apart from setting the entity manager, you can also specify default values for new SOA records.

```yaml

sys_eleven_power_dns:
    entity_manager: default
    soa_defaults:
            primary: ns.domain.com
            hostmaster: admin@domain.com
            default_ttl: 3600

```

Then adapt your routing to load the routes:

```yaml

syseleven_power_dns:
    resource: "@SysElevenPowerDnsBundle/Resources/config/routing.yml"
    prefix: /

```

Third Party Bundles
-------------------

The bundle uses the NelmioApiDocBundle to expose the documentation of the route parameters through its interface if you want to use this feature you have to activate and configure the Bundle. Please refer to the bundles homepage for more information.
Integration with the FosRestBundle, the bundle uses the View components of the FosRestBundle, if you have the FosRestBundle in use please check your configuration.



API Documentation
-----------------

[You can find a documentation of the resources and its parameter here.](Resources/doc/api.md)

