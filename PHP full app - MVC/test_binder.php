<?php
spl_autoload_register();
$binder = new \Core\DataBinder();
$arr = [
    'name' => 'pesho',
    'age' => 24,
    'fb' => 'http://facebook.com/profile.php'
];

class MyProfile
{
    private $name;
    private $age;
    private $fb;
}

$profile = $binder->bind($arr, MyProfile::class);
var_dump($profile);