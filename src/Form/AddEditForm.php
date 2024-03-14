<?php
namespace Drupal\daily_password_fetch\Form;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\daily_password_fetch\ConfigData;



class AddEditForm implements FormInterface, ContainerInjectionInterface  {

  use StringTranslationTrait;
  use MessengerTrait;

  private ConfigData $configData;

  public static function create(ContainerInterface $container): AddEditForm|static
  {
    return new static(
      $container->get('daily_password_fetch.config_data')
    );
  }
  public function __construct( ConfigData $configData) {
    $this->configData = $configData;
  }


  /**
   * @return string
   */
  public function getFormId(): string
  {
    return 'add_edit_form';
  }

  /**
   * Builds the form.
   *
   * @param array $form
   *   The form array.
   * @param \FormStateInterface $form_state
   *   The form state object.
   *
   * @return array
   *   The built form array.
   */
  public function buildForm(array $form, FormStateInterface $form_state): array
  {
    $form['url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Url'),
      '#description' => $this->t('Enter the url to fetch from'),
      '#required' => TRUE,
      '#default_value' => $this->configData->getURL(),
    ];
    $form['password_property'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Property'),
      '#description' => $this->t('Enter the json property to retrieve the password from'),
      '#required' => TRUE,
      '#default_value' => $this->configData->getPasswordProperty(),
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save settings'),
    ];
    return $form;
  }


  /**
   *  Url validation function
   * @param $url
   *
   * @return bool
   */
  private function isValidUrl($url): bool {
    $pattern = $pattern = "/^(http|https):\/\/([a-z0-9+_-]+\.)*[a-z0-9+_-]+(\.[a-z]+)?(:[0-9]{1,5})?(\/.*)?$/i";
    if (preg_match($pattern, $url)) {
      // The string is a valid URL
      return true;
    } else {
      // The string is not a valid URL
      return false;
    }
  }

  /**
   * @param array $form
   * @param \FormStateInterface $form_state
   *
   * @return void
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void
  {
    $url = $form_state->getValue('url');
    $password_property = $form_state->getValue('password_property');

    // Validate the URL
    if(empty($url) || !$this->isValidUrl($url)) {
      $form_state->setErrorByName('url', $this->t('Valid Url is required'));
    }
    // Validate the password property
    if(empty($password_property)) {
      $form_state->setErrorByName('password_property', $this->t('Property is required'));
    }
  }


  /**
   * Submit the form and save the settings.
   *
   * @param array &$form
   *   The form that was submitted.
   * @param \FormStateInterface $form_state
   *   The form state object representing the submitted form.
   *
   * @return void
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void
  {

    $this->configData->setURL($form_state->getValue('url'));
    $this->configData->setPasswordProperty($form_state->getValue('password_property'));
    $this->messenger()->addMessage($this->t('The settings have been saved.'));

  }

}