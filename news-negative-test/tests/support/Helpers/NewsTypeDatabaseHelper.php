<?php

declare(strict_types = 1);

namespace UserstoryTemp\NewsNegativeTest\tests\support\Helpers;

use Codeception\Exception\ModuleException;
use Codeception\Lib\Driver\Db as DbDriver;
use Codeception\Module;
use Codeception\Module\Db;
use Codeception\Util\Fixtures;
use Userstory\ComponentHelpers\helpers\ArrayHelper;
use UserstoryTemp\NewsNegativeTest\entities\newsType\ActiveRecord;
use yii;

/**
 * Хелпер для создания предусловий в тестах API сущности "Типы новостей".
 */
class NewsTypeDatabaseHelper extends Module
{
    public const NEWS_TYPE_DATA_KEY = 'NewsType_';

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
    public function getDbDriver(): DbDriver
    {
        return $this->getModuleDb()->_getDriver();
    }

    /**
     * Возвращает название таблицу сущности "Тип новостей".
     *
     * @return string
     */
    protected function getNewsTypeTableName(): string
    {
        return Yii::$app->db->schema->getRawTableName(ActiveRecord::tableName());
    }

    /**
     * Добавляет запись в таблицу сущности "Тип новостей".
     *
     * @param array $params Значение полей записи, которую нужно добавить.
     *
     * @throws ModuleException Если модуль DB не зарегистрирован в конфигурации codeception.
     *
     * @return int
     */
    public function addNewsTypeEntity(array $params): int
    {
        $newsTypeData       = [
            'name'      => ArrayHelper::getValue($params, 'name', sqs('NameNewsType')),
            'isDefault' => ArrayHelper::getValue($params, 'isDefault', false),
        ];
        $newsTypeData['id'] = $this->getModuleDb()->haveInDatabase($this->getNewsTypeTableName(), $newsTypeData);
        Fixtures::add(self::NEWS_TYPE_DATA_KEY . $newsTypeData['id'], $newsTypeData);
        return $newsTypeData['id'];
    }

    /**
     * Обновляет запись в таблице сущности "Тип новостей".
     *
     * @param int   $id     Идентификатор обновляемой записи.
     * @param array $params Значение полей записи, которую нужно добавить.
     *
     * @throws ModuleException Если модуль DB не зарегистрирован в конфигурации codeception.
     *
     * @return void
     */
    protected function updateNewsTypeEntityById(int $id, array $params): void
    {
        $this->getModuleDb()->updateInDatabase($this->getNewsTypeTableName(), [
            'name'      => ArrayHelper::getValue($params, 'name', sqs('NameNewsType')),
            'isDefault' => ArrayHelper::getValue($params, 'isDefault', 1),
        ], [
            'id' => $id,
        ]);
    }

    /**
     * Метод для удаления записи из таблицы сущности "Тип новостей" по id.
     *
     * @param int $id идентификатор, которому должна удолвлетворять удаляемая запись.
     *
     * @throws ModuleException
     */
    public function deleteOneFromNewsTypeTableById(int $id): void
    {
        $this->getDbDriver()->deleteQueryByCriteria($this->getNewsTypeTableName(), ['id' => $id]);
    }

    /**
     * Метод для удаления записей из таблицы сущности "Тип новостей".
     *
     * @param array $criteria Критерии, которым должны удолвлетворять удаляемые записи.
     *
     * @throws ModuleException
     */
    public function deleteManyFromNewsTypeTable(array $criteria = []): void
    {
        $this->getDbDriver()->deleteQueryByCriteria($this->getNewsTypeTableName(), $criteria);
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
    public function grabOneFromNewsTypeTable(array $criteria): ?array
    {
        $data = $this->grabManyFromNewsTypeTable($criteria);
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
    public function grabOneFromNewsTypeTableById(int $id): ?array
    {
        $data = $this->grabManyFromNewsTypeTable(['id' => $id]);
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
    public function grabColumnFromNewsTypeTable(string $column, array $criteria = []): array
    {
        return $this->getModuleDb()->grabColumnFromDatabase($this->getNewsTypeTableName(), $column, $criteria);
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
    public function grabNumRecordsFromNewsTypeTable(array $criteria = []): int
    {
        return $this->getModuleDb()->grabNumRecords($this->getNewsTypeTableName(), $criteria);
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
    public function grabManyFromNewsTypeTable(array $criteria = []): array
    {
        $dbDriver = $this->getDbDriver();
        $query    = $dbDriver->select('*', $this->getNewsTypeTableName(), $criteria);
        $response = $dbDriver->executeQuery($query, $criteria);
        $result   = [];
        $rowCount = $response->rowCount();
        for ($i = 0; $i < $rowCount; $i ++) {
            $result[] = $response->fetch(\PDO::FETCH_ASSOC);
        }
        return $this->format($result);
    }

    /**
     *  Метод преобразует булевый тип данных.
     *
     * @param array $data Данные полученные из Бд.
     *
     * @return array
     */
    public function format(array $data): array
    {
        $index = 0;
        foreach ($data as $item) {
            foreach ($item as $columnKey => $value) {
                if (('isDefault' === $columnKey) && ('0' === $value || 0 === $value)) {
                    $data[$index][$columnKey] = (bool)$value;
                }
                if (('isDefault' === $columnKey) && ('1' === $value || 1 === $value)) {
                    $data[$index][$columnKey] = (bool)$value;
                }
            }
            $index ++;
        }
        return $data;
    }

    /**
     * Метод возвращает значение id не существующщее в таблице сущности 'Типы новостей'.
     *
     * @throws ModuleException Если модуль DB не зарегистрирован в конфигурации codeception.
     * @throws \Exception
     *
     * @return int
     */
    public function getNotExistNewsTypeId(): int
    {
        $newsTypeIds = $this->grabColumnFromNewsTypeTable('id');
        return DataTypesValueHelper::getRandomIntMissingInArray($newsTypeIds);
    }

    /**
     * Проверяет наличие записи в таблице сущности "Тип новостей".
     *
     * @param array $criteria Критерии, которым должна удолвлетворять искомая запись.
     *
     * @throws ModuleException Если модуль DB не зарегистрирован в конфигурации codeception.
     *
     * @return void
     */
    public function seeNewsTypeInTable(array $criteria): void
    {
        $this->getModuleDb()->seeInDatabase($this->getNewsTypeTableName(), $criteria);
    }

    /**
     * Проверяет отсутствие записи в таблице сущности "Тип новостей".
     *
     * @param array $criteria Критерии, которым должна удолвлетворять искомая запись.
     *
     * @throws ModuleException Если модуль DB не зарегистрирован в конфигурации codeception.
     *
     * @return void
     */
    public function dontSeeNewsTypeInTable(array $criteria): void
    {
        $this->getModuleDb()->dontSeeInDatabase($this->getNewsTypeTableName(), $criteria);
    }

    /**
     * Проверяет количество записей в таблице сущности "Тип новостей" по указаным критериям.
     *
     * @param array $criteria Критерии, которым должна удолвлетворять искомая запись.
     * @param int   $num      кол-во искомых записей.
     *
     * @throws ModuleException Если модуль DB не зарегистрирован в конфигурации codeception.
     *
     * @return void
     */
    public function seeNumRecordsInNewsTypeTable($num, array $criteria = []): void
    {
        $this->getModuleDb()->seeNumRecords($num, $this->getNewsTypeTableName(), $criteria);
    }
}
