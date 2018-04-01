<?php
/**
 * Created by PhpStorm.
 * User: late
 * Date: 23.02.17
 * Time: 10:05
 */

namespace app\components\balance\client;

use app\components\ActiveRecord;
use app\components\balance\client\payment\PaymentInterface;
use app\models\ClaimCompensation;
use app\models\CouponRedeemTrack;
use app\models\Orders;
use app\Uii;
use yii\base\Behavior;
use yii\db\AfterSaveEvent;

/**
 * Что - то вроде switcher`a
 * Определяет какой менеджер транзакций будет использован в данный момент
 *
 * @author Levin Aleksandr
 */
class BalanceTransactionBehavior extends Behavior
{
    const EVENT_BALANCE_DELIVERY_UP = 'balanceDeliveryUp';
    const EVENT_BALANCE_DELIVERY_DOWN = 'balanceDeliveryDown';
    const EVENT_BALANCE_ORDER_UP = 'balanceOrderUp';
    const EVENT_BALANCE_ORDER_DOWN = 'balanceOrderDown';
    const ORDERS_STATUS_ATTRIBUTE = 'orders_status';

    /**
     * @return array
     * @throws BalanceException
     */
    public function events()
    {
        $client = Uii::$app->balanceManager->client;

        return [
            ActiveRecord::EVENT_BEFORE_DELETE => 'check',
            ActiveRecord::EVENT_BEFORE_INSERT => 'check',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'check',

            ClaimCompensation::EVENT_COMPENSATION_ATTACH => [$client->compensation, 'up'],
            ClaimCompensation::EVENT_COMPENSATION_DETACH => [$client->compensation, 'down'],
            CouponRedeemTrack::EVENT_COUPON_ATTACH => [$client->coupon, 'up'],
            CouponRedeemTrack::EVENT_COUPON_DETACH => [$client->coupon, 'down'],
            PaymentInterface::EVENT_PAYMENT_ATTACH => [$client->payment, 'up'],
            PaymentInterface::EVENT_PAYMENT_DETACH => [$client->payment, 'down'],

            ActiveRecord::EVENT_AFTER_UPDATE => 'balanceOrder',
            //События, которые возникают при смене статуса заказа
            self::EVENT_BALANCE_DELIVERY_UP => [$client->delivery, 'up'],
            self::EVENT_BALANCE_DELIVERY_DOWN => [$client->delivery, 'down'],
            self::EVENT_BALANCE_ORDER_UP => [$client->order, 'up'],
            self::EVENT_BALANCE_ORDER_DOWN => [$client->order, 'down'],
        ];
    }

    /**
     * @throws BalanceException
     */
    public function check()
    {
        if (!$this->isTransactionActive()) {
            throw new BalanceException('Transaction is not active');
        }
    }

    /**
     * @return bool
     */
    private function isTransactionActive()
    {
        return Uii::$app->db->transaction->isActive ?? false;
    }

    /**
     * @param AfterSaveEvent $event
     */
    public function balanceOrder(AfterSaveEvent $event)
    {
        $owner = $this->owner;

        if (!$owner instanceof Orders) {
            return;
        }

        $changedAttributes = $event->changedAttributes;
        $statusOld = $changedAttributes[self::ORDERS_STATUS_ATTRIBUTE] ?? null;

        if ($statusOld === null) {
            return;
        }

        //Смена статуса с 1 на 2
        if ($statusOld === Orders::ORDER_STATUS_AWAITING_CHECKING && $owner->orders_status === Orders::ORDER_STATUS_AWAITING_PAYMENT) {
            $owner->trigger(self::EVENT_BALANCE_DELIVERY_UP);
            $owner->trigger(self::EVENT_BALANCE_ORDER_UP, $event);
        }

        //Смена статуса с 2 на 1
        if ($statusOld === Orders::ORDER_STATUS_AWAITING_PAYMENT && $owner->orders_status === Orders::ORDER_STATUS_AWAITING_CHECKING) {
            $owner->trigger(self::EVENT_BALANCE_DELIVERY_DOWN);
            $owner->trigger(self::EVENT_BALANCE_ORDER_DOWN, $event);
        }
    }
}
