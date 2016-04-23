<?php
/*
 *
 * @author Ananaskelly
 */

namespace App\Sockets\Protocol;

use App\Sockets\PushServerSocket;
use Ratchet\ConnectionInterface;
use App\Token;

class AuthenticationProtocol implements ProtocolInterface
{
    /**
     *
     * @var array
     */
    protected $data;
    
    /**
     *
     * @var ConnectionInterface
     */
    protected $client;
    
    /**
     *
     * @var PushServerSocket
     */
    protected $server;
    
    /**
     * 
     * @param array $data
     * @param ConnectionInterface $client
     * @param PushServerSocket $server
     * @return void
     */
    public function __construct(array $data, ConnectionInterface $client, PushServerSocket $server) {
        $this->data = $data;
        $this->client = $client;
        $this->server = $server;
    }
    public function compile() {
        $str = $this->data['token'];
        $token = Token::find($str);
        $response = [
                'name' => 'authentication',
                'data' => [
                    'type' => 'response'
                ]
            ];
        if ($token === null){
            $response['data']['result'] = 'failed';
        } else {
            $this->server->setUser($this->client->resourceId, $token->user_id);
            $response['data']['result'] = 'success';
        }
        $msg = json_encode($response);
        $this->client->send($msg);
    }
}

