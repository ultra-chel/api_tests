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
class ViewListActionCest
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
                'list' => [
                    [
                        'newsId'     => 'integer',
                        'tagId'      => 'integer',
                        'createDate' => 'string',
                        'creatorId'  => 'integer|null',
                        'updateDate' => 'string',
                        'updaterId'  => 'integer|null',
                    ],
                ],
                'more' => 'boolean',
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
     *
     * @param ApiTester $i Объект текущего тестировщика.
     *
     * @group L2
     * @group authorization
     *
     * @return void
     */
    public function listNewsTagParamWithoutAuth(ApiTester $i): void
    {
        $i->wantTo('N [NewsTagParam]: 
        Передаем: Запрос на получение списка сущностей без авторизации.
        Ожидаем: Ошибку с поянением - Доступ запрещен.');

        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI);
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
     * Метод проверяет позитивный кейс.
     *
     * @param ApiTester $i Объект текущего тестировщика.
     *
     * @group L1
     * @group functional
     *
     * @return void
     */
    public function listNewsTagParamPositive(ApiTester $i): void
    {
        $i->wantTo('P [NewsTagParam]: 
        Передаем: Запрос на получение списка сущностей без фильтров, лимитов и оффсетов.
        Ожидаем: список всех сущностей.');
        $firstNewsTagParam  = $i->createNewsTagParam();
        $secondNewsTagParam = $i->createNewsTagParam();
        $thirdNewsTagParam  = $i->createNewsTagParam(['newsId' => $firstNewsTagParam['newsId']]);

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI);
        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => [
                    $firstNewsTagParam,
                    $secondNewsTagParam,
                    $thirdNewsTagParam,
                ],
                'more' => false,
            ],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param ApiTester $i Объект текущего тестировщика.
     *
     * @group L1
     * @group functional
     *
     * @return void
     */
    public function listNewsTagParamFilterNewsIdCorrect(ApiTester $i): void
    {
        $i->wantTo('P [NewsTagParam] {Filter: newsId}: 
        Передаем: корректное значение.
        Ожидаем: список отфильтрованных сущностей.');
        $firstNewsTagParam = $i->createNewsTagParam();
        $i->createNewsTagParam();
        $thirdNewsTagParam = $i->createNewsTagParam(['newsId' => $firstNewsTagParam['newsId']]);

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'newsId' => $firstNewsTagParam['newsId'],
            ],
        ]);
        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => [
                    $firstNewsTagParam,
                    $thirdNewsTagParam,
                ],
                'more' => false,
            ],
        ]);
        $i->dontSeeResponseJsonMatchesJsonPath('$.data.list[2]');
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param ApiTester $i Объект текущего тестировщика.
     *
     * @group L1
     * @group functional
     *
     * @return void
     */
    public function listNewsTagParamFilterTagIdCorrect(ApiTester $i): void
    {
        $i->wantTo('P [NewsTagParam] {Filter: tagId}: 
        Передаем: корректное значение.
        Ожидаем: список отфильтрованных сущностей.');
        $firstNewsTagParam = $i->createNewsTagParam();
        $i->createNewsTagParam();
        $thirdNewsTagParam = $i->createNewsTagParam(['tagId' => $firstNewsTagParam['tagId']]);

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'tagId' => $firstNewsTagParam['tagId'],
            ],
        ]);
        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => [
                    $firstNewsTagParam,
                    $thirdNewsTagParam,
                ],
                'more' => false,
            ],
        ]);
        $i->dontSeeResponseJsonMatchesJsonPath('$.data.list[2]');
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param ApiTester $i Объект текущего тестировщика.
     *
     * @group L1
     * @group functional
     *
     * @return void
     */
    public function listNewsTagParamFilterIdsCorrect(ApiTester $i): void
    {
        $i->wantTo('P [NewsTagParam] {Filter: newsId, tagId}: 
        Передаем: корректное значение.
        Ожидаем: список отфильтрованных сущностей.');
        $firstNewsTagParam = $i->createNewsTagParam();
        $i->createNewsTagParam(['newsId' => $firstNewsTagParam['newsId']]);
        $i->createNewsTagParam(['tagId' => $firstNewsTagParam['tagId']]);

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'newsId' => $firstNewsTagParam['newsId'],
                'tagId'  => $firstNewsTagParam['tagId'],
            ],
        ]);
        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => [$firstNewsTagParam],
                'more' => false,
            ],
        ]);
        $i->dontSeeResponseJsonMatchesJsonPath('$.data.list[1]');
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param ApiTester $i Объект текущего тестировщика.
     *
     * @group L1
     * @group functional
     *
     * @return void
     */
    public function listNewsTagParamOffsetCorrect(ApiTester $i): void
    {
        $i->wantTo('P [NewsTagParam] {Filter: offset}: 
        Передаем: корректное значение.
        Ожидаем: список отфильтрованных сущностей.');
        $firstNewsTagParam  = $i->createNewsTagParam();
        $secondNewsTagParam = $i->createNewsTagParam(['newsId' => $firstNewsTagParam['newsId']]);
        $thirdNewsTagParam  = $i->createNewsTagParam(['tagId' => $firstNewsTagParam['tagId']]);

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'offset' => 1,
            ],
        ]);
        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => [
                    $secondNewsTagParam,
                    $thirdNewsTagParam,
                ],
                'more' => false,
            ],
        ]);
        $i->dontSeeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => [$firstNewsTagParam],
                'more' => false,
            ],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param ApiTester $i Объект текущего тестировщика.
     *
     * @group L1
     * @group functional
     *
     * @return void
     */
    public function listNewsTagParamLimitCorrect(ApiTester $i): void
    {
        $i->wantTo('P [NewsTagParam] {Filter: limit}: 
        Передаем: корректное значение.
        Ожидаем: список отфильтрованных сущностей.');
        $firstNewsTagParam  = $i->createNewsTagParam();
        $secondNewsTagParam = $i->createNewsTagParam(['newsId' => $firstNewsTagParam['newsId']]);
        $thirdNewsTagParam  = $i->createNewsTagParam(['tagId' => $firstNewsTagParam['tagId']]);

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'limit' => 1,
            ],
        ]);
        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => [$firstNewsTagParam],
                'more' => true,
            ],
        ]);
        $i->dontSeeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => [
                    $secondNewsTagParam,
                    $thirdNewsTagParam,
                ],
                'more' => false,
            ],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
        $i->dontSeeResponseJsonMatchesJsonPath('$.data.list[1]');
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param ApiTester $i Объект текущего тестировщика.
     *
     * @group L1
     * @group functional
     *
     * @return void
     */
    public function listNewsTagParamLimitOffsetCorrect(ApiTester $i): void
    {
        $i->wantTo('P [NewsTagParam] {Filter: offset, limit}: 
        Передаем: корректное значение.
        Ожидаем: список отфильтрованных сущностей.');
        $firstNewsTagParam  = $i->createNewsTagParam();
        $secondNewsTagParam = $i->createNewsTagParam();
        $thirdNewsTagParam  = $i->createNewsTagParam();
        $fourthNewsTagParam = $i->createNewsTagParam();

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'offset' => 2,
                'limit'  => 1,
            ],
        ]);
        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => [$thirdNewsTagParam],
                'more' => true,
            ],
        ]);
        $i->dontSeeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => [
                    $secondNewsTagParam,
                    $firstNewsTagParam,
                    $fourthNewsTagParam,
                ],
                'more' => false,
            ],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
        $i->dontSeeResponseJsonMatchesJsonPath('$.data.list[1]');
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param ApiTester $i Объект текущего тестировщика.
     *
     * @group L1
     * @group functional
     *
     * @return void
     */
    public function listNewsTagParamLimitOffsetFilterCorrect(ApiTester $i): void
    {
        $i->wantTo('P [NewsTagParam] {Filter: offset, limit, newsId}: 
        Передаем: корректное значение.
        Ожидаем: список отфильтрованных сущностей.');
        $firstNewsTagParam  = $i->createNewsTagParam();
        $secondNewsTagParam = $i->createNewsTagParam();
        $thirdNewsTagParam  = $i->createNewsTagParam(['newsId' => $firstNewsTagParam['newsId']]);
        $fourthNewsTagParam = $i->createNewsTagParam(['newsId' => $firstNewsTagParam['newsId']]);

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'offset' => 1,
                'limit'  => 1,
                'newsId' => $firstNewsTagParam['newsId'],
            ],
        ]);
        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => [$thirdNewsTagParam],
                'more' => true,
            ],
        ]);
        $i->dontSeeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => [
                    $secondNewsTagParam,
                    $firstNewsTagParam,
                    $fourthNewsTagParam,
                ],
                'more' => false,
            ],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
        $i->dontSeeResponseJsonMatchesJsonPath('$.data.list[1]');
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param ApiTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     * @throws Exception
     */
    public function listNewsTagParamFilterIdsNotExist(ApiTester $i): void
    {
        $i->wantTo('N [NewsTagParam] {Filter: newsId, tagId}: 
        Передаем: несуществующий в таблице новостей идентификатор.
        Ожидаем: ошибки с пояснением, что значения «Id» неверны.');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'newsId' => $i->getNotExistNewsId(),
                'tagId'  => $i->getNotExistTagId(),
            ],
        ]);
        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'newsId' => 'Значение «News Id» неверно.',
                    ],
                ],
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'tagId' => 'Значение «Tag Id» неверно.',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param ApiTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function listNewsTagParamFilterIdsOverMaxPhysicInt(ApiTester $i): void
    {
        $i->wantTo('N [NewsTagParam] {Filter: newsId, tagId}: 
        Передаем: превышение максимального значения типа int.
        Ожидаем: ошибку с пояснением - Значение «Id» не должно превышать 2147483647.');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'newsId' => DataTypesValueHelper::OVER_MAX_INT_SIGNED,
                'tagId'  => DataTypesValueHelper::OVER_MAX_INT_SIGNED,
            ],
        ]);
        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'newsId' => 'Значение «News Id» не должно превышать 2147483647.',
                    ],
                ],
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'tagId' => 'Значение «Tag Id» не должно превышать 2147483647.',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param ApiTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function listNewsTagParamFilterIdsLessMinLogicInt(ApiTester $i): void
    {
        $i->wantTo('N [NewsTagParam] {Filter: newsId, tagId}: 
        Передаем: меньше минимального значения типа int.
        Ожидаем: ошибку с пояснением - Значение «Id» неверно.');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'newsId' => - 1,
                'tagId'  => 0,
            ],
        ]);
        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'newsId' => 'Значение «News Id» неверно.',
                    ],
                ],
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'tagId' => 'Значение «Tag Id» неверно.',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param ApiTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function listNewsTagParamFilterIdsToArray(ApiTester $i): void
    {
        $i->wantTo('N [NewsTagParam] {Filter: newsId, tagId}: 
        Передаем: значение типа array.
        Ожидаем: ошибку с пояснением - Значение «Id» должно быть целым числом.');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'newsId' => ['array'],
                'tagId'  => ['array'],
            ],
        ]);
        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'newsId' => 'Значение «News Id» должно быть целым числом.',
                    ],
                ],
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'tagId' => 'Значение «Tag Id» должно быть целым числом.',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param ApiTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function listNewsTagParamFilterIdsToString(ApiTester $i): void
    {
        $i->wantTo('N [NewsTagParam] {Filter: newsId, tagId}: 
        Передаем: значение типа string.
        Ожидаем: ошибку с пояснением - Значение «Id» должно быть целым числом.');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'newsId' => 'text',
                'tagId'  => 'text',
            ],
        ]);
        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'newsId' => 'Значение «News Id» должно быть целым числом.',
                    ],
                ],
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'tagId' => 'Значение «Tag Id» должно быть целым числом.',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param ApiTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function listNewsTagParamFilterIdsToSqlQuery(ApiTester $i): void
    {
        $i->wantTo('N [NewsTagParam] {Filter: newsId}: 
        Передаем: корректный sql запрос в строке.
        Ожидаем: ошибку с пояснением - Значение «News Id» должно быть целым числом.');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'newsId' => DataTypesValueHelper::SQL_QUERY,
                'tagId'  => DataTypesValueHelper::SQL_QUERY,
            ],
        ]);
        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'newsId' => 'Значение «News Id» должно быть целым числом.',
                    ],
                ],
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'tagId' => 'Значение «Tag Id» должно быть целым числом.',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }
}
