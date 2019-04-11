
RestApi
=====================

The Kunstmaan RestApi Bundle provides a REST api for the popular Kunstmaan CMS bundles

# Enabling the bundles

## Add bundles to Appkernel.php

### Core bundles
```
new Kunstmaan\Rest\CoreBundle\KunstmaanRestCoreBundle(),
new Kunstmaan\Rest\NodeBundle\KunstmaanRestNodeBundle(),
new Kunstmaan\Rest\MediaBundle\KunstmaanRestMediaBundle(),
new Kunstmaan\Rest\ConfigBundle\KunstmaanRestConfigBundle(),
new Kunstmaan\Rest\UserBundle\KunstmaanRestUserBundle(),
new Kunstmaan\Rest\RedirectBundle\KunstmaanRestRedirectBundle(),
new Kunstmaan\Rest\FormBundle\KunstmaanRestFormBundle(),
new Kunstmaan\Rest\MenuBundle\KunstmaanRestMenuBundle(),
new Kunstmaan\Rest\TranslationsBundle\KunstmaanRestTranslationsBundle(),
```

### Required third party bundles
```
new FOS\RestBundle\FOSRestBundle(),
new Nelmio\ApiDocBundle\NelmioApiDocBundle(),
new JMS\SerializerBundle\JMSSerializerBundle(),
new Bazinga\Bundle\HateoasBundle\BazingaHateoasBundle(),
```

## Add to routing.yml

```
# Kunstmaan rest
KunstmaanRestApi:
    resource: "@KunstmaanRestCoreBundle/Resources/config/routing_all.yml"
```

## Add to config.yml

```
# Kunstmaan rest
imports:
    - { resource: '@KunstmaanRestCoreBundle/Resources/config/config_all.yml' }
```

## Contributing

We love contributions!
If you're submitting a pull request, please follow the guidelines in the [Submitting pull requests](docs/pull-requests.md)
