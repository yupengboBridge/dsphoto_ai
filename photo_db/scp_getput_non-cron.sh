#!/bin/sh

php getcsv.php
php gethotelxml.php
php MakeXml_main.php test

php makeothers_kansai.php
php makeothers_tokyo.php
php makeothers_senmon_link.php
php putxml.php
