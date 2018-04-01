<?php
/**
 * Created by PhpStorm.
 * User: late
 * Date: 06.03.17
 * Time: 11:30
 */

namespace app\components\balance\client\order;


use app\components\balance\client\BalanceTrait;
use app\models\Orders;
use yii\base\Event;

trait OrderTrait
{
    use BalanceTrait;

    /**
     * @param Event $event
     */
    public function up(Event $event)
    {
        if (!$this->checkSender($event)) {
            return;
        }

        /**
         * @var int   $account
         * @var float $amount
         * @var array $data
         */
        extract($this->parseSender($event), EXTR_OVERWRITE);

        $this->increase($account, $amount, $data);
    }


    /**
     * @param Event $event
     */
    public function down(Event $event)
    {
        if (!$this->checkSender($event)) {
            return;
        }

        /**
         * @var Orders $sender
         */
        $sender = $event->sender;
        $orderId = $sender->orders_id;

        $transaction = $this->findTransactionByOrderId($orderId);

        if (!$transaction) {
            return;
        }

        /**
         * @var int   $account
         * @var float $amount
         * @var array $data
         */
        extract($this->parseSender($event), EXTR_OVERWRITE);

        $transactionId = $transaction[$this->getTransactionIdAttribute()];

        $this->revert($transactionId, $data);
    }
}
