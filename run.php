<?php
require_once 'AlertesBoncoin.php';
$ctrl = new AlertesBoncoin('http://www.leboncoin.fr/locations/offres/rhone_alpes/?f=a&th=1&mre=700&sqs=3&ros=2&ret=2&zz=69001', 'bchoquet@gmail.com', 'Locations Lyon 1');
$ctrl->run();