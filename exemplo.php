<?php
require_once __DIR__ . "/bootstrap.php";
$Notify = new \App\Notify();
// envia mensagem para um número do WhatsApp
$Notify->sendZap('Sua mensagem para WhatsApp', '5511');

// Envia menagem de e-mail
$Notify->senMail('Assunto de Email', 'Conteúdo do e-mail');