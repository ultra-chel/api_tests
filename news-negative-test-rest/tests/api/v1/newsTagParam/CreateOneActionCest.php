<?php

declare( strict_types = 1 );

namespace UserstoryTemp\NewsNegativeTestRest\tests\api\v1\newsTagParam;

use Codeception\Util\HttpCode;
use Exception;
use UserstoryTemp\NewsNegativeTest\tests\support\Helpers\DataTypesValueHelper;
use UserstoryTemp\NewsNegativeTestRest\tests\support\ApiTester;

/**
 * Класс тестирования REST API: создание новой сущности "Связь новостей и тэгов".
 */
class CreateOneActionCest
{
    /**
     * Заголовки, указывающие на метод.
     *
     * @var array
     */
    public static $methodHeader = [
        'name'  => 'X-HTTP-Method-Override',
        'value' => 'CREATE',
    ];

    /**
     * Путь до тестируемого экшена.
     *
     * @var string
     */
    public static $URI = 'v1/news-tag-param';

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
                'newsId'     => 'integer',
                'tagId'      => 'integer',
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
        $i->flushCache();
    }

    /**
     * Метод проверяет позитивный кейс.
     * Передаем: Запрос на создание сущности со сгенерированными случайными данными.
     * Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.
     *
     * @param ApiTester $i Объект текущего тестировщика.
     *
     * @group L2
     * @group authorization
     *
     * @return void
     */
    public function createParamWithoutAuth(ApiTester $i): void
    {
        $i->wantTo('P [NewsTagParam]: 
        Передаем: Запрос на создание сущности без авторизации. 
        Ожидаем: ошибку с пояснением - Доступ запрещен.');

        $newsId = $i->createNews()['id'];
        $tagId  = $i->createTag()['id'];

        $request = [
            'newsId' => $newsId,
            'tagId'  => $tagId,
        ];
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $request);
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
        $i->dontSeeInNewsTagParamTable($request);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     * Передаем: Запрос на создание сущности со сгенерированными случайными данными.
     * Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.
     *
     * @param ApiTester $i Объект текущего тестировщика.
     *
     * @group L1
     * @group functional
     *
     * @return void
     */
    public function createNewsTagParamCorrect(ApiTester $i): void
    {
        $i->wantTo('P [NewsTagParam]: 
        Передаем: Запрос на создание сущности со сгенерированными случайными данными.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $preconditionForNTParam = $i->preconditionForNewsTagParam();
        $newsId                 = $preconditionForNTParam['newsData']['id'];
        $tagId                  = $preconditionForNTParam['tagData']['id'];
        $request                = [
            'newsId' => $newsId,
            'tagId'  => $tagId,
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $request);
        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeInNewsTagParamTable($request);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'newsId' => $newsId,
                'tagId'  => $tagId,
            ],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     * Передаем: Запрос на создание сущности со сгенерированными случайными данными с кириллицей.
     * Ожидаем: Ошибка, указывающая что идентификаторы должны быть числом.
     *
     * @param ApiTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function createNewsTagParamIdsToString(ApiTester $i): void
    {
        $i->wantTo('P [NewsTagParam]: 
        Передаем: Запрос на создание сущности со строковыми значениями в идентификаторах.
        Ожидаем: Ошибка, указывающая что идентификаторы должны быть числом.');
        $request = [
            'newsId' => 'string',
            'tagId'  => 'string',
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $request);

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
     * Передаем: Запрос на создание сущности со строковыми значениями в идентификаторах.
     * Ожидаем: Ошибка, указывающая что идентификаторы должны быть числом.
     *
     * @param ApiTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function createNewsTagParamIdsToArray(ApiTester $i): void
    {
        $i->wantTo('P [NewsTagParam]: 
        Передаем: Запрос на создание сущности с массивами в значениях в идентификаторах.
        Ожидаем: Ошибка, указывающая что идентификаторы должны быть числом.');
        $request = [
            'newsId' => ['array'],
            'tagId'  => ['array'],
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $request);

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
     * Передаем: Запрос на создание сущности со сгенерированными случайными данными с символами.
     * Ожидаем: Ошибка, указывающая что идентификаторы должны быть числом.
     *
     * @param ApiTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function createNewsTagParamIdsToSQLQuery(ApiTester $i): void
    {
        $i->wantTo('P [NewsTagParam]: 
        Передаем: Запрос на создание сущности с запросом в базу в идентификаторах.
        Ожидаем: Ошибка, указывающая что идентификаторы должны быть числом.');
        $request = [
            'newsId' => DataTypesValueHelper::SQL_QUERY,
            'tagId'  => DataTypesValueHelper::SQL_QUERY,
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $request);
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
     * Передаем: Запрос на создание сущности со сгенерированными случайными данными с символами.
     * Ожидаем: Ошибка, указывающая что идентификаторы должны быть числом.
     *
     * @param ApiTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     * @throws Exception
     */
    public function createNewsTagParamIdsToNotExistedId(ApiTester $i): void
    {
        $i->wantTo('P [NewsTagParam]: 
        Передаем: Запрос на создание сущности с несуществующими в системе идентификаторами.
        Ожидаем: Ошибка, указывающая что идентификаторы должны быть числом.');
        $request = [
            'newsId' => $i->getNotExistNewsId(),
            'tagId'  => $i->getNotExistTagId(),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $request);

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
     * Передаем: Запрос на создание сущности со сгенерированными случайными данными с символами.
     * Ожидаем: Ошибка, указывающая что идентификаторы должны быть числом.
     *
     * @param ApiTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     * @throws Exception
     */
    public function createNewsTagParamIdsToOverMaxPhysicInt(ApiTester $i): void
    {
        $i->wantTo('P [NewsTagParam]: 
        Передаем: Превышающее физический лимит integer целочисленое значение.
        Ожидаем: Ошибка, указывающая что идентификаторы должны быть числом.');
        $request = [
            'newsId' => DataTypesValueHelper::OVER_MAX_INT_SIGNED,
            'tagId'  => DataTypesValueHelper::OVER_MAX_INT_SIGNED,
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $request);

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
     * Передаем: Запрос на создание сущности со сгенерированными случайными данными с символами.
     * Ожидаем: Ошибка, указывающая что идентификаторы должны быть числом.
     *
     * @param ApiTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     * @throws Exception
     */
    public function createNewsTagParamIdsToOverMinLogicInt(ApiTester $i): void
    {
        $i->wantTo('P [NewsTagParam]: 
        Передаем: Меньше логического лимита integer целочисленое значение.
        Ожидаем: Ошибка, указывающая что идентификаторы должны быть числом.');
        $request = [
            'newsId' => 0,
            'tagId'  => 0,
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $request);

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
