<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 * 
 */
class Socket {
    private $ip = '127.0.0.1';
    private $port = 1935;
    private $socket = null;
    
    public function send($msg){
        ( !is_resource( $this->socket ) && $this->socket = socket_create( AF_INET, SOCK_DGRAM, SOL_UDP ) ) or die( $this->err() );
        ( $res = socket_sendto( $this->socket, $msg, strlen( $msg ), MSG_DONTROUTE, $this->ip, $this->port )===FALSE ) or die( $this->err() );
        return $res;
    }
    
    protected function err(){
        return socket_strerror( socket_last_error() );
    }
    
    
    
    
    
    
}