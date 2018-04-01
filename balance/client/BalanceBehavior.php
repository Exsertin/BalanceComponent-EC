<?php
/**
 * Created by PhpStorm.
 * User: late
 * Date: 06.03.17
 * Time: 17:02
 */

namespace app\components\balance\client;

use app\api\core\customers\models\CustomersBalance;
use yii\base\Behavior;
use yii\db\Expression;
use yii2tech\balance\TransactionEvent;

/**
 * После любой транзакции пересчитывается баланс клиента, которому принадлежит
 * эта транзакция
 *
 * @author Levin Aleksandr
 */
class BalanceBehavior extends Behavior
{
    public function events()
    {
        return [
            ManagerDb::EVENT_AFTER_CREATE_TRANSACTION => 'recalc',
        ];
    }

    /**
     * @param TransactionEvent $event
     *
     * @throws \Exception
     */
    public function recalc(TransactionEvent $event)
    {
        $table = ManagerDb::ACCOUNT_TABLE_NAME;
        $accountId = $event->accountId;
        $condition = is_array($accountId) ? $accountId
            : [ManagerDb::ACCOUNT_LINK_ATTRIBUTE => $event->accountId];
        $value = new Expression($this->getFormula());
        $columns = [
            ManagerDb::BALANCE_ATTRIBUTE => $value,
            ManagerDb::ACCOUNT_DATE_UPDATED_ATTRIBUTE => new Expression('NOW()'),
        ];

        CustomersBalance::getDb()->createCommand()
            ->update($table, $columns, $condition)
            ->execute();
    }

    /**
     * @return string
     */
    private function getFormula()
    {
        $compensation = ManagerDb::COMPENSATION_SUM_ATTRIBUTE;
        $coupon = ManagerDb::COUPON_SUM_ATTRIBUTE;
        $delivery = ManagerDb::DELIVERY_SUM_ATTRIBUTE;
        $order = ManagerDb::ORDER_SUM_ATTRIBUTE;
        $payment = ManagerDb::PAYMENT_SUM_ATTRIBUTE;

        return "([[$compensation]]+[[$coupon]]+[[$payment]])-([[$delivery]]+[[$order]])";
    }
}
