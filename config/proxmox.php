<?php

return [
    'host' => env('PROXMOX_HOST', 'https://localhost:8006'),
    'node' => env('PROXMOX_NODE', 'pve'),
    'token' => env('PROXMOX_TOKEN_NAME', '') . '=' . env('PROXMOX_TOKEN_SECRET', ''),
    'verify_ssl' => env('PROXMOX_VERIFY_SSL', false),
];
