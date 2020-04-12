<?php

namespace UserstoryTemp\NewsNegativeTest\tests\support\Helpers\Traits;

use Codeception\Util\Fixtures;
use UserstoryTemp\NewsNegativeTest\tests\support\Helpers\CommentDatabaseHelper;

/**
 * Трейт для создания комметария в системе.
 *
 * @package UserstoryTemp\NewsNegativeTest\Tests\Support\Helpers\Traits
 */
trait CommentHelperTrait
{
    /**
     * Метод добавления сущности из хэлпера.
     *
     * @param array $params
     *
     * @return mixed
     */
    abstract public function addCommentEntity(array $params);

    /**
     * Метод для создания зависимой сущности в системе.
     *
     * @param $params
     *
     * @return array
     */
    abstract public function createNews(array $params = []): array;

    /**
     * Метод создаёт предусловия для комментария в системе.
     *
     * @param array $params
     *
     * @return array
     */
    public function preconditionForComment(): array
    {
        $newsData = $this->createNews();
        return ['newsData' => $newsData];
    }

    /**
     * Метод создаёт комментарий в системе.
     *
     * @param array $params
     *
     * @return array
     */
    public function createComment(array $params = []): array
    {
        if (! array_key_exists('newsId', $params)) {
            $params['newsId'] = $this->createNews()['id'];
        }
        return Fixtures::get(CommentDatabaseHelper::COMMENT_DATA_KEY . $this->addCommentEntity($params));
    }
}
