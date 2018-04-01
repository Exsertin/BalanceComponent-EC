<?php
/**
 * Created by PhpStorm.
 * User: late
 * Date: 28.02.17
 * Time: 13:34
 */

namespace app\components\balance\client\order;

use app\components\balance\client\BalanceInterface;
use app\components\balance\client\ManagerDb;
use app\models\CustomerBalanceTransactionType;
use app\models\Orders;
use app\Uii;
use yii\base\Event;
use yii\base\UnknownClassException;

/**
 * Заказ
 */
class Order extends ManagerDb implements BalanceInterface
{
    use OrderTrait;

    public function init()
    {
        parent::init();
        $this->accountBalanceAttribute = self::ORDER_SUM_ATTRIBUTE;
        $this->typeIdAttributeValue = CustomerBalanceTransactionType::TYPE_ORDER;
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
        $data = $event->changedAttributes ?? [];
        $data[self::ORDER_ID_ATTRIBUTE] = $sender->orders_id;

        return [
            'account' => $sender->customers_id,
            'amount' => $sender->countPositionsSum(),
            'data' => $data,
        ];
    }
}
