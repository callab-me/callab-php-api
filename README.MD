# Callab-API

This is the PHP-API for https://callab.me. You can get your API credentials from the settings menu.

##  Usage

```
<?php

require __DIR__ . '/src/CallabClient.php';

$api_key = "68481fb0-a98e-11ea-b627-8b2a92262801";
$secret_key = "8f0a9c7228e7893ef0f7d9706dce499c34501714";
$base_url = "https://app.callab.me";

// 1. Create instance of Client with credentials
$client = new CallabClient($base_url, $api_key, $secret_key);

// 2. Schedule meeting
$response = $client->scheduleMeeting(
    '30.04.2021 13:30:00',
    '30.04.2021 13:55:00',
    'My meeting with somebody',
    'Europe/Berlin'
);

var_dump($response);
```