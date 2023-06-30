<?php
define('ENV_PRODUCTION', 'production');
define('ENV_STAGING', 'staging');
define('ENV_DEVELOPMENT', 'development');
define('ENV_LOCAL', 'local');

define('APP_TYPE_PC','pc');
define('APP_TYPE_ANDROID','android');
define('APP_TYPE_IPHONE','iphone');
define('APP_TYPE_GOOGLEPLAY','googleplay');

define('DATABASE_DEFINE', 'kanpani_define');
define('DATABASE_USER', 'kanpani_user');
define('DATABASE_EVENT', 'kanpani_event');
define('DATABASE_LOG', 'kanpani_log');
define('DATABASE_PRERELEASE', 'kanpani_prerelease');
define('DATABASE_RANKING', 'kanpani_ranking');
define('DATABASE_SUPPORT', 'kanpani_support');
define('DATABASE_TEMP', 'kanpani_temp');
define('DATABASE_WORLD', 'kanpani_world');
define('DATABASE_MASTER', DATABASE_DEFINE);
define('DATABASE_SLAVE', DATABASE_DEFINE);
define('DATABASE_DEFAULT', DATABASE_USER);
define('DATABASE_LIST', array(
    DATABASE_DEFINE,
    DATABASE_USER
));
define('CURRENT_TIMESTAMP', 'CURRENT_TIMESTAMP');

define('ACCOUNT_TYPE_DMM', 'dmm');

define('DI_SERVICE_URL','url');
define('DI_SERVICE_CACHE','cache');
define('DI_SERVICE_SESSION_CACHE','session_cache');
define('DI_SERVICE_MASTER_CACHE','master_cache');
define('DI_SERVICE_MISSION_CACHE', 'mission_cache');
define('DI_SERVICE_MODELSMETADATA','modelsMetadata');
define('DI_SERVICE_MODELSCACHE','modelsCache');
define('DI_SERVICE_MODELSMANAGER','modelsManager');
define('DI_SERVICE_LOGGER','logger');
define('DI_SERVICE_SESSION','session');
define('DI_SERVICE_CONFIG','config');
define('DI_SERVICE_ROUTER','router');
define('DI_SERVICE_LOCATE','locate');
define('DI_SERVICE_TRANSACTION','transactions');
define('DI_SERVICE_DATE','date');
define('DI_SERVICE_HELPER','helper');
define('DI_SERVICE_AUTH','auth_cms');
define('DI_SERVICE_ACHIEVEMENT','achievement');
define('DI_SERVICE_AWS','aws');

define('SUCCESS', 'success');
define('FAIL', 'success');
define('HTTP_STATUS_CODE', 'http_status_code');
define('HTTP_STATUS_CODE_200', '200');
define('DATA', 'data');
define('IS_ERROR', 'is_error');
define('RESULT', 'result');
define('MESSAGE', 'message');
define('ACCESS_TOKEN', 'access_token');


define('CONTENT_TYPE', serialize(array(
    'XML' => 'text/xml',
    'JSON' => 'application/json'
)));
define('EXPIRES_TOKEN', 3600);

define('EMAIL_COMPANY', '@realmemobile.vn');
