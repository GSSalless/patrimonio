<?php
/**
 * Configuração geral da aplicação (estilo Laravel).
 * Valores sensíveis (banco) continuam no .env; aqui ficam ajustes gerais.
 */
return [
    'name'     => 'Gestão Patrimonial',
    // URL base da aplicação. Vem do .env (BASE_URL) para funcionar em qualquer
    // ambiente: local = /cezar/ ; produção = / (raiz do domínio) ou /cesar/.
    'base_url' => Env::get('BASE_URL', '/cezar/'),
    'env'      => Env::get('APP_ENV', 'local'),
];
