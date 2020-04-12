<?php

namespace UserstoryTemp\NewsNegativeTest\tests\support\Helpers\Traits;

use Codeception\Util\Fixtures;
use UserstoryTemp\NewsNegativeTest\tests\support\Helpers\TagDatabaseHelper;

/**
 * Трейт для создания тэгов в системе.
 */
trait TagsHelperTrait
{
    /**
     * Метод добавления сущности из хэлпера.
     *
     * @param array $params
     *
     * @return mixed
     */
    abstract public function addTagEntity(array $params);

    /**
     * Метод создаёт комментарий в системе.
     *
     * @param array $params
     *
     * @return array
     */
    public function createTag(array $params = []): array
    {
        return Fixtures::get(TagDatabaseHelper::TAG_DATA_KEY . $this->addTagEntity($params));
    }

    /**
     * Добавляет список записей в таблицу сущности "Тэги".
     *
     * @param int   $count  количество комментариев.
     * @param array $params параметры создаваемых сущностей.
     *
     * @throws
     *
     * @return array
     */
    public function createTagsList(int $count, array $params = []): array
    {
        $dataNews = [];
        for ($i = 1; $i <= $count; $i ++) {
            $dataNews[] = $this->createTag($params);
        }
        return $dataNews;
    }
}
