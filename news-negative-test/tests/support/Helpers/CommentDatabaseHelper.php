<?php

declare( strict_types = 1 );

namespace UserstoryTemp\NewsNegativeTest\tests\support\Helpers;

use Codeception\Exception\ModuleException;
use Codeception\Lib\Driver\Db as DbDriver;
use Codeception\Module;
use Codeception\Module\Db;
use Codeception\Util\Fixtures;
use Exception;
use Userstory\ComponentHelpers\helpers\ArrayHelper;
use UserstoryTemp\NewsNegativeTest\entities\comment\ActiveRecord;
use yii;

/**
 * Хелпер для создания предусловий в тестах API сущности "Комментарии к новостям".
 */
class CommentDatabaseHelper extends Module
{
    const COMMENT_DATA_KEY = 'Comment_';

    /**
     * Возвращает стандартный модуль codeception DB.
     *
     * @throws ModuleException Если модуль DB не зарегистрирован в конфигурации codeception.
     *
     * @return Db|Module
     */
    protected function getModuleDb(): Db
    {
        return $this->getModule('Db');
    }

    /**
     * Получаем драйвер базы данных к которой подключены.
     *
     * @return DbDriver
     *
     * @throws ModuleException
     */
    protected function getDbDriver(): DbDriver
    {
        return $this->getModuleDb()->_getDriver();
    }

    /**
     * Возвращает название таблицу сущности "Комментарии к новостям".
     *
     * @return string
     */
    protected function getCommentTableName(): string
    {
        return Yii::$app->db->schema->getRawTableName(ActiveRecord::tableName());
    }

    /**
     * Добавляет запись в таблицы сущности "Комментарии к новостям".
     *
     * @param array $params Значение полей записи, которую нужно добавить.
     *
     * @throws ModuleException Если модуль DB не зарегистрирован в конфигурации codeception.
     *
     * @return int
     */
    public function addCommentEntity(array $params): int
    {
        $commentData       = [
            'newsId' => $params['newsId'],
            'body'   => ArrayHelper::getValue($params, 'body', sqs('body')),
        ];
        $commentData['id'] = $this->getModuleDb()->haveInDatabase($this->getCommentTableName(), $commentData);
        Fixtures::add(self::COMMENT_DATA_KEY . $commentData['id'], $commentData);
        return $commentData['id'];
    }

    /**
     * Обновляет запись в таблицы сущности "Комментарии к новостям".
     *
     * @param int   $id     Идентификатор обновляемой записи.
     * @param array $params Значение полей записи, которую нужно добавить.
     *
     * @throws ModuleException Если модуль DB не зарегистрирован в конфигурации codeception.
     *
     * @return void
     */
    public function updateCommentEntityById(int $id, array $params): void
    {
        $this->getModuleDb()->updateInDatabase($this->getCommentTableName(), $params, [
            'id' => $id,
        ]);
    }

    /**
     * Метод возвращает данные конкретной записи из таблицы.
     *
     * @param int $id идентификатор записи, данные которой необходимы для вывода.
     *
     * @throws ModuleException Если модуль DB не зарегистрирован в конфигурации codeception.
     *
     * @return array
     */
    public function grabOneFromCommentTable(array $criteria): ?array
    {
        $data = $this->grabManyFromCommentTable($criteria);
        return array_shift($data);
    }

    /**
     * Метод возвращает данные конкретной записи из таблицы по указанному идентификатору.
     *
     * @param int $id идентификатор записи, данные которой необходимы для вывода.
     *
     * @throws ModuleException Если модуль DB не зарегистрирован в конфигурации codeception.
     *
     * @return array
     */
    public function grabOneFromCommentTableById(int $id): ?array
    {
        $data = $this->grabManyFromCommentTable(['id' => $id]);
        return array_shift($data);
    }

