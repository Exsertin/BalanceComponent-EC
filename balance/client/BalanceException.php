<?php
/**
 * Created by PhpStorm.
 * User: late
 * Date: 07.03.17
 * Time: 16:41
 */

namespace app\components\balance\client;


use yii\web\ServerErrorHttpException;

/**
 * Исключение для баланса
 */
class BalanceException extends ServerErrorHttpException
{
    public function getName()
    {
        return 'Balance Error';
    }
}