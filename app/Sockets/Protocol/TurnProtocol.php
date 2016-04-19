<?php
/**
 *
 * @author artesby
 */

namespace App\Sockets\Protocol;

use Ratchet\ConnectionInterface;
use App\Sockets\PushServerSocket;

class TurnProtocol extends PushServerSocket implements ProtocolInterface
{
    /**
     * WS server.
     *
     * @var PushServerSocket
     */
    protected $server;
    
    /**
     * Information about turn.
     *
     * @var array
     */
    protected $data;

    /**
     * Create a new TurnProtocol instance.
     *
     * @param array $data
     * @param ConnectionInterface $client
     * @param PushServerSocket $server
     * @return void
     */
    public function __construct(array $data, ConnectionInterface $client, PushServerSocket $server)
    {
        $this->data = $data;
        $this->server = $server;
    }
    
    /**
     *
     * Send information about turn to all clients.
     *
     * @return void
     */
    public function compile()
    {
        echo sprintf('send turn to all clients' . PHP_EOL);
        array_unshift($this->data, ['name' => 'turn']);
        $msg = json_encode($this->data);
        foreach ($this->server->clients as $client) {
            $client->send($msg);
        }
    }
}
