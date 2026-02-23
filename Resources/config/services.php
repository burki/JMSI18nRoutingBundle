<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();
    $parameters->set('jms_i18n_routing.router.class', \JMS\I18nRoutingBundle\Router\I18nRouter::class);
    $parameters->set('jms_i18n_routing.locale_resolver.class', \JMS\I18nRoutingBundle\Router\DefaultLocaleResolver::class);
    $parameters->set('jms_i18n_routing.loader.class', \JMS\I18nRoutingBundle\Router\I18nLoader::class);
    $parameters->set('jms_i18n_routing.route_exclusion_strategy.class', \JMS\I18nRoutingBundle\Router\DefaultRouteExclusionStrategy::class);
    $parameters->set('jms_i18n_routing.pattern_generation_strategy.class', \JMS\I18nRoutingBundle\Router\DefaultPatternGenerationStrategy::class);
    $parameters->set('jms_i18n_routing.locale_choosing_listener.class', \JMS\I18nRoutingBundle\EventListener\LocaleChoosingListener::class);
    $parameters->set('jms_i18n_routing.cookie_setting_listener.class', \JMS\I18nRoutingBundle\EventListener\CookieSettingListener::class);
    $parameters->set('jms_i18n_routing.route_translation_extractor.class', \JMS\I18nRoutingBundle\Translation\RouteTranslationExtractor::class);

    $services->set('jms_i18n_routing.locale_resolver.default', '%jms_i18n_routing.locale_resolver.class%')
        ->private()
        ->args(['%jms_i18n_routing.cookie.name%']);

    $services->alias('jms_i18n_routing.locale_resolver', 'jms_i18n_routing.locale_resolver.default')
        ->private();

    $services->set('jms_i18n_routing.router', '%jms_i18n_routing.router.class%')
        ->private()
        ->parent('router.default')
        ->args(['index_0' => service('service_container')])
        ->call('setLocaleResolver', [service('jms_i18n_routing.locale_resolver')])
        ->call('setI18nLoaderId', ['jms_i18n_routing.loader'])
        ->call('setDefaultLocale', ['%jms_i18n_routing.default_locale%'])
        ->call('setRedirectToHost', ['%jms_i18n_routing.redirect_to_host%']);

    $services->set('jms_i18n_routing.locale_choosing_listener', '%jms_i18n_routing.locale_choosing_listener.class%')
        ->private()
        ->args([
            '%jms_i18n_routing.default_locale%',
            '%jms_i18n_routing.locales%',
            service('jms_i18n_routing.locale_resolver'),
        ]);

    $services->set('jms_i18n_routing.cookie_setting_listener', '%jms_i18n_routing.cookie_setting_listener.class%')
        ->private();

    $services->set('jms_i18n_routing.route_exclusion_strategy', '%jms_i18n_routing.route_exclusion_strategy.class%')
        ->private();

    $services->set('jms_i18n_routing.pattern_generation_strategy.default', '%jms_i18n_routing.pattern_generation_strategy.class%')
        ->private()
        ->args([
            '%jms_i18n_routing.strategy%',
            service('translator'),
            '%jms_i18n_routing.locales%',
            '%kernel.cache_dir%',
            '%jms_i18n_routing.catalogue%',
            '%jms_i18n_routing.default_locale%',
        ]);

    $services->alias('jms_i18n_routing.pattern_generation_strategy', 'jms_i18n_routing.pattern_generation_strategy.default')
        ->private();

    $services->set('jms_i18n_routing.loader', '%jms_i18n_routing.loader.class%')
        ->public()
        ->args([
            service('jms_i18n_routing.route_exclusion_strategy'),
            service('jms_i18n_routing.pattern_generation_strategy'),
        ]);

    $services->set('jms_i18n_routing.route_translation_extractor', '%jms_i18n_routing.route_translation_extractor.class%')
        ->private()
        ->args([
            service('router'),
            service('jms_i18n_routing.route_exclusion_strategy'),
        ])
        ->tag('jms_translation.extractor', ['alias' => 'jms_i18n_routing']);
};
