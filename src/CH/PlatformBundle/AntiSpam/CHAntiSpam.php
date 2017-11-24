<?php

namespace CH\PlatformBundle\AntiSpam;

class CHAntiSpam {

	public function __construct(\Swift_Mailer $mailer, $mail_transport, $min_length) {
		$this->mailer = $mailer;
		$this->mail_transport = $mail_transport;
		$this->min_length = (int) $min_length;
	}

	public function isSpam($text) {
		return strlen($text) < $this->min_length;
	}

}