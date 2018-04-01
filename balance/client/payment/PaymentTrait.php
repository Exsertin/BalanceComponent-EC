<?php
/**
 * Created by PhpStorm.
 * User: late
 * Date: 03.03.17
 * Time: 16:08
 */

namespace app\components\balance\client\payment;

use app\models\OrdersTo1s;
use app\models\OrdersToCash;
use app\Uii;
use yii\base\Event;
use yii\base\UnknownClassException;

trait PaymentTrait
{
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
     *
     * @return bool
     */
    protected function checkSender(Event $event):bool
    {
        $sender = $event->sender;

        if (!$sender instanceof PaymentInterface) {
            $interface = PaymentInterface::class;
            $exception = new UnknownClassException("Class {$sender::className()} no implements $interface");
            Uii::error($exception);

            return false;
        }

        return true;
    }

    /**
     * @param Event $event
     *
     * @return array
     */
    protected function parseSender(Event $event):array
    {
        /**
         * @var OrdersTo1s|OrdersToCash $sender
         */
        $sender = $event->sender;
        $data = $sender->toArray([]);
        $data[self::ORDER_ID_ATTRIBUTE] = $sender->orders_id;

        return [
            'account' => $sender->order->customers_id,
            'amount' => $sender->sum_to_order,
            'data' => $data,
        ];
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
         * @var int   $account
         * @var float $amount
         * @var array $data
         */
        extract($this->parseSender($event), EXTR_OVERWRITE);

        $this->decrease($account, $amount, $data);
    }
}
