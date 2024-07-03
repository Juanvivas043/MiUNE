<?php namespace Une\Email\Agents;

use Une\Email\Mailer;
use Une\Email\MessageFormatter;
use Swift_Mailer;
use Swift_SendmailTransport;
use Swift_SmtpTransport;
use Swift_LoadBalancedTransport;
use Swift_Message;

/**
 * Implementacion de swiftmailer
 * en MiUNE
 */

class UneSwiftMailer implements Mailer {

  private $options = array(
    "smtp" => array (
      "host" => "smtp.gmail.com",
      "port" => 587,
      "username" => "ddti.une@gmail.com",
      "password" => "35504352"
    ),
    "sendmail" => array (
      "buf" => '/usr/sbin/sendmail -bs'
    )
  );

  public function __construct($options = null) {

    // Permitimos la sobrescribir las opciones
    $this->config($options);
    $this->setTransport();
  }

  public function config($options) {
    if (is_array($options)) {
      $this->options = array_unique(
        array_replace_recursive(
          $this->options,
          $options
        )
      );
    }
  }

  private function setTransport() {

    $transport = new Swift_LoadBalancedTransport();

    $transport->setTransports(
        array(
          Swift_SendmailTransport::newInstance(
            $this->options["sendmail"]["buf"]
          ),
          Swift_SmtpTransport::newInstance(
            $this->options["smtp"]["host"],
            $this->options["smtp"]["port"],
            'tls'
          )
          ->setUsername($this->options["smtp"]["username"])
          ->setPassword($this->options["smtp"]["password"])
        )
    );

    $this->mailer =  Swift_Mailer::newInstance(
      $transport
    );
  }

  public function send($template, $data, $formatter) {
      $message = Swift_Message::newInstance();
      $formatMessage = $formatter($message);
      $this->mailer->send($formatMessage);
  }

}
