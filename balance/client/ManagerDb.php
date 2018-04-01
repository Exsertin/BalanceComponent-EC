<?php
/**
 * Created by PhpStorm.
 * User: late
 * Date: 27.02.17
 * Time: 12:19
 */

namespace app\components\balance\client;

use app\api\common\__lib\classes\AdminGetter;
use app\Uii;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use yii\db\Exception;
use yii\db\Expression;
use yii\db\Query;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii2tech\balance\ManagerDb as ParentClass;
use yii2tech\balance\TransactionEvent;

/**
 * Общий менеджер для клиентского баланса
 *
 * Описание методов:
 * increase - Добавляет запись в таблицу транзакций и прибавляет сумму текущей транзакции к балансу
 * decrease - Аналогично методу increase, только сумма отрицательная
 * revert - Откатывает определённую транзакцию(Не удаляет, а добавляет новую запись с противоположной суммой). Т.е. работа аналогична decrease. Если присутствует доп. идентификатор, то сработает как transfer
 * transfer - Работа с двумя балансами. Осуществляет перевод(естественно создаст 2 записи)
 * calculateBalance - Считает баланс(Используется для вывода)
 * recalculateBalance - Пересчитывает баланс
 * resetBalance - Сбрасывает баланс
 * incrementAccountBalance - Увеличивает определённый баланс без создания записи о транзакции. Не рекомендую к прямому вызову.
 *
 * @author Levin Aleksandr
 */
class ManagerDb extends ParentClass
{
    const ACCOUNT_TABLE_NAME = 'customer_balance';
    const ACCOUNT_LINK_ATTRIBUTE = 'owner_id';
    const ACCOUNT_DATE_UPDATED_ATTRIBUTE = 'date_updated';
    const ORDER_ID_ATTRIBUTE = 'order_id';
    const TYPE_ID_ATTRIBUTE = 'type_id';

    const BALANCE_ATTRIBUTE = 'balance';
    const COMPENSATION_SUM_ATTRIBUTE = 'compensation_sum';
    const COUPON_SUM_ATTRIBUTE = 'coupon_sum';
    const DELIVERY_SUM_ATTRIBUTE = 'delivery_sum';
    const ORDER_SUM_ATTRIBUTE = 'order_sum';
    const PAYMENT_SUM_ATTRIBUTE = 'payment_sum';

    public $accountDateUpdatedAttribute = self::ACCOUNT_DATE_UPDATED_ATTRIBUTE;
    public $adminIdAttribute = 'admin_id';

    protected $typeIdAttributeValue;

    public function behaviors()
    {
        return [
            'balance' => BalanceBehavior::class,
        ];
    }

    public function init()
    {
        parent::init();
        $this->accountTable = self::ACCOUNT_TABLE_NAME;
        $this->setAccountIdAttribute(self::ACCOUNT_LINK_ATTRIBUTE);
        $this->transactionTable = 'customer_balance_transaction';
        $this->accountLinkAttribute = self::ACCOUNT_LINK_ATTRIBUTE;
        $this->extraAccountLinkAttribute = 'extra_owner_id';
    }

    /**
     * @return Expression
     */
    public function getDateAttributeValue()
    {
        return new Expression('NOW()');
    }

    /**
     * @param $account
     *
     * @return float|int|mixed
     * @throws \Exception
     */
    public function recalculateBalance($account)
    {
        $transaction = Uii::$app->db->beginTransaction();
        try {
            $accountId = $this->fetchAccountId($account);
            $sum = $this->calculateBalance($accountId);
            $this->resetBalance($accountId);
            $this->incrementAccountBalance($accountId, $sum);
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        $transaction->commit();

        return $sum;
    }

    /**
     * @param mixed $idOrFilter
     *
     * @return mixed
     */
    protected function fetchAccountId($idOrFilter)
    {
        if (is_int($idOrFilter)) {
            $idOrFilter = [$this->getAccountIdAttribute() => $idOrFilter];
        }

        return parent::fetchAccountId($idOrFilter);
    }

    /**
     * @param array|mixed $account
     *
     * @return mixed
     */
    public function calculateBalance($account)
    {
        $accountId = $this->fetchAccountId($account);

        return (new Query())
            ->from($this->transactionTable)
            ->andWhere([
                $this->accountLinkAttribute => $accountId,
                self::TYPE_ID_ATTRIBUTE => $this->typeIdAttributeValue,
            ])
            ->sum($this->amountAttribute, $this->db);
    }

    /**
     * @param $accountId
     *
     * @throws Exception
     */
    public function resetBalance($accountId)
    {
        $accountBalanceAttribute = $this->accountBalanceAttribute;
        $condition = [$this->getAccountIdAttribute() => $accountId];
        $table = $this->accountTable;
        $value = new Expression("[[$accountBalanceAttribute]]-[[$accountBalanceAttribute]]");
        $columns = [
            $accountBalanceAttribute => $value,
            $this->accountDateUpdatedAttribute => new Expression('NOW()'),
        ];

        $this->db->createCommand()
            ->update($table, $columns, $condition)
            ->execute();

        $event = new TransactionEvent(compact('accountId'));
        $this->trigger(ManagerDb::EVENT_AFTER_CREATE_TRANSACTION, $event);
    }

    /**
     * @param mixed     $accountId
     * @param float|int $amount
     *
     * @throws Exception
     */
    public function incrementAccountBalance($accountId, $amount)
    {
        $accountBalanceAttribute = $this->accountBalanceAttribute;
        $condition = [$this->getAccountIdAttribute() => $accountId];
        $table = $this->accountTable;
        $value = new Expression("[[$accountBalanceAttribute]]+:amount", ['amount' => $amount]);
        $columns = [
            $accountBalanceAttribute => $value,
            $this->accountDateUpdatedAttribute => new Expression('NOW()'),
        ];

        $this->db->createCommand()
            ->update($table, $columns, $condition)
            ->execute();

        $event = new TransactionEvent(compact('accountId'));
        $this->trigger(ManagerDb::EVENT_AFTER_CREATE_TRANSACTION, $event);
    }

    /**
     * @param array $attributes
     *
     * @return mixed|string
     */
    protected function createTransaction($attributes)
    {
        $attributes[$this->adminIdAttribute] = $this->getAdminIdAttributeValue();
        $attributes[self::TYPE_ID_ATTRIBUTE] = $this->typeIdAttributeValue;

        return parent::createTransaction($attributes);
    }

    /**
     * @return int
     * @throws \BadMethodCallException
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws InvalidConfigException
     * @throws InvalidParamException
     */
    public function getAdminIdAttributeValue()
    {
        return AdminGetter::getAdminID();
    }

    /**
     * @param int $orderId
     *
     * @return array|null
     */
    protected function findTransactionByOrderId(int $orderId)
    {
        $row = (new Query())
            ->from($this->transactionTable)
            ->where([
                self::ORDER_ID_ATTRIBUTE => $orderId,
                self::TYPE_ID_ATTRIBUTE => $this->typeIdAttributeValue,
            ])
            ->orderBy([$this->getTransactionIdAttribute() => SORT_DESC])
            ->one($this->db);

        if ($row === false) {
            return null;
        }

        return $this->unserializeAttributes($row);
    }
}
