<?php
/**
 * Created by PhpStorm.
 * User: late
 * Date: 03.03.17
 * Time: 10:07
 */

namespace app\components\balance\client\delivery;

use app\components\balance\client\BalanceInterface;
use app\components\balance\client\ManagerDb;
use app\components\balance\client\order\OrderTrait;
use app\models\CustomerBalanceTransactionType;
use app\models\Orders;
use app\models\OrdersTotal;
use app\Uii;
use yii\base\Event;
use yii\base\UnknownClassException;

/**
 * Доставка
 */
class Delivery extends ManagerDb implements BalanceInterface
{
    use OrderTrait;

    public function init()
    {
        parent::init();
        $this->accountBalanceAttribute = self::DELIVERY_SUM_ATTRIBUTE;
        $this->typeIdAttributeValue = CustomerBalanceTransactionType::TYPE_DELIVERY;
    }

    /**
     * @param Event $event
     *
     * @return bool
     */
    public function checkSender(Event $event):bool
    {
        $sender = $event->sender;

        if (!$sender instanceof Orders) {
            $class = Orders::class;
            $exception = new UnknownClassException("Class {$sender::className()} is not $class");
            Uii::error($exception);

            return false;
        }

        $shipping = $sender->shippingOne;

        if (!$shipping || $shipping->class !== OrdersTotal::CLASS_SHIPPING) {
            return false;
        }

        return true;
    }

    /**
     * @param Event $event
     *
     * @return array
     */
    public function parseSender(Event $event):array
    {
        /**
         * @var Orders $sender
         */
        $sender = $event->sender;
        $data = $sender->shippingOne->toArray([]) ?? [];
        $data[self::ORDER_ID_ATTRIBUTE] = $sender->orders_id;

        return [
            'account' => $sender->customers_id,
            'amount' => $sender->shippingOne->valueFloat,
            'data' => $data,
        ];
    }
}
