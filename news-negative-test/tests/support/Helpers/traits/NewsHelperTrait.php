<?php

namespace UserstoryTemp\NewsNegativeTest\tests\support\Helpers\traits;

use Codeception\Util\Fixtures;
use UserstoryTemp\NewsNegativeTest\tests\support\Helpers\NewsDatabaseHelper;

/**
 * Трейт для создания новости в системе.
 *
 * @package UserstoryTemp\NewsNegativeTest\Tests\Support\Helpers\Traits
 */
trait NewsHelperTrait
{
    /**
     * Метод добавления сущности из хэлпера.
     *
     * @param array $params
     *
     * @return mixed
     */
    abstract public function addNewsEntity(array $params);

    /**
     * Метод для создания зависимой сущности в системе.
     *
     * @param $params
     *
     * @return array
     */
    abstract public function createNewsType(array $params = []): array;

    /**
     * Метод создаёт предусловия для новости в системе.
     *
     * @param array $params
     *
     * @return array
     */
    public function preconditionForNews(): array
    {
        $newsTypeData = $this->createNewsType();
        return ['newsTypeData' => $newsTypeData];
    }

    /**
     * Метод создаёт новость в системе.
     *
     * @param array $params
     *
     * @return array
     */
    public function createNews(array $params = []): array
    {
        if (! array_key_exists('newsTypeId', $params)) {
            $params['newsTypeId'] = $this->createNewsType()['id'];
        }
        return Fixtures::get(NewsDatabaseHelper::NEWS_DATA_KEY . $this->addNewsEntity($params));
    }
}
