<?php
namespace Drupal\daily_password_fetch\Plugin\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\daily_password_fetch\ConfigData;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Provides a 'DailyPasswordFetchBlock' Block.
 *
 * @Block(
 *   id = "block_daily_password_fetch",
 *   admin_label = @Translation("Daily Password Fetch Block"),
 *   category = @Translation("Custom"),
 * )
 */
class DailyPasswordFetchBlock extends BlockBase  implements ContainerFactoryPluginInterface {

  private LoggerChannelFactoryInterface $logger;
  private ConfigData $configData;

  /**
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
   * @param \Drupal\daily_password_fetch\ConfigData $configData
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LoggerChannelFactoryInterface $logger, ConfigData $configData) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configData = $configData;
    $this->logger = $logger;
  }

  /**
   *  Set the cache max age to 60 seconds.
   * @return int
   */
  public function getCacheMaxAge() {
    return 60;
  }


  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   *
   * @return static
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('logger.factory'),
      $container->get('daily_password_fetch.config_data')
    );
  }

  /**
   *  Fetch the data from the remote server.
   *  Using the URL and password property from the configuration.
   *
   * @return null|string
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  private function fetchData(): ?string
  {
    try {
      $url = $this->configData->getURL();
      $password_property = $this->configData->getPasswordProperty();
      $response = \Drupal::httpClient()->get($url);

      // Convert the response body to a string
      $body = (string) $response->getBody();

      // Remove any non-printable characters, including BOM
      $cleaned_body = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $body);

      // Decode the JSON response
      $data = json_decode($cleaned_body);

      // Check for JSON decode errors
      if (json_last_error() !== JSON_ERROR_NONE) {
        $this->logger->get('daily_password_fetch')->error('JSON Decode Error: ' . json_last_error_msg());
        return NULL;
      }

      // Access the dynamic property using curly braces
      return $data->{$password_property};

    } catch (\Exception $e) {
      $this->logger->get('daily_password_fetch')->error($e->getMessage());
      return NULL;
    }
  }

  /**
   * Build the output for the current request.
   *
   * The build method retrieves the password by calling the FetchData method
   * and sets it as the value for the '#markup' element in the render array.
   *
   * @return array
   *   A render array containing an '#markup' element with the fetched
   *   password.
   */
  public function build() {
    // Fetch the password.
    $password = $this->fetchData();

    // If no password is found, set a default message.
    if (!$password) {
      $password = "Unable to fetch password";

    }

    return [
      '#markup' =>  $password,
    ];
  }
}