<?php

declare( strict_types = 1 );

namespace UserstoryTemp\NewsNegativeTestRest\tests\api\v1\comment;

use Codeception\Util\HttpCode;
use Exception;
use UserstoryTemp\NewsNegativeTest\tests\support\Helpers\DataTypesValueHelper;
use UserstoryTemp\NewsNegativeTestRest\tests\support\Step\Api\CommentTester;

/**
 * Класс тестирования REST API: удаление сущности "Комментарии к новостям".
 */
class DeleteOneActionCest
{
    /**
     * Заголовки, указывающие на метод.
     *
     * @var array
     */
    public static $methodHeader = [
        'name'  => 'X-HTTP-Method-Override',
        'value' => 'DELETE',
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
                'success' => 'boolean',
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
    public function deleteCommentWithOutAuth(CommentTester $i): void
    {
        $i->wantTo('N [Comment] 
        Передаем: удаление сущности без авторизации. 
        Ожидаем: ошибку с пояснением - Доступ запрещен');
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
        $i->seeInCommentTable($commentData);
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
    public function deleteCommentCorrect(CommentTester $i): void
    {
        $i->wantTo('P [Comment] 
        Передаем: корректный id. 
        Ожидаем: успешное удаление.');
        $commentData = $i->createComment();

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $commentData['id']);

        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'success' => true,
            ],
        ]);
        $i->dontSeeInCommentTable($commentData);
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
     *
     * @throws Exception
     */
    public function deleteCommentNotExist(CommentTester $i): void
    {
        $i->wantTo('N [Comment] 
        Передаем: несуществующей в таблице идентификатор комментария.
        Ожидаем: Ошибку с пояснением - Сущность не найдена');
        $commentIds          = $i->grabColumnFromCommentTable('id');
        $notExistId          = $i->getNotExistCommentId();
        $commentsCountBefore = count($commentIds);

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $notExistId);
        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'success' => true,
            ],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
        $i->seeNumRecordsInCommentTable($commentsCountBefore);
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
    public function deleteCommentIdLessMinLogicInt(CommentTester $i): void
    {
        $i->wantTo('N [Comment] тип int(11)
        Передаем: значение id меньше логического минимума типа данных. 
        Ожидаем: Ошибку с пояснением - Значение «Id» должно быть не меньше 1.');

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
    public function deleteCommentIdOverMaxPhysicInt(CommentTester $i): void
    {
        $i->wantTo('N [Comment] тип int(11)
        Передаем: значение id больше физического максимума типа данных. 
        Ожидаем: ошибку с пояснением - Значение «Id» не должно превышать 2147483647.');

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
