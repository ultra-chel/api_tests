<?php

declare(strict_types = 1);

namespace UserstoryTemp\NewsNegativeTest\tests\support\Helpers;

use Codeception\Exception\ModuleException;
use Codeception\Lib\Driver\Db as DbDriver;
use Codeception\Module;
use Codeception\Module\Db;
use Codeception\Util\Fixtures;
use Userstory\ComponentHelpers\helpers\ArrayHelper;
use UserstoryTemp\NewsNegativeTest\entities\tag\ActiveRecord;
use yii;

/**
 * Хелпер для создания предусловий в тестах API сущности "Тэги".
 */
class TagDatabaseHelper extends Module
{
    public const TAG_DATA_KEY = 'Tag_';

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
     * Возвращает название таблицы сущности "Тэги".
     *
     * @return string
     */
    protected function getTagTableName(): string
    {
        return Yii::$app->db->schema->getRawTableName(ActiveRecord::tableName());
    }

    /**
     * Добавляет запись в таблицы сущности "Тэги".
     *
     * @param array $params Значение полей записи, которую нужно добавить.
     *
     * @throws ModuleException Если модуль DB не зарегистрирован в конфигурации codeception.
     *
     * @return int
     */
    public function addTagEntity(array $params): int
    {
        $tagData       = [
            'name' => ArrayHelper::getValue($params, 'name', sqs('name')),
        ];
        $tagData['id'] = $this->getModuleDb()->haveInDatabase($this->getTagTableName(), $tagData);
        Fixtures::add(self::TAG_DATA_KEY . $tagData['id'], $tagData);
        return $tagData['id'];
    }

    /**
     * Обновляет запись в таблицы сущности "Тэги".
     *
     * @param int   $id     Идентификатор обновляемой записи.
     * @param array $params Значение полей записи, которую нужно добавить.
     *
     * @throws ModuleException Если модуль DB не зарегистрирован в конфигурации codeception.
     *
     * @return void
     */
    public function updateTagEntityById(int $id, array $params): void
    {
        $this->getModuleDb()->updateInDatabase($this->getTagTableName(), $params, [
            'id' => $id,
        ]);
    }

    /**
     * Метод для удаления записи из таблицы сущности "Тэги" по id.
     *
     * @param int $id идентификатор, которому должна удолвлетворять удаляемая запись.
     *
     * @throws ModuleException
     */
    public function deleteOneFromTagTableById(int $id): void
    {
        $this->getDbDriver()->deleteQueryByCriteria($this->getTagTableName(), ['id' => $id]);
    }

    /**
     * Метод для удаление записей из таблицы сущности "Тэги".
     *
     * @param array $criteria Критерии, которым должны удолвлетворять удаляемые записи.
     *
     * @throws ModuleException
     */
    public function deleteManyFromTagTable(array $criteria): void
    {
        $this->getDbDriver()->deleteQueryByCriteria($this->getTagTableName(), $criteria);
    }

    /**
     * Метод возвращает данные конкретной записи из таблицы.
     *
     * @param array $criteria критерии, которым должны удовлетворять удаляемые записи.
     *
     * @throws ModuleException Если модуль DB не зарегистрирован в конфигурации codeception.
     *
     * @return array
     */
    public function grabOneFromTagTable(array $criteria): ?array
    {
        $data = $this->grabManyFromTagTable($criteria);
        return array_shift($data);
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
    public function grabOneFromTagTableById(int $id): array
    {
        $data = $this->grabManyFromTagTable(['id' => $id]);
        return array_shift($data);
    }

    /**
     * Метод возвращает все данные из указанного столбца записей, подходящие под условия выборки.
     *
     * @param array  $criteria Критерии, которым должны удолвлетворять получаемые записи.
     * @param string $column   атрибуты сущности, по которым происходит поиск критериев.
     *
     * @throws ModuleException Если модуль DB не зарегистрирован в конфигурации codeception.
     *
     * @return array
     */
    public function grabColumnFromTagTable(string $column, array $criteria = []): array
    {
        return $this->getModuleDb()->grabColumnFromDatabase($this->getTagTableName(), $column, $criteria);
    }

    /**
     * Метод возвращает количество записей в таблице, подходящие под условия выборки.
     *
     * @param array $criteria Критерии, которым должны удолвлетворять получаемые записи.
     *
     * @throws ModuleException Если модуль DB не зарегистрирован в конфигурации codeception.
     *
     * @return int
     */
    public function grabNumRecordsFromTagTable(array $criteria = []): int
    {
        return $this->getModuleDb()->grabNumRecords($this->getTagTableName(), $criteria);
    }

    /**
     * Метод возвращает все данные из таблицы.
     *
     * @param array $criteria Критерии, которым должны удолвлетворять получаемые записи.
     *
     * @throws ModuleException Если модуль DB не зарегистрирован в конфигурации codeception.
     * @throws \Exception
     *
     * @return array
     */
    public function grabManyFromTagTable(array $criteria = []): array
    {
        $dbDriver = $this->getDbDriver();
        $query    = $dbDriver->select('*', $this->getTagTableName(), $criteria);
        $response = $dbDriver->executeQuery($query, $criteria);
        $result   = [];
        for ($i = 0; $i < $response->rowCount(); $i ++) {
            $result[] = $response->fetch(\PDO::FETCH_ASSOC);
        }
        return $result;
    }

    /**
     * Метод возвращает значение id не существующщее в таблице сущности 'Тэги'.
     *
     * @throws ModuleException Если модуль DB не зарегистрирован в конфигурации codeception.
     * @throws \Exception
     *
     * @return int
     */
    public function getNotExistTagId(): int
    {
        $tagIds = $this->grabColumnFromTagTable('id');
        return DataTypesValueHelper::getRandomIntMissingInArray($tagIds);
    }

    /**
     * Проверяет наличие записи в таблице сущности "Тэги".
     *
     * @param array $criteria Критерии, которым должна удолвлетворять искомая запись.
     *
     * @throws ModuleException Если модуль DB не зарегистрирован в конфигурации codeception.
     *
     * @return void
     */
    public function seeInTagTable(array $criteria): void
    {
        $this->getModuleDb()->seeInDatabase($this->getTagTableName(), $criteria);
    }

    /**
     * Проверяет отсутствие записи в таблице сущности "Тэги".
     *
     * @param array $criteria Критерии, которым должна удолвлетворять искомая запись.
     *
     * @throws ModuleException Если модуль DB не зарегистрирован в конфигурации codeception.
     *
     * @return void
     */
    public function dontSeeInTagTable(array $criteria): void
    {
        $this->getModuleDb()->dontSeeInDatabase($this->getTagTableName(), $criteria);
    }

    /**
     * Проверяет количество записей в таблице сущности "Тэги" по указаным критериям.
     *
     * @param array $criteria Критерии, которым должна удолвлетворять искомая запись.
     * @param int   $num      кол-во искомых записей.
     *
     * @throws ModuleException Если модуль DB не зарегистрирован в конфигурации codeception.
     *
     * @return void
     */
    public function seeNumRecordsInTagTable($num, array $criteria): void
    {
        $this->getModuleDb()->seeNumRecords($num, $this->getTagTableName(), $criteria);
    }
}
