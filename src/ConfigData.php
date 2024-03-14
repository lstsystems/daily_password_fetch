<?php

namespace Drupal\daily_password_fetch;

use Drupal\Core\Config\ConfigFactoryInterface;

class ConfigData {

  /**
   * Config settings.
   *
   * @var ConfigFactoryInterface
   */
  protected ConfigFactoryInterface $settings;


  /**
   * Get all config data
   * ConfigData constructor.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->settings = $config_factory;
  }


  public function getSettings() {
    return $this->settings;
  }


  public function getURL() {
    return $this->settings->getEditable('daily_password_fetch.settings')->get('url');
  }

  public function getPasswordProperty() {
    return $this->settings->getEditable('daily_password_fetch.settings')->get('password_property');
  }

  public function setURL($value) {
    $this->settings->getEditable('daily_password_fetch.settings')->set('url', $value)
      ->save();
  }

  public function setPasswordProperty($value) {
    $this->settings->getEditable('daily_password_fetch.settings')->set('password_property', $value)
      ->save();
  }

}