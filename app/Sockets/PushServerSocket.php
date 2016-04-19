<?php
/**
 *
 * @author malinink
 */
namespace App\Sockets;

use ZMQ;
use ZMQContext;
use Exception;
use SplObjectStorage;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use App\Sockets\Protocol\ProtocolInterface;
use App\Sockets\Protocol\TokenProtocol;
use App\Sockets\Protocol\AuthentificationProtocol;
use App\Sockets\Protocol\SubscribeProtocol;
use App\Sockets\Protocol\SynchronizeProtocol;
use App\Sockets\Protocol\TurnProtocol;

class PushServerSocket implements MessageComponentInterface
{
    protected $clients;
    
    public function __construct()
    {
        $this->clients = new SplObjectStorage();
    }
    
    public static function setDataToServer($data)
    {
        $context = new ZMQContext();
        $socket = $context->getSocket(ZMQ::SOCKET_PUSH);
        $socket->connect('tcp://127.0.0.1:5555');
        $socket->send(json_encode($data));
    }
    
    public function __invoke($data)
    {
        $data = json_decode($data, true);
        echo sprintf('invoked with data' . PHP_EOL);
        foreach ($this->clients as $client) {
            $client->send($data);
        }
    }
    
    public function onOpen(ConnectionInterface $conn)
    {
        echo sprintf('client %s connected' . PHP_EOL, $conn->resourceId);
        $this->clients->attach($conn);
    }

    public function onClose(ConnectionInterface $conn)
    {
        echo sprintf('client %s disconnected' . PHP_EOL, $conn->resourceId);
        $this->clients->detach($conn);
    }

    public function onError(ConnectionInterface $conn, Exception $e)
    {
        echo sprintf('client %s error message: %' . PHP_EOL, $e->getMessage());
        $this->clients->detach($conn);
        $conn->close();
    }
    
    public function onMessage($data, $client)
    {
        try {
            $data_js = json_decode($data, true);
            if ($data_js === null) {
                $err = "It is not json!";
                throw new Exception($err);
            }
            $name = $data_js['name'];
            switch ($name) {
                case "token":
                    if (class_exists('TokenProtocol')) {
                        $obj = new TokenProtocol($data, $client, $this);
                    }
                    break;
                case "authentification":
                    if (class_exists('AuthentificationProtocol')) {
                        $obj = new AuthentificationProtocol($data, $client, $this);
                    }
                    break;
                case "subscribe":
                    if (class_exists('SubscribeProtocol')) {
                        $obj = new SubscribeProtocol($data, $client, $this);
                    }
                    break;
                case "synchronize":
                    if (class_exists('SynchronizeProtocol')) {
                        $obj = new SynchronizeProtocol($data, $client, $this);
                    }
                    break;
                case "turn":
                    if (class_exists('TurnProtocol')) {
                        $obj = new TurnProtocol($data, $client, $this);
                    }
                    break;
                default:
                    $err = "Wrong name!";
                    throw new Exception($err);
            }
            if (!($obj instanceof ProtocolInterface)) {
                $err = "Wrong Interface!";
                throw new Exception($err);
            }
            if (!(method_exists($obj, 'compile'))) {
                $err = "There isnt such method!";
                throw new Exception($err);
            }
            $obj->compile();
        } catch (Exception $e) {
            echo sprintf('something wrong!', $e->getMessage());
        }
    }
}
