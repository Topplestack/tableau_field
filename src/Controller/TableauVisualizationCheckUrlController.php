<?php

namespace Drupal\tableau_vis\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Check url controller
 *
 * Class TableauVisualizationCheckUrlController
 * @package Drupal\tableau_vis\Controller
 */
class TableauVisualizationCheckUrlController extends ControllerBase {

  /**
   * @var ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * TableauVisualizationCheckUrlController constructor.
   * @param ConfigFactoryInterface $config_factory
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory')
    );
  }

  /**
   * @return bool|Response
   */
  public function tableauVisualizationCheckUrl() {
    $config = $this->config('tableau_visualization.settings');
    // Get tableau server from tableau_viz_settings_form
    $server = !empty($config->get('tableau_server')) ? $config->get('tableau_server') : '';
    $parseUrl = parse_url(trim($server));
    $host = trim($parseUrl['host'] ? $parseUrl['host'] : array_shift(explode('/', $parseUrl['path'], 2)));
    if ($server == NULL) {
      return FALSE;
    }
    $ch = curl_init('https://'.$host.'/');
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $data = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if($httpcode >= 200 && $httpcode < 300){
      $response = 'true';
    }
    else {
      $response = 'false';
    }

    return new Response($response);
  }

}