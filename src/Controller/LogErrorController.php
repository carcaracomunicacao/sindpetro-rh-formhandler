<?php

$data = json_decode(file_get_contents('php://input'), true);

$logFile = __DIR__ . '/../Service/submit_attempts.log';
$ip      = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$ts      = date('Y-m-d H:i:s');

$line = "[{$ts}] status=FETCH_ERROR | form_id=" . ($data['form_id'] ?? '?')
    . " | ip={$ip}"
    . " | mensagem=" . ($data['mensagem'] ?? '?')
    . " | url=" . ($data['url'] ?? '?')
    . " | campos=" . json_encode($data['campos'] ?? [], JSON_UNESCAPED_UNICODE)
    . PHP_EOL;

file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);

http_response_code(204); // No content — sem resposta necessária