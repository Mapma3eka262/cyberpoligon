<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class ProxmoxService
{
    private $client;
    private $host;
    private $token;
    private $verifySsl;

    public function __construct()
    {
        $this->host = config('proxmox.host');
        $this->token = config('proxmox.token');
        $this->verifySsl = config('proxmox.verify_ssl', false);

        $this->client = new Client([
            'base_uri' => $this->host . '/api2/json/',
            'verify' => $this->verifySsl,
            'timeout' => 30,
        ]);
    }

    public function getVncProxy($node, $vmid)
    {
        try {
            $response = $this->client->post("nodes/{$node}/qemu/{$vmid}/vncproxy", [
                'headers' => [
                    'Authorization' => 'PVEAPIToken=' . $this->token,
                    'Accept' => 'application/json',
                ],
                'form_params' => [
                    'websocket' => 1,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            
            return [
                'success' => true,
                'ticket' => $data['data']['ticket'],
                'port' => $data['data']['port'],
            ];
            
        } catch (RequestException $e) {
            return [
                'success' => false,
                'error' => 'Ошибка подключения к Proxmox: ' . $e->getMessage(),
            ];
        }
    }
}
