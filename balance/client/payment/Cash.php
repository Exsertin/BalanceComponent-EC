<?php
/**
 * Created by PhpStorm.
 * User: late
 * Date: 03.03.17
 * Time: 16:07
 */

namespace app\components\balance\client\payment;

use app\components\balance\client\BalanceInterface;
use app\components\balance\client\ManagerDb;
use app\models\CustomerBalanceTransactionType;

/**
 * Наличный платёж
 */
class Cash extends ManagerDb implements BalanceInterface
{
    use PaymentTrait;

    public function init()
    {
        parent::init();
        $this->accountBalanceAttribute = self::PAYMENT_SUM_ATTRIBUTE;
        $this->typeIdAttributeValue = CustomerBalanceTransactionType::TYPE_PAYMENT_CASH;
    }
}
