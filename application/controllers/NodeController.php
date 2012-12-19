<?php

class NodeController extends Zend_Controller_Action {

    public function init() {
        /* Initialize action controller here */
    }

    public function indexAction() {
        // action body;
        $elephant = new Client('http://192.168.10.2:8888', 'socket.io', 1, false, true, true);

        $elephant->init();
        $elephant->send(
                5, null, null, json_encode(array('name' => 'action', 'args' => 'You have received a new update.'))
        );
        $elephant->close();
    }

}

