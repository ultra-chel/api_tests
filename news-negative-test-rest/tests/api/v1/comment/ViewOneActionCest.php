<?php

declare( strict_types = 1 );

namespace UserstoryTemp\NewsNegativeTestRest\tests\api\v1\comment;

use Codeception\Util\HttpCode;
use UserstoryTemp\NewsNegativeTest\tests\support\Helpers\DataTypesValueHelper;
use UserstoryTemp\NewsNegativeTestRest\tests\support\Step\Api\CommentTester;

/**
 * Класс тестирования REST API: просмотр одной сущности "Комментарии к новостям".
 */
class ViewOneActionCest
{
    /**
     * Заголовки, указывающие на метод.
     *
     * @var array
     */
    public static $methodHeader = [
        'name'  => 'X-HTTP-Method-Override',
        'value' => 'GET',
    ];

    /**
     * Путь до тестируемого экшена.
     *
     * @var string
     */
    public static $URI = 'v1/comment/';

    /**
     * Массив с типами ключей в ответах.
     *
     * @var array
     */
    protected $responseTypes = [
        'correct' => [
            'errors'  => 'array',
            'notices' => 'array',
            'data'    => [
                'id'         => 'integer',
                'newsId'     => 'integer',
                'body'       => 'string',
                'createDate' => 'string',
                'creatorId'  => 'integer|null',
                'updateDate' => 'string',
                'updaterId'  => 'integer|null',
            ],
        ],
        'errors'  => [
            'errors'  => [
                [
                    'code'   => 'integer',
                    'title'  => 'string',
                    'detail' => 'string',
                    'data'   => 'array',
                ],
            ],
            'notices' => 'array',
            'data'    => 'array',
        ],
    ];

    /**
     * Метод для послетестовых де-инициализаций.
     *
     * @param CommentTester $i Объект текущего тестировщика.
     *
     * @return void
     */
    public function _after(CommentTester $i): void
    {
    }

    /**
     * Метод для предварительных инициализаций перед тестами.
     *
     * @param CommentTester $i Объект текущего тестировщика.
     *
     * @return void
     */
    public function _before(CommentTester $i): void
    {
        $i->flushCache();
    }

    /**
     * Метод проверяет запрос удаления типа новости.
     *
     * @param CommentTester $i Объект текущего тестировщика.
     *
     * @group L2
     * @group authorization
     *
     * @return void
     */
    public function viewOneCommentWithOutAuth(CommentTester $i): void
    {
        $i->wantTo('N [Comment] 
        Передаем: получение сущности без авторизации. 
        Ожидаем: Ошибку с пояснением - Доступ запрещен.');
        $commentData = $i->createComment();

        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $commentData['id']);
        $i->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 403,
                    'title'  => 'Доступ запрещен',
                    'detail' => '',
                    'data'   => [],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет запрос удаления типа новости.
     *
     * @param CommentTester $i Объект текущего тестировщика.
     *
     * @group L1
     * @group functional
     *
     * @return void
     */
    public function viewOneCommentCorrect(CommentTester $i): void
    {
        $i->wantTo('P [Comment] 
        Передаем: корректный id. 
        Ожидаем: массив с информацией по запрашиваемому обьекту.');
        $commentData = $i->createComment();

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $commentData['id']);
        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => $commentData,
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет запрос удаления типа новости.
     *
     * @param CommentTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function viewOneCommentNotExist(CommentTester $i): void
    {
        $i->wantTo('N [Comment] 
        Передаем: несуществующий в таблице идентификатор.
        Ожидаем: Ошибку с пояснением - Сущность не найдена.');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->getNotExistCommentId());
        $i->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 404,
                    'title'  => 'Сущность не найдена',
                    'detail' => '',
                    'data'   => [],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет запрос удаления типа новости.
     *
     * @param CommentTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function viewOneCommentIdLessMinLogicInt(CommentTester $i): void
    {
        $i->wantTo('N [Comment]: тип int(11)
        Передаем: значение id меньше логического минимума типа данных. 
        Ожидаем: ошибку с пояснением - Значение «Id» должно быть не меньше 1.');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . 0);
        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'id' => 'Значение «Id» должно быть не меньше 1.',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет запрос удаления типа новости.
     *
     * @param CommentTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function viewOneCommentIdOverMaxPhysicInt(CommentTester $i): void
    {
        $i->wantTo('N [Comment] тип int(11)
        Передаем: значение id больше физического максимума типа данных. 
        Ожидаем: Ошибку с пояснением - Значение «Id» не должно превышать 2147483647.');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . DataTypesValueHelper::OVER_MAX_INT_SIGNED);
        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'id' => 'Значение «Id» не должно превышать 2147483647.',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }
}
