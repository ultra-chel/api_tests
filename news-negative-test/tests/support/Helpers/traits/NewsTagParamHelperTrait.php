<?php

namespace UserstoryTemp\NewsNegativeTest\tests\support\Helpers\traits;

use Codeception\Util\Fixtures;
use UserstoryTemp\NewsNegativeTest\tests\support\Helpers\NewsTagParamDatabaseHelper;

/**
 * Трейт для создания новости в системе.
 *
 * @package UserstoryTemp\NewsNegativeTest\Tests\Support\Helpers\Traits
 */
trait NewsTagParamHelperTrait
{
    /**
     * Метод добавления сущности из хэлпера.
     *
     * @param array $params
     *
     * @return mixed
     */
    abstract public function addNewsTagParamEntity(array $params);

    /**
     * Метод для создания зависимой сущности в системе.
     *
     * @param $params
     *
     * @return array
     */
    abstract public function createNews(array $params = []): array;

    /**
     * Метод для создания зависимой сущности в системе.
     *
     * @param $params
     *
     * @return array
     */
    abstract public function createTag(array $params = []): array;

    /**
     * Метод создаёт предусловия для новости в системе.
     *
     * @param array $params
     *
     * @return array
     */
    public function preconditionForNewsTagParam(): array
    {
        $newsData = $this->createNews();
        $tagData  = $this->createTag();
        return [
            'newsData' => $newsData,
            'tagData'  => $tagData,
        ];
    }

    /**
     * Метод создаёт новость в системе.
     *
     * @param array $params
     *
     * @return array
     */
    public function createNewsTagParam(array $params = []): array
    {
        if (! array_key_exists('newsId', $params)) {
            $params['newsId'] = $this->createNews()['id'];
        }
        if (! array_key_exists('tagId', $params)) {
            $params['tagId'] = $this->createTag()['id'];
        }
        return Fixtures::get(NewsTagParamDatabaseHelper::NEWS_TAG_PARAM_KEY . $this->addNewsTagParamEntity($params));
    }
}
