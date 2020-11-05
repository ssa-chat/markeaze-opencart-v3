<?php

/*
MIT License

Copyright (c) Markeaze Inc. https://markeaze.com

This file is part of the markeaze-php-tracker library created by Markeaze.

Repository: https://github.com/markeaze/markeaze-php-tracker
Documentation: https://github.com/markeaze/markeaze-php-tracker/blob/master/README.md
*/

class MkzWebhook {
  public $debug = true;
  private $endpoint = null;
  private $app_key = null;
  private $app_secret = null;
  private $cms = null;

  public function __construct($app_key, $app_secret, $cms) {
    $this->app_key = (string) $app_key;
    $this->app_secret = (string) $app_secret;
    $this->cms = (string) $cms;
    $this->endpoint = strrpos($app_key, '@stage') === false ? 'apps.markeaze.com' : 'apps-stage.markeaze.com';
  }

  public function send($topic, $payload) {
    include_once('mkz_sender.php');
    $sender = new MkzSender("https://{$this->endpoint}/i/cms/{$this->cms}/webhook");
    $data = array(
      'app_key' => (string) $this->app_key,
      'topic' => (string) $topic,
      'payload' => json_encode($payload)
    );
    $signature = $this->get_webhook_signature($data);
    $headers = array(
      "HTTP_X_MARKEAZE_WEBHOOK_SIGNATURE: {$signature}"
    );
    $response = $sender->send($data, 'POST', $headers);

    include_once('mkz_logger.php');
    $logger = new MkzLogger($this->debug);
    $logger->put($data, $response);
  }

  private function get_webhook_signature($data) {
    return base64_encode(hash_hmac('sha256', json_encode($data), $this->app_secret, true));
  }

}