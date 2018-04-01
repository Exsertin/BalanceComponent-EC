<?php
/**
 * Created by PhpStorm.
 * User: late
 * Date: 17.02.17
 * Time: 9:25
 */

namespace app\components\balance;

use app\components\balance\client\compensation\Compensation;
use app\components\balance\client\coupon\Coupon;
use app\components\balance\client\delivery\Delivery;
use app\components\balance\client\order\Order;
use app\components\balance\client\payment\Payment;
use yii\base\Component;

/**
 * @property Compensation $compensation This property is read-only.
 * @property Coupon       $coupon       This property is read-only.
 * @property Delivery     $delivery     This property is read-only.
 * @property Order        $order        This property is read-only.
 * @property Payment      $payment      This property is read-only.
 *
 * @author Levin Aleksandr
 */
final class ClientBalance extends Component
{
    /**
     * @var Compensation
     */
    private $_compensation;

    /**
     * @var Coupon
     */
    private $_coupon;

    /**
     * @var Delivery
     */
    private $_delivery;

    /**
     * @var Order
     */
    private $_order;

    /**
     * @var Payment
     */
    private $_payment;

    /**
     * @return Compensation
     */
    public function getCompensation()
    {

        if (!$this->_compensation) {
            $this->_compensation = new Compensation();
        }

        return $this->_compensation;
    }

    /**
     * @return Coupon
     */
    public function getCoupon()
    {

        if (!$this->_coupon) {
            $this->_coupon = new Coupon();
        }

        return $this->_coupon;
    }

    /**
     * @return Delivery
     */
    public function getDelivery()
    {

        if (!$this->_delivery) {
            $this->_delivery = new Delivery();
        }

        return $this->_delivery;
    }

    /**
     * @return Order
     */
    public function getOrder()
    {

        if (!$this->_order) {
            $this->_order = new Order();
        }

        return $this->_order;
    }

    /**
     * @return Payment
     */
    public function getPayment()
    {

        if (!$this->_payment) {
            $this->_payment = new Payment();
        }

        return $this->_payment;
    }
}
