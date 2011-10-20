<?php

require '/home/steve/projects/session/src/autoload.php';

class Session extends spriebsch\session\AbstractSession
{
    public function getCounter()
    {
        return $this->get('counter');
    }

    public function incrementCounter()
    {
        if (!$this->has('counter')) {
            $this->set('counter', 0);
        }

        $this->set('counter', $this->get('counter') + 1);
    }
}

$session = new Session(new spriebsch\session\PhpSessionBackend());
$session->configure('session-name', 'localhost');

$session->start('foo');
$session->incrementCounter();

var_dump($session->getCounter());

$session->commit();
