<?php
/**
 * Created by PhpStorm.
 * User: late
 * Date: 07.03.17
 * Time: 14:02
 */

namespace app\components\balance\client\compensation;

use app\components\balance\client\BalanceInterface;
use app\components\balance\client\ManagerDb;
use app\models\CustomerBalanceTransactionType;

/**
 * Любая другая компенсация
 */
class NotDiscount extends ManagerDb implements BalanceInterface
{
    use CompensationTrait;

    public function init()
    {
        parent::init();
        $this->accountBalanceAttribute = self::COMPENSATION_SUM_ATTRIBUTE;
        $this->typeIdAttributeValue = CustomerBalanceTransactionType::TYPE_OTHER_COMPENSATION;
    }
}