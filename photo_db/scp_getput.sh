#!/bin/sh

/home2/chroot/usr/local/php/bin/php /home2/chroot/home/xhankyu/public_html/photo_db/getcsv.php
/home2/chroot/usr/local/php/bin/php /home2/chroot/home/xhankyu/public_html/photo_db/gethotelxml.php
/home2/chroot/usr/local/php/bin/php /home2/chroot/home/xhankyu/public_html/photo_db/MakeXml_main.php

/home2/chroot/usr/local/php/bin/php /home2/chroot/home/xhankyu/public_html/photo_db/makexml_for_test/getcsv.php
/home2/chroot/usr/local/php/bin/php /home2/chroot/home/xhankyu/public_html/photo_db/makexml_for_test/gethotelxml.php
/home2/chroot/usr/local/php/bin/php /home2/chroot/home/xhankyu/public_html/photo_db/makexml_for_test/MakeXml_main.php

/home2/chroot/usr/local/php/bin/php /home2/chroot/home/xhankyu/public_html/photo_db/makeothers_kansai.php
/home2/chroot/usr/local/php/bin/php /home2/chroot/home/xhankyu/public_html/photo_db/makeothers_tokyo.php
/home2/chroot/usr/local/php/bin/php /home2/chroot/home/xhankyu/public_html/photo_db/makeothers_senmon_link.php
/home2/chroot/usr/local/php/bin/php /home2/chroot/home/xhankyu/public_html/photo_db/putxml.php
