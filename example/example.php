<?php

require __DIR__ . '/../src/autoload.php';

use spriebsch\session\AbstractSession;
use spriebsch\session\PhpSessionBackend;

class Session extends AbstractSession
{
    public function setUser(User $user)
    {
        $this->set('user', $user);
    }

    public function hasUser()
    {
        return $this->has('user');
    }

    public function getUser()
    {
        return $this->get('user');
    }
}

class User
{
}

$backend = new PhpSessionBackend();

$session = new Session($backend);
$session->configure('session-name', '.example.com');
$session->start();

if ($session->hasUser()) {
    $user = $session->getUser();
} else {
    $session->setUser(new User());
}

var_dump($session->hasUser());
var_dump($session->getUser());