    /**
     * Метод возвращает все данные из указанного столбца записей, подходящие под условия выборки.
     *
     * @param int $id идентификатор записи, данные которой необходимы для вывода.
     *
     * @throws ModuleException Если модуль DB не зарегистрирован в конфигурации codeception.
     *
     * @return array
     */
    public function grabColumnFromCommentTable(string $column, array $criteria = []): array
    {
        return $this->getModuleDb()->grabColumnFromDatabase($this->getCommentTableName(), $column, $criteria);
    }

    /**
     * Метод возвращает все данные из таблицы.
     *
     * @param array $criteria Критерии, которым должны удолвлетворять получаемые записи.
     *
     * @throws ModuleException Если модуль DB не зарегистрирован в конфигурации codeception.
     * @throws Exception
     *
     * @return array
     */
    public function grabManyFromCommentTable(array $criteria = []): array
    {
        $dbDriver = $this->getDbDriver();
        $query    = $dbDriver->select('*', $this->getCommentTableName(), $criteria);
        $response = $dbDriver->executeQuery($query, $criteria);
        $result   = [];
        $rowCount = $response->rowCount();
        for ($i = 0; $i < $rowCount; $i ++) {
            $result[] = $response->fetch(\PDO::FETCH_ASSOC);
        }
        return $result;
    }

    /**
     * Метод возвращает значение id не существующщее в таблице сущности 'Комментарии'.
     *
     * @throws ModuleException Если модуль DB не зарегистрирован в конфигурации codeception.
     * @throws Exception
     *
     * @return int
     */
    public function getNotExistCommentId(): int
    {
        $existedIds   = $this->grabColumnFromCommentTable('id');
        return DataTypesValueHelper::getRandomIntMissingInArray($existedIds);
    }

    /**
     * Проверяет количество записей в таблице сущности "Комментарии к новостям" по указаным критериям.
     *
     * @param array $criteria Критерии, которым должна удолвлетворять искомая запись.
     * @param int   $num      кол-во искомых записей.
     *
     * @throws ModuleException Если модуль DB не зарегистрирован в конфигурации codeception.
     *
     * @return void
     */
    public function seeNumRecordsInCommentTable($num, array $criteria = []): void
    {
        $this->getModuleDb()->seeNumRecords($num, $this->getCommentTableName(), $criteria);
    }

    /**
     * Проверяет количество записей в таблице сущности "Комментарии к новостям" по указаным критериям.
     *
     * @param array $criteria Критерии, которым должна удолвлетворять искомая запись.
     * @param int   $num      кол-во искомых записей.
     *
     * @throws ModuleException Если модуль DB не зарегистрирован в конфигурации codeception.
     *
     * @return int
     */
    public function grabNumRecordsInCommentTable(array $criteria = []): int
    {
        return $this->getModuleDb()->grabNumRecords($this->getCommentTableName(), $criteria);
    }

    /**
     * Проверяет наличие записи в таблице сущности "Комментарии к новостям".
     *
     * @param array $criteria Критерии, которым должна удолвлетворять искомая запись.
     *
     * @throws ModuleException Если модуль DB не зарегистрирован в конфигурации codeception.
     *
     * @return void
     */
    public function seeInCommentTable(array $criteria): void
    {
        $this->getModuleDb()->seeInDatabase($this->getCommentTableName(), $criteria);
    }

    /**
     * Проверяет отсутствие записи в таблице сущности "Комментарии к новостям".
     *
     * @param array $criteria Критерии, которым должна удолвлетворять искомая запись.
     *
     * @throws ModuleException Если модуль DB не зарегистрирован в конфигурации codeception.
     *
     * @return void
     */
    public function dontSeeInCommentTable(array $criteria): void
    {
        $this->getModuleDb()->dontSeeInDatabase($this->getCommentTableName(), $criteria);
    }

    /**
     * Метод для удаление записей из таблицы сущности "Комментарии к новостям".
     *
     * @param array $criteria Критерии, которым должны удолвлетворять удаляемые записи.
     *
     * @throws ModuleException
     */
    public function deleteFromCommentTable(array $criteria): void
    {
        $this->getDbDriver()->deleteQueryByCriteria($this->getCommentTableName(), $criteria);
    }
}
