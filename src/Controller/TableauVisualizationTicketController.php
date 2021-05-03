<?php

namespace Drupal\tableau_vis\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Check ticket controller
 *
 * Class TableauVisualizationTicketController
 * @package Drupal\tableau_vis\Controller
 */
class TableauVisualizationTicketController extends ControllerBase {

  /**
   * @var ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * TableauVisualizationTicketController constructor.
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
   * @return bool|string
   */
  public function tableauVisualizationGetTrustedTicket() {
    $config = $this->config('tableau_visualization.settings');
    // Get tableau server from tableau_viz_settings_form
    $server = !empty($config->get('tableau_server')) ? $config->get('tableau_server') : '';
    // Get tableau user from tableau_viz_settings_form
    $username = !empty($config->get('tableau_user')) ? $config->get('tableau_user') : '';
    // Get target site name from tableau_viz_settings_form
    $target_site = !empty($config->get('tableau_target_site')) ? $config->get('tableau_target_site') : '';
    // Data to send to Tableau server
    $data = array('username' => $username, 'target_site' => $target_site);
    // Initialize cURL session
    $ch = curl_init($server);

    // Use POST method
    curl_setopt($ch, CURLOPT_POST, TRUE);
    // Post data
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    // Return ticket variable
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

    // Execute cURL and return ticket
    $response = (string) curl_exec($ch);
    // Close cURL session
    curl_close($ch);

    return new Response($response);
  }

}