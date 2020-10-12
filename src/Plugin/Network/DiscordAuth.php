<?php

namespace Drupal\social_auth_discord\Plugin\Network;

use Drupal\Core\Url;
use Drupal\social_api\SocialApiException;
use Drupal\social_auth\Plugin\Network\NetworkBase;
use Drupal\social_auth_discord\Settings\DiscordAuthSettings;
use Wohali\OAuth2\Client\Provider\Discord;

/**
 * Defines a Network Plugin for Social Auth Discord.
 *
 * @package Drupal\social_auth_discord\Plugin\Network
 *
 * @Network(
 *   id = "social_auth_discord",
 *   social_network = "Discord",
 *   type = "social_auth",
 *   handlers = {
 *     "settings": {
 *       "class": "\Drupal\social_auth_discord\Settings\DiscordAuthSettings",
 *       "config_id": "social_auth_discord.settings"
 *     }
 *   }
 * )
 */
class DiscordAuth extends NetworkBase implements DiscordAuthInterface {

  /**
   * Sets the underlying SDK library.
   *
   * @return \Wohali\OAuth2\Client\Provider\Discord
   *   The initialized 3rd party library instance.
   *
   * @throws \Drupal\social_api\SocialApiException
   *   If the SDK library does not exist.
   */
  protected function initSdk() {

    $class_name = '\Wohali\OAuth2\Client\Provider\Discord';
    if (!class_exists($class_name)) {
      throw new SocialApiException(sprintf('The Discord Library for the league oAuth not found. Class: %s.', $class_name));
    }

    /** @var \Drupal\social_auth_discord\Settings\DiscordAuthSettings $settings */
    $settings = $this->settings;

    if ($this->validateConfig($settings)) {
      // All these settings are mandatory.
      $league_settings = [
        'clientId' => $settings->getClientId(),
        'clientSecret' => $settings->getClientSecret(),
        'redirectUri' => Url::fromRoute('social_auth_discord.callback')->setAbsolute()->toString(),
      ];

      // Proxy configuration data for outward proxy.
      $httpClientConfig = $this->siteSettings->get('http_client_config');
      if (isset($httpClientConfig['proxy']['http'])) {
        $league_settings['proxy'] = $httpClientConfig['proxy']['http'];
      }

      return new Discord($league_settings);
    }

    return FALSE;
  }

  /**
   * Checks that module is configured.
   *
   * @param \Drupal\social_auth_discord\Settings\DiscordAuthSettings $settings
   *   The Discord auth settings.
   *
   * @return bool
   *   True if module is configured.
   *   False otherwise.
   */
  protected function validateConfig(DiscordAuthSettings $settings) {
    $client_id = $settings->getClientId();
    $client_secret = $settings->getClientSecret();
    if (!$client_id || !$client_secret) {
      $this->loggerFactory
        ->get('social_auth_discord')
        ->error('Define Client ID and Client Secret on module settings.');

      return FALSE;
    }

    return TRUE;
  }

}
