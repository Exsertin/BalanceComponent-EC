<?php
/**
 * Created by PhpStorm.
 * User: late
 * Date: 03.03.17
 * Time: 15:10
 */

namespace app\components\balance\client\compensation;


use app\models\ClaimCompensation;
use app\Uii;
use yii\base\Event;
use yii\base\UnknownClassException;

trait CompensationTrait
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

        if (!$sender instanceof ClaimCompensation) {
            $class = ClaimCompensation::class;
            $exception = new UnknownClassException("Class {$sender::className()} is not $class");
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
         * @var ClaimCompensation $sender
         */
        $sender = $event->sender;
        $data = [];
        $claim = $sender->claim;
        $data[self::ORDER_ID_ATTRIBUTE] = $claim->orderProduct->orders_id;

        return [
            'account' => $claim->customers_id,
            'amount' => $sender->amount,
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
