<?php
/**
 * Created by PhpStorm.
 * User: late
 * Date: 23.02.17
 * Time: 12:44
 */

namespace app\components\balance\client\payment;


interface PaymentInterface
{
    const EVENT_PAYMENT_ATTACH = 'paymentAttach';
    const EVENT_PAYMENT_DETACH = 'paymentDetach';
}
