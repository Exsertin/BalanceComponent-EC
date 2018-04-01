<?php
/**
 * Created by PhpStorm.
 * User: late
 * Date: 23.02.17
 * Time: 12:05
 */

namespace app\components\balance\client;

use yii\base\Event;

interface BalanceInterface
{
    public function up(Event $event);

    public function down(Event $event);
}
