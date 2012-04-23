<?php
require_once 'AlertesBoncoin.php';
$ctrl = new AlertesBoncoin('http://www.leboncoin.fr/locations/offres/rhone_alpes/rhone/?f=a&th=1&mre=1000&sqs=7&ros=3&roe=3&ret=2&location=Lyon%2069001', 'bchoquet@gmail.com', 'Locations Lyon 1', 'mem', 'emilie.chanussot@free.fr');
$ctrl->run();
