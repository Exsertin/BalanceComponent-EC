<?php
/**
 * Created by PhpStorm.
 * User: late
 * Date: 20.02.17
 * Time: 10:42
 */

namespace app\components\balance\client\payment;

use app\components\balance\client\BalanceInterface;
use app\models\OrdersTo1s;
use app\models\OrdersToCash;
use yii\base\Event;
use yii\base\Object;

/**
 * @property Cash     $cash     This property is read-only.
 * @property Cashless $cashless This property is read-only
 */
final class Payment extends Object implements BalanceInterface
{
    const TYPE_CASH = 1;
    const TYPE_CASHLESS = 2;

    /**
     * @var Cash
     */
    private $_cash;
    /**
     * @var Cashless
     */
    private $_cashless;

    /**
     * @return Cash
     */
    public function getCash()
    {

        if (!$this->_cash) {
            $this->_cash = new Cash();
        }

        return $this->_cash;
    }

    /**
     * @return Cashless
     */
    public function getCashless()
    {

        if (!$this->_cashless) {
            $this->_cashless = new Cashless();
        }

        return $this->_cashless;
    }

    /**
     * @param Event $event
     */
    public function up(Event $event)
    {
        if (!$payment = $this->getType($event)) {
            return;
        }

        $payment->up($event);
    }

    /**
     * @param Event $event
     *
     * @return Cash|Cashless|null
     */
    private function getType(Event $event)
    {
        $sender = $event->sender;

        if ($sender instanceof OrdersToCash) {
            return $this->cash;
        } elseif ($sender instanceof OrdersTo1s) {
            return $this->cashless;
        }

        return null;
    }

    /**
     * @param Event $event
     */
    public function down(Event $event)
    {
        if (!$payment = $this->getType($event)) {
            return;
        }

        $payment->down($event);
    }
}
