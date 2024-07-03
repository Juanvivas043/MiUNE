<?php namespace Une\Email;
/**
* Interfaz de implementacion para servicios
* de mensajeria email
 *
 * @package Une/Email
 * @author Alejandro Tejada
 */

interface Mailer {
  public function config($options);
  public function send($template, $data, $formatter);
}

