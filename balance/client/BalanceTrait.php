<?php
/**
 * Created by PhpStorm.
 * User: late
 * Date: 03.03.17
 * Time: 14:27
 */

namespace app\components\balance\client;

use yii\base\Event;

trait BalanceTrait
{
    /**
     * @param Event $event
     *
     * @return bool
     */
    abstract protected function checkSender(Event $event):bool;

    /**
     * Формат возврата: [
     * 'account' => mixed,
     * 'amount' => float,
     * 'data' => array | null,
     * ]
     *
     * @param Event $event
     *
     * @return array
     */
    abstract protected function parseSender(Event $event):array;
}
