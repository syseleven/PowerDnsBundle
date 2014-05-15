PowerDnsBundle
==============

This Bundle provides an ReST API for the PowerDNS server written in PHP and integrated in the Symfony framework. It includes methods for creating and maintaining zone and domain records.

Installation
------------
To install you need to include this bundle in your composer.json:

```json
    require: {
         ....
         syseleven/powerdnsbundle: "1.*"
    }
```
Then run php composer.phar update and activate the Bundle in your kernel and adapt your configuration.

```php
    $bundles = array(
        new SysElevenPowerDnsBundle();
    );
```
