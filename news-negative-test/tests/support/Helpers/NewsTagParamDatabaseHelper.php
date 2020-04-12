<?php

declare( strict_types = 1 );

namespace UserstoryTemp\NewsNegativeTest\tests\support\Helpers;

use Codeception\Exception\ModuleException;
use Codeception\Module;
use Codeception\Module\Db;
use Codeception\Util\Fixtures;
use Exception;
use UserstoryTemp\NewsNegativeTest\entities\newsTagParam\ActiveRecord;
use yii;

/**
 * Хелпер для создания предусловий в тестах API сущности "Связь новостей и тэгов".
 */
class NewsTagParamDatabaseHelper extends Module
{
    const NEWS_TAG_PARAM_KEY = 'NewsTagParam_';

    /**
     * Добавляет запись в таблицы сущности "Связь новостей и тэгов".
     *
     * @param array $params Значение полей записи, которую нужно добавить.
     *
     * @throws ModuleException Если модуль DB не зарегистрирован в конфигурации codeception.
     *
     * @return int
     */
    public function addNewsTagParamEntity(array $params): int
    {
        $paramData       = [
            'newsId' => $params['newsId'],
            'tagId'  => $params['tagId'],
        ];
        $paramData['id'] = $this->getModuleDb()->haveInDatabase($this->getNewsTagParamTableName(), $paramData);
        Fixtures::add(self::NEWS_TAG_PARAM_KEY . $paramData['id'], $paramData);
        return $paramData['id'];
    }

    /**
     * Проверяет отсутствие записи в таблице сущности "Связь новостей и тэгов".
     *
     * @param array $criteria Критерии, которым должна удолвлетворять искомая запись.
     *
     * @throws ModuleException Если модуль DB не зарегистрирован в конфигурации codeception.
     *
     * @return void
     */
    public function dontSeeInNewsTagParamTable(array $criteria): void
    {
        $this->getModuleDb()->dontSeeInDatabase($this->getNewsTagParamTableName(), $criteria);
    }

    /**
     * Метод возвращает значение id не существующщее в таблице сущности 'Типы новостей'.
     *
     * @throws ModuleException Если модуль DB не зарегистрирован в конфигурации codeception.
     * @throws Exception
     *
     * @return int
     */
    public function getNotExistNewsTagParamId(): int
    {
        $newsTagParamIds = $this->grabColumnFromNewsTagParamTable('id');
        return DataTypesValueHelper::getRandomIntMissingInArray($newsTagParamIds);
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
    public function grabColumnFromNewsTagParamTable(string $column): array
    {
        return $this->getModuleDb()->grabColumnFromDatabase($this->getNewsTagParamTableName(), $column);
    }

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
     * Возвращает название таблицы сущности "Связь новостей и тэгов".
     *
     * @return string
     */
    protected function getNewsTagParamTableName(): string
    {
        return Yii::$app->db->schema->getRawTableName(ActiveRecord::tableName());
    }

    /**
     * Возвращает значение поля из таблицы сущности "Связь новостей и тэгов".
     *
     * @param string $column   Название столца значение которого нужно вернуть.
     * @param array  $criteria Критерии, которым должна удолвлетворять искомая запись.
     *
     * @throws ModuleException Если модуль DB не зарегистрирован в конфигурации codeception.
     *
     * @notyping
     *
     * @return mixed
     */
    public function grabFromNewsTagParamTable(string $column, array $criteria)
    {
        return $this->getModuleDb()->grabFromDatabase($this->getNewsTagParamTableName(), $column, $criteria);
    }

    /**
     * Проверяет наличие записи в таблице сущности "Связь новостей и тэгов".
     *
     * @param array $criteria Критерии, которым должна удолвлетворять искомая запись.
     *
     * @throws ModuleException Если модуль DB не зарегистрирован в конфигурации codeception.
     *
     * @return void
     */
    public function seeInNewsTagParamTable(array $criteria): void
    {
        $this->getModuleDb()->seeInDatabase($this->getNewsTagParamTableName(), $criteria);
    }

    /**
     * Обновляет запись в таблицы сущности "Связь новостей и тэгов".
     *
     * @param int   $id     Идентификатор обновляемой записи.
     * @param array $params Значение полей записи, которую нужно добавить.
     *
     * @throws ModuleException Если модуль DB не зарегистрирован в конфигурации codeception.
     *
     * @return void
     */
    public function updateNewsTagParamEntityById(int $id, array $params): void
    {
        $this->getModuleDb()->updateInDatabase($this->getNewsTagParamTableName(), $params, [
            '' => $id,
        ]);
    }
}
