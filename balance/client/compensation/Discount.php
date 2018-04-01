<?php
/**
 * Created by PhpStorm.
 * User: late
 * Date: 03.03.17
 * Time: 14:26
 */

namespace app\components\balance\client\compensation;


use app\components\balance\client\BalanceInterface;
use app\components\balance\client\ManagerDb;
use app\models\CustomerBalanceTransactionType;

/**
 * Скидка
 */
class Discount extends ManagerDb implements BalanceInterface
{
    use CompensationTrait;

    public function init()
    {
        parent::init();
        $this->accountBalanceAttribute = self::COMPENSATION_SUM_ATTRIBUTE;
        $this->typeIdAttributeValue = CustomerBalanceTransactionType::TYPE_DISCOUNT;
    }
}
