# Como usar?

Renomeio o arquivo .env.example para .env
E faça as configurações no mesmo.

No arquivo docker-composer.yaml escolha uma senha/chave api para EvolutionAPI, no lugar de "EvolutionAPIKey" ou deixe assim mesmo se não se importar.

Na pasta principal rode o comando: 
```shel
docker compose up -d
```
Acesse a página html/zap_login.php para fazer login no WhatsApp.

Agora já pode usar como desejar:
```php
<?php
require_once __DIR__ . "/bootstrap.php";
$Notify = new \App\Notify();
// envia mensagem para um número do WhatsApp
$Notify->sendZap('Sua mensagem para WhatsApp', '5511');

// Envia menagem de e-mail
$Notify->senMail('Assunto de Email', 'Conteúdo do e-mail');
```

Se desejar o script /crons/correios
Importe a base de dados notify.sql