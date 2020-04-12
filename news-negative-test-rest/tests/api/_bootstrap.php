<?php

declare(strict_types = 1);

use Userstory\ComponentAutoconfig\components\web\Autoconfig;
use yii\web\Application;

require_once __DIR__ . '/../../../../../vendor/autoload.php';
require_once __DIR__ . '/../../../../../vendor/yiisoft/yii2/Yii.php';

$config = (new Autoconfig())->load(__DIR__ . '/../../../../../protected/config/web.php');

new Application($config);
