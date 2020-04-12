<?php

declare( strict_types = 1 );

namespace UserstoryTemp\NewsNegativeTest\tests\support\Helpers;

use Codeception\Exception\ModuleException;
use Codeception\Lib\Driver\Db as DbDriver;
use Codeception\Module;
use Codeception\Module\Db;
use Codeception\Util\Fixtures;
use Userstory\ComponentHelpers\helpers\ArrayHelper;
use UserstoryTemp\NewsNegativeTest\entities\news\ActiveRecord;
use yii;

/**
 * Хелпер для создания предусловий в тестах API сущности "Новости".
 */
class NewsDatabaseHelper extends Module
{
    public const NEWS_DATA_KEY = 'News_';

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
     * Возвращает название таблицы сущности "Новости".
     *
     * @return string
     */
    protected function getNewsTableName(): string
    {
        return Yii::$app->db->schema->getRawTableName(ActiveRecord::tableName());
    }

    /**
     * Добавляет запись в таблицу сущности "Новости".
     *
     * @param array $params Значение полей записи, которую нужно добавить.
     *
     * @throws ModuleException Если модуль DB не зарегистрирован в конфигурации codeception.
     *
     * @return int
     */
    public function addNewsEntity(array $params): int
    {
        $newsData       = [
            'newsTypeId'    => $params['newsTypeId'],
            'isActive'      => ArrayHelper::getValue($params, 'isActive', false),
            'publicDate'    => ArrayHelper::getValue($params, 'publicDate', date('Y-m-d')),
            'publicTime'    => ArrayHelper::getValue($params, 'publicTime', date('H:i:s')),
            'closeDateTime' => ArrayHelper::getValue($params, 'closeDateTime', date('Y-m-d H:i:s')),
            'isMain'        => ArrayHelper::getValue($params, 'isMain', false),
            'title'         => ArrayHelper::getValue($params, 'title', sqs('title')),
            'srcUrl'        => ArrayHelper::getValue($params, 'srcUrl', sqs('srcUrl')),
            'summary'       => ArrayHelper::getValue($params, 'summary', sqs('summary')),
            'body'          => ArrayHelper::getValue($params, 'body', sqs('body')),
        ];
        $newsData['id'] = $this->getModuleDb()->haveInDatabase($this->getNewsTableName(), $newsData);
        Fixtures::add(self::NEWS_DATA_KEY . $newsData['id'], $newsData);
        return $newsData['id'];
    }

    /**
     * Обновляет запись в таблицы сущности "Новости".
     *
     * @param int   $id     Идентификатор обновляемой записи.
     * @param array $params Значение полей записи, которую нужно добавить.
     *
     * @throws ModuleException Если модуль DB не зарегистрирован в конфигурации codeception.
     *
     * @return void
     */
    public function updateNewsEntityById(int $id, array $params): void
    {
        $this->getModuleDb()->updateInDatabase($this->getNewsTableName(), $params, [
            'id' => $id,
        ]);
    }

    /**
     * Метод для удаления записи из таблицы сущности "Новости" по id.
     *
     * @param int $id идентификатор, которому должна удолвлетворять удаляемая запись.
     *
     * @throws ModuleException
     */
    public function deleteOneFromNewsTableById(int $id): void
    {
        $this->getDbDriver()->deleteQueryByCriteria($this->getNewsTableName(), ['id' => $id]);
    }

    /**
     * Метод для удаление записей из таблицы сущности "Новости".
     *
     * @param array $criteria Критерии, которым должны удолвлетворять удаляемые записи.
     *
     * @throws ModuleException
     */
    public function deleteManyFromNewsTable(array $criteria): void
    {
        $this->getDbDriver()->deleteQueryByCriteria($this->getNewsTableName(), $criteria);
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
    public function grabOneFromNewsTable(array $criteria): ?array
    {
        $data = $this->grabManyFromNewsTable($criteria);
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
    public function grabOneFromNewsTableById(int $id): ?array
    {
        $data = $this->grabManyFromNewsTable(['id' => $id]);
        return array_shift($data);
    }

    /**
     * Метод возвращает данные колонки из таблицы.
     *
     * @param string $column Название колонки в таблице.
     *
     * @throws ModuleException Если модуль DB не зарегистрирован в конфигурации codeception.
     *
     * @return array
     */
    public function grabColumnFromNewsTable(string $column): array
    {
        return $this->getModuleDb()->grabColumnFromDatabase($this->getNewsTableName(), $column);
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
    public function grabManyFromNewsTable(array $criteria = []): array
    {
        $dbDriver = $this->getDbDriver();
        $query    = $dbDriver->select('*', $this->getNewsTableName(), $criteria);
        $response = $dbDriver->executeQuery($query, $criteria);
        $result   = [];
        $rowCount = $response->rowCount();
        for ($i = 0; $i < $rowCount; $i ++) {
            $result[] = $response->fetch(\PDO::FETCH_ASSOC);
        }
        return $this->format($result);
    }

    /**
     * Метод преобразует булевый тип данных.
     *
     * @param array $data Данные полученные из Бд.
     *
     * @return array
     */
    public function format(array $data): array
    {
        $count = count($data);
        for ($j = 0; $j < $count; $j ++) {
            $data[$j]['isActive'] = (bool)$data[$j]['isActive'];
            $data[$j]['isMain']   = (bool)$data[$j]['isMain'];
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
    public function getNotExistNewsId(): int
    {
        $newsIds = $this->grabColumnFromNewsTable('id');
        return DataTypesValueHelper::getRandomIntMissingInArray($newsIds);
    }

    /**
     * Проверяет наличие записи в таблице сущности "Новости".
     *
     * @param array $criteria Критерии, которым должна удолвлетворять искомая запись.
     *
     * @throws ModuleException Если модуль DB не зарегистрирован в конфигурации codeception.
     *
     * @return void
     */
    public function seeInNewsTable(array $criteria): void
    {
        $this->getModuleDb()->seeInDatabase($this->getNewsTableName(), $criteria);
    }

    /**
     * Проверяет отсутствие записи в таблице сущности "Новости".
     *
     * @param array $criteria Критерии, которым должна удолвлетворять искомая запись.
     *
     * @throws ModuleException Если модуль DB не зарегистрирован в конфигурации codeception.
     *
     * @return void
     */
    public function dontSeeInNewsTable(array $criteria): void
    {
        $this->getModuleDb()->dontSeeInDatabase($this->getNewsTableName(), $criteria);
    }

    /**
     * Проверяет количество записей в таблице сущности "новости" по указаным критериям.
     *
     * @param array $criteria Критерии, которым должна удолвлетворять искомая запись.
     * @param int   $num      кол-во искомых записей.
     *
     * @throws ModuleException Если модуль DB не зарегистрирован в конфигурации codeception.
     *
     * @return void
     */
    public function seeNumRecordsInNewsTable($num, array $criteria): void
    {
        $this->getModuleDb()->seeNumRecords($num, $this->getNewsTableName(), $criteria);
    }
}
