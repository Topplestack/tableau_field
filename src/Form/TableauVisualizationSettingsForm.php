<?php

/**
 * Tableau Visualization Configuration form
 *
 * @file
 */

namespace Drupal\tableau_vis\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TableauVisualizationSettingsForm extends ConfigFormBase {

  /**
   * @inheritDoc
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    parent::__construct($config_factory);
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
   * @inheritDoc
   */
  public function getFormId() {
    return 'tableau_visualization_settings_form';
  }

  /**
   * @inheritDoc
   */
  public function getEditableConfigNames() {
    return [
      'tableau_visualization.settings',
    ];
  }

  /**
   * @inheritDoc
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('tableau_visualization.settings');

    $form['tableau_vis_text'] = array(
      '#type' => 'markup',
      '#markup' => '<p>' . $this->t('The following settings will be used as
      values on all Tableau Visualization fields.') . '</p>',
    );

    $form['tableau_js_api'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Tableau Javascript API URL'),
      '#default_value' => !empty($config->get('tableau_js_api')) ? $config->get('tableau_js_api') : NULL,
    );

    $form['tableau_server'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Tableau Trusted Server URL'),
      '#description' => '<b>' . $this->t('Example:') . ' </b>' . $this->t('//tableau.example.org/trusted/'),
      '#default_value' => !empty($config->get('tableau_server')) ? $config->get('tableau_server') : NULL,
    );

    $form['tableau_user'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Tableau User'),
      '#description' => $this->t('Enter the Tableau username'),
      '#default_value' => !empty($config->get('tableau_user')) ? $config->get('tableau_user') : NULL,
    );

    $form['tableau_target_site'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Target Site Name'),
      '#description' => $this->t('Enter the target site name for this site'),
      '#default_value' => !empty($config->get('tableau_target_site')) ? $config->get('tableau_target_site') : NULL,
    );

    $form['tableau_honeypot'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Tableau Honeypot'),
      '#default_value' => ($config->get('tableau_honeypot') !== NULL) ? $config->get('tableau_honeypot') : 1,
    );

    $form['tableau_bot_blocker'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Prevent bots from seeing tableau'),
      '#default_value' => ($config->get('tableau_bot_blocker') !== NULL) ? $config->get('tableau_bot_blocker') : 1,
    );

    $form['tableau_timeout_bool'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Tableau Idle Refresh'),
      '#default_value' => ($config->get('tableau_timeout_bool') !== NULL) ? $config->get('tableau_timeout_bool') : 1,
    );

    $form['tableau_break_bool'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Tableau Breakpoint Refresh'),
      '#default_value' => ($config->get('tableau_break_bool') !== NULL) ? $config->get('tableau_break_bool') : 1,
    );

    $form['tableau_back_bool'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Tableau Back Button Refresh'),
      '#default_value' => ($config->get('tableau_back_bool') !== NULL) ? $config->get('tableau_back_bool') : 1,
    );

    $form['tableau_timeout'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Tableau Idle Timeout'),
      '#description' => $this->t('Set this in seconds to prevent users from seeing tableau server login screen. Example: 180 = 3 minutes'),
      '#default_value' => !empty($config->get('tableau_timeout')) ? $config->get('tableau_timeout') : NULL,
    );

    $form['tableau_breakpoint'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Mobile Breakpoint'),
      '#description' => $this->t('Pixel breakpoint to switch between phone and desktop modes. Example: 1024'),
      '#default_value' => !empty($config->get('tableau_breakpoint')) ? $config->get('tableau_breakpoint') : NULL,
    );

    $form['tableau_placeholder'] = array(
      '#type' => 'managed_file',
      '#title' => $this->t('Tableau Placeholder Image'),
      '#description' => $this->t('JPG image used as a placeholder when the tableau server is unavaialble'),
      '#default_value' => !empty($config->get('tableau_placeholder')) ? $config->get('tableau_placeholder') : '',
      '#upload_location' => 'public://tableau-placeholder',
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * @inheritDoc
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    // Set the submitted configuration settings.
    if (!empty($values)) {
      $this->configFactory->getEditable('tableau_visualization.settings')
        ->set('tableau_js_api', $values['tableau_js_api'])
        ->set('tableau_server', $values['tableau_server'])
        ->set('tableau_user', $values['tableau_user'])
        ->set('tableau_target_site', $values['tableau_target_site'])
        ->set('tableau_honeypot', $values['tableau_honeypot'])
        ->set('tableau_bot_blocker', $values['tableau_bot_blocker'])
        ->set('tableau_timeout_bool', $values['tableau_timeout_bool'])
        ->set('tableau_break_bool', $values['tableau_break_bool'])
        ->set('tableau_back_bool', $values['tableau_back_bool'])
        ->set('tableau_timeout', $values['tableau_timeout'])
        ->set('tableau_breakpoint', $values['tableau_breakpoint'])
        ->set('tableau_placeholder', $values['tableau_placeholder'])
        ->save();

      if (!empty($values['tableau_placeholder'][0])) {
        try {
          $file = File::load($values['tableau_placeholder'][0]);
          if (!empty($file)) {
            $file->setPermanent();
            $file->save();
            $file_usage = \Drupal::service('file.usage');
            $file_usage->add($file, 'tableau_viz', 'tableau_placeholder_id', $file->id());
          }
        }
        catch (\Exception $exception) {
          watchdog_exception('tableau_vis', $exception);
        }
      }
    }

    parent::submitForm($form, $form_state);
  }

}