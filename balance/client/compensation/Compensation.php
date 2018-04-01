<?php
/**
 * Created by PhpStorm.
 * User: late
 * Date: 03.03.17
 * Time: 14:20
 */

namespace app\components\balance\client\compensation;

use app\components\balance\client\BalanceInterface;
use app\models\ClaimCompensation;
use app\models\ClaimCompensationType;
use yii\base\Event;
use yii\base\Object;

/**
 * @property Discount    $discount    This property is read-only.
 * @property NotDiscount $notDiscount This property is read-only.
 * @property Refund      $refund      This property is read-only
 */
final class Compensation extends Object implements BalanceInterface
{
    /**
     * @var Discount
     */
    private $_discount;

    /**
     * @var NotDiscount
     */
    private $_notDiscount;

    /**
     * @var Refund
     */
    private $_refund;

    /**
     * @return Discount
     */
    public function getDiscount()
    {

        if (!$this->_discount) {
            $this->_discount = new Discount();
        }

        return $this->_discount;
    }

    /**
     * @return NotDiscount
     */
    public function getNotDiscount()
    {

        if (!$this->_notDiscount) {
            $this->_notDiscount = new NotDiscount();
        }

        return $this->_notDiscount;
    }

    /**
     * @return Refund
     */
    public function getRefund()
    {

        if (!$this->_refund) {
            $this->_refund = new Refund();
        }

        return $this->_refund;
    }

    /**
     * @param Event $event
     */
    public function up(Event $event)
    {
        if (!$compensation = $this->getType($event)) {
            return;
        }

        $compensation->up($event);
    }

    /**
     * @param Event $event
     *
     * @return Discount|NotDiscount|Refund|null
     */
    private function getType(Event $event)
    {
        $sender = $event->sender;

        if (!$sender instanceof ClaimCompensation || !$compensationType = $sender->claimCompensationType) {
            return null;
        }

        if ($compensationType->flag_discount === ClaimCompensationType::FLAG_DISCOUNT_ACTIVE) {
            return $this->discount;
        } elseif ($compensationType->id === ClaimCompensationType::TYPE_PRODUCT_RETURNS) {
            return $this->refund;
        } else {
            return $this->notDiscount;
        }
    }

    /**
     * @param Event $event
     */
    public function down(Event $event)
    {
        if (!$compensation = $this->getType($event)) {
            return;
        }

        $compensation->down($event);
    }
}
