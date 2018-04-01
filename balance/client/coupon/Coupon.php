<?php

/**
 * Created by PhpStorm.
 * User: late
 * Date: 15.03.17
 * Time: 15:24
 */
namespace app\components\balance\client\coupon;

use app\components\balance\client\BalanceInterface;
use app\components\balance\client\BalanceTrait;
use app\components\balance\client\ManagerDb;
use app\models\CouponRedeemTrack;
use app\models\CustomerBalanceTransactionType;
use app\Uii;
use yii\base\Event;
use yii\base\UnknownClassException;

/**
 * Купон
 */
class Coupon extends ManagerDb implements BalanceInterface
{
    use BalanceTrait;

    public function init()
    {
        parent::init();
        $this->accountBalanceAttribute = self::COUPON_SUM_ATTRIBUTE;
        $this->typeIdAttributeValue = CustomerBalanceTransactionType::TYPE_COUPON;
    }

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
    protected function checkSender(Event $event): bool
    {
        $sender = $event->sender;

        if (!$sender instanceof CouponRedeemTrack) {
            $interface = CouponRedeemTrack::class;
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
    protected function parseSender(Event $event): array
    {
        /**
         * @var CouponRedeemTrack $sender
         */
        $sender = $event->sender;
        $data = [];
        $data[self::ORDER_ID_ATTRIBUTE] = $sender->order_id;

        return [
            'account' => $sender->customer_id,
            'amount' => $sender->redeem_sum,
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