<?php

namespace Drupal\drupal_laravel_cache\Controller;

use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;

/**
 * Lists the default includes per JSON:API resource, so the API gateway knows
 * which include paths a frontend may ask for.
 */
class GatewayIncludesController extends ControllerBase
{
    public function list(): CacheableJsonResponse
    {
        $includes = [];
        $cacheability = (new CacheableMetadata())->addCacheTags(['config:jsonapi_resource_config_list']);

        if ($this->entityTypeManager()->hasDefinition('jsonapi_resource_config')) {
            $configs = $this->entityTypeManager()->getStorage('jsonapi_resource_config')->loadMultiple();

            foreach ($configs as $config) {
                if ($config->get('disabled')) {
                    continue;
                }

                $path = $config->get('path') ?: str_replace('--', '/', $config->id());
                $includes[$path] = array_values($config->getThirdPartySetting('jsonapi_defaults', 'default_include', []));
            }
        }

        ksort($includes);

        $response = new CacheableJsonResponse($includes);
        $response->addCacheableDependency($cacheability);

        return $response;
    }
}
