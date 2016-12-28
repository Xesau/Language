<?php

use Xesau\Language;

include_once __DIR__.'/../vendor/Xesau/Language.php';

Language::loadFile(__DIR__.'/en-US.txt');

echo Language::translate('user.registered', 'Xesau', Language::formatAgo(time() - 10000));
// Should say: Xesau registered two hours ago

echo '<br /><br />';

echo Language::translate('user.registered', 'NewUser', Language::formatAgo(time() - 2));
// Should say: Xesau registered just now

echo '<br /><br />';

echo Language::translate('user.registered', 'Test', Language::formatAgo(null));
// Should say: Xesau registered never