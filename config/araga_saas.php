<?php

return [
    // E-mails que podem acessar o painel /saas (plataforma).
    // Dica: use .env para manter isso fora do código-fonte.
    'platform_owner_emails' => array_filter(array_map('trim', explode(',', env('ARAGA_SAAS_OWNER_EMAILS', '')))),
];
