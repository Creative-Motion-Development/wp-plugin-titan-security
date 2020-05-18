<?php
/***************************************************************************************
 * Different ways to get remote address: direct & behind proxy
 **************************************************************************************/
define( 'WTITAN_BRUTEFORCE_DIRECT_ADDR', 'REMOTE_ADDR' );
define( 'WTITAN_BRUTEFORCE_PROXY_ADDR', 'HTTP_X_FORWARDED_FOR' );
