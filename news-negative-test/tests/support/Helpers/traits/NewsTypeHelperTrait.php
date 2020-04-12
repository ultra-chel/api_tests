<?php

namespace UserstoryTemp\NewsNegativeTest\tests\support\Helpers\traits;

use Codeception\Util\Fixtures;
use UserstoryTemp\NewsNegativeTest\tests\support\Helpers\NewsTypeDatabaseHelper;

/**
 * Трейт для создания типа новости в системе.
 *
 * @package UserstoryTemp\NewsNegativeTest\Tests\Support\Helpers\Traits
 */
trait NewsTypeHelperTrait
{
    /**
     * Метод добавления сущности из хэлпера.
     *
     * @param array $params
     *
     * @return mixed
     */
    abstract public function addNewsTypeEntity(array $params);

    /**
     * Метод создаёт тип новости в системе.
     *
     * @param array $params
     *
     * @return array
     */
    public function createNewsType(array $params = []): array
    {
        return Fixtures::get(NewsTypeDatabaseHelper::NEWS_TYPE_DATA_KEY . $this->addNewsTypeEntity($params));
    }

    /**
     * Добавляет список записей в таблицу сущности "Тип новостей".
     *
     * @param int   $count  Значение полей записи, которую нужно добавить.
     * @param array $params Параметры для сущностей, которые нужно добавить.
     *
     * @throws
     *
     * @return array
     */
    public function createNewsTypeList(int $count, array $params = []): array
    {
        $entitiesData = [];
        for ($i = 1; $i <= $count; $i ++) {
            $entitiesData[] = $this->createNewsType($params);
        }
        return $entitiesData;
    }
}
