<?php

namespace Mocktrine;

use Mocktrine\Pool;

/**
 * Class Server
 * @package Mocktrine
 */
class Server
{
    protected $pool;
    protected $serviceLocator;
    protected $host;
    protected $port;
    protected $connected;
    protected $client;

    public function __construct($host = '0.0.0.0', $port = 8181, Pool $pool = null)
    {
        error_reporting(E_ALL);

        /* Permitir al script esperar para conexiones. */
        set_time_limit(0);

        /* Activar el volcado de salida implícito, así veremos lo que estamo obteniendo
         * mientras llega. */
        ob_implicit_flush();

        if (is_null($pool)) {
            $pool = new Pool();
        }
        $this->pool = $pool;

        $this->host = $host;
        $this->port = $port;
    }

    protected function createSocket()
    {
        if (($sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
            echo "socket_create() falló: razón: " . socket_strerror(socket_last_error()) . "\n";
        }

        if (socket_bind($sock, $this->host, $this->port) === false) {
            echo "socket_bind() falló: razón: " . socket_strerror(socket_last_error($sock)) . "\n";
        }

        if (socket_listen($sock, 5) === false) {
            echo "socket_listen() falló: razón: " . socket_strerror(socket_last_error($sock)) . "\n";
        }

        return $sock;
    }

    public function serve()
    {
        $sock = $this->createSocket();

        //start loop to listen for incoming connections
        while (true) {
            //Accept incoming connection - This is a blocking call
            $this->client = socket_accept($sock);

            //display information about the client who is connected
            if (socket_getpeername($this->client, $address, $this->port)) {
                echo "Client $address : $this->port is now connected to us. \n";
            }

            $this->processCommand();


            echo "Client $address : $this->port disconnected. \n";
            socket_close($this->client);
        }
    }

    protected function processCommand()
    {
        //read data from the incoming socket
        if (false !== ($buf = socket_read($this->client, 2048, PHP_NORMAL_READ))) {
            //echo $input;

            $response = "OK .. $buf";
            if (is_null(($command = json_decode($buf)))) {
                $response = 'Not valid json format!';
            };
            echo $command->command;
            /*try {

                $command = json_decode($buf, JSON_UNESCAPED_UNICODE);
            } catch (\Exception $e) {
                echo 'not valid json entity';
                $response = 'not valid json entity';
                var_dump($e);die;
            }*/

            echo $response . "\n";

            // Display output  back to client
            socket_write($this->client, $response);
        }
    }

        /*

        do {
            if (($msgsock = socket_accept($sock)) === false) {
                echo "socket_accept() falló: razón: " . socket_strerror(socket_last_error($sock)) . "\n";
                break;
            }*/
            /* Enviar instrucciones. */
            //$msg = "\nBienvenido al Servidor De Prueba de PHP. \n" .
            //    "Para salir, escriba 'quit'. Para cerrar el servidor escriba 'shutdown'.\n";
            //socket_write($msgsock, $msg, strlen($msg));
/*
            do {
                if (false === ($buf = socket_read($msgsock, 2048, PHP_NORMAL_READ))) {
                    echo "socket_read() falló: razón: " . socket_strerror(socket_last_error($msgsock)) . "\n";
                    break 2;
                }

                if (!$buf = trim($buf)) {
                    continue;
                }
                if ($buf == 'quit') {
                    break;
                }
                if ($buf == 'shutdown') {
                    socket_close($msgsock);
                    break 2;
                }

                $talkback = "PHP: Usted dijo '$buf'.\n";
                socket_write($msgsock, $talkback, strlen($talkback));
                echo "$buf\n";
            } while (true);
            socket_close($msgsock);
        } while (true);

        socket_close($sock);
*/
        //if (!$this->lazy) {
        //    $this->pool->getStorage()->refresh();
        //}
        //$this->connected = true;
        //$socket = $this->createSocket();
        //while ($this->connected) {
        //    while ($client = socket_accept($socket)) {
        //        $text = socket_read($socket, 1024);
        //        echo $text;
        //    }
        //}
    //}
}
