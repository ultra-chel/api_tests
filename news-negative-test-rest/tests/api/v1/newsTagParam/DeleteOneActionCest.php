<?php

declare( strict_types = 1 );

namespace UserstoryTemp\NewsNegativeTestRest\tests\api\v1\newsTagParam;

use Codeception\Util\HttpCode;
use Exception;
use UserstoryTemp\NewsNegativeTest\tests\support\Helpers\DataTypesValueHelper;
use UserstoryTemp\NewsNegativeTestRest\tests\support\ApiTester;

/**
 * Класс тестирования REST API: удаление сущности "Связь новостей и тэгов".
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
    public static $URI = 'v1/news-tag-param/';

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
     * @param ApiTester $i Объект текущего тестировщика.
     *
     * @return void
     */
    public function _after(ApiTester $i): void
    {
    }

    /**
     * Метод для предварительных инициализаций перед тестами.
     *
     * @param ApiTester $i Объект текущего тестировщика.
     *
     * @return void
     */
    public function _before(ApiTester $i): void
    {
    }

    /**
     * Метод проверяет запрос удаления типа новости.
     *
     * @param ApiTester $i Объект текущего тестировщика.
     *
     * @group L2
     * @group authorization
     *
     * @return void
     */
    public function deleteNewsTagParamWithOutAuth(ApiTester $i): void
    {
        $i->wantTo('N [NewsTagParam] 
        Запрос: удаление сущности без авторизации. 
        Ожидаем: ошибку с пояснением - Доступ запрещен.');
        $paramsData = $i->createNewsTagParam();

        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $paramsData);
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
        $i->seeInNewsTagParamTable($paramsData);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет запрос удаления типа новости.
     *
     * @param ApiTester $i Объект текущего тестировщика.
     *
     * @group L1
     * @group functional
     *
     * @skip
     *
     * @return void
     */
    public function deleteNewsTagParamCorrect(ApiTester $i): void
    {
        $i->wantTo('P [NewsTagParam] 
        Передаем: корректное значение идентификатора. 
        Ожидаем: успешное удаление.');
        $paramsData = $i->createNewsTagParam();

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $paramsData);
        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'success' => true,
            ],
        ]);
        $i->dontSeeInNewsTable($paramsData);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет запрос удаления типа новости.
     *
     * @param ApiTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function deleteNewsTagParamIdsToString(ApiTester $i): void
    {
        $i->wantTo('P [NewsTagParam] 
        Передаем: Строковые значения в идентификаторах.
        Ожидаем: Ошибка удаления с указанием, что идентификаторы должны быть целым числом.');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'newsId' => 'string',
            'tagId'  => 'string',
        ]);
        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => ['newsId' => 'Значение «News Id» должно быть целым числом.'],
                ],
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => ['tagId' => 'Значение «Tag Id» должно быть целым числом.'],
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
     * @param ApiTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function deleteNewsTagParamIdsToArray(ApiTester $i): void
    {
        $i->wantTo('P [NewsTagParam] 
        Передаем: Массивы в идентификаторах. 
        Ожидаем: Ошибка удаления с указанием, что идентификаторы должны быть целым числом.');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'newsId' => ['array'],
            'tagId'  => ['array'],
        ]);
        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => ['newsId' => 'Значение «News Id» должно быть целым числом.'],
                ],
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => ['tagId' => 'Значение «Tag Id» должно быть целым числом.'],
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
     * @param ApiTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function deleteNewsTagParamIdsToSQLQuery(ApiTester $i): void
    {
        $i->wantTo('P [NewsTagParam] 
        Передаем: SQL запросы в идентификаторах.
        Ожидаем: Ошибка удаления с указанием, что идентификаторы должны быть целым числом.');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'newsId' => DataTypesValueHelper::SQL_QUERY,
            'tagId'  => DataTypesValueHelper::SQL_QUERY,
        ]);
        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => ['newsId' => 'Значение «News Id» должно быть целым числом.'],
                ],
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => ['tagId' => 'Значение «Tag Id» должно быть целым числом.'],
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
     * @param ApiTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     * @throws Exception
     */
    public function deleteNewsTagParamIdsToNotExistedId(ApiTester $i): void
    {
        $i->wantTo('P [NewsTagParam] 
        Передаем: Не существующие в системе идентификаторы.
        Ожидаем: Ошибка удаления с указанием, что идентификаторы ошибочны.');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'newsId' => $i->getNotExistNewsId(),
            'tagId'  => $i->getNotExistTagId(),
        ]);
        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => ['newsId' => 'Значение «News Id» неверно.'],
                ],
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => ['tagId' => 'Значение «Tag Id» неверно.'],
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
     * @param ApiTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     * @throws Exception
     */
    public function deleteNewsTagParamIdsToOverMaxPhysicInt(ApiTester $i): void
    {
        $i->wantTo('P [NewsTagParam] 
        Передаем: Превышающее физический лимит integer целочисленое значение.
        Ожидаем: Ошибка удаления с указанием, что идентификаторы ошибочны.');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'newsId' => DataTypesValueHelper::OVER_MAX_INT_SIGNED,
            'tagId'  => DataTypesValueHelper::OVER_MAX_INT_SIGNED,
        ]);
        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => ['newsId' => 'Значение «News Id» не должно превышать 2147483647.'],
                ],
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => ['tagId' => 'Значение «Tag Id» не должно превышать 2147483647.'],
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
     * @param ApiTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     * @throws Exception
     */
    public function deleteNewsTagParamIdsToOverMinLogicInt(ApiTester $i): void
    {
        $i->wantTo('P [NewsTagParam] 
        Передаем: Меньше логического лимита integer целочисленое значение.
        Ожидаем: Ошибка удаления с указанием, что идентификаторы ошибочны.');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'newsId' => 0,
            'tagId'  => 0,
        ]);
        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => ['newsId' => 'Значение «News Id» неверно.'],
                ],
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => ['tagId' => 'Значение «Tag Id» неверно.'],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }
}
