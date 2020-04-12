<?php

declare( strict_types = 1 );

namespace UserstoryTemp\NewsNegativeTestRest\tests\api\v1\news;

use Codeception\Util\HttpCode;
use Exception;
use UserstoryTemp\NewsNegativeTest\tests\support\Helpers\DataTypesValueHelper;
use UserstoryTemp\NewsNegativeTestRest\tests\support\Step\Api\NewsTester;

/**
 * Класс тестирования REST API: обновление сущности "Новости".
 */
class UpdateOneActionCest
{
    /**
     * Заголовки, указывающие на метод.
     *
     * @var array
     */
    public static $methodHeader = [
        'name'  => 'X-HTTP-Method-Override',
        'value' => 'PUT',
    ];

    /**
     * Путь до тестируемого экшена.
     *
     * @var string
     */
    public static $URI = 'v1/news/';

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
                'id'            => 'integer',
                'newsTypeId'    => 'integer',
                'isActive'      => 'boolean',
                'publicDate'    => 'string|null',
                'publicTime'    => 'string|null',
                'closeDateTime' => 'string|null',
                'isMain'        => 'boolean',
                'title'         => 'string',
                'srcUrl'        => 'string|null',
                'summary'       => 'string',
                'body'          => 'string|null',
                'createDate'    => 'string',
                'creatorId'     => 'integer|null',
                'updateDate'    => 'string',
                'updaterId'     => 'integer|null',
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
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function _after(NewsTester $i): void
    {
    }

    /**
     * Метод для предварительных инициализаций перед тестами.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     *
     * @throws
     */
    public function _before(NewsTester $i): void
    {
        $i->flushCache();
    }

    /**
     * Метод проверяет запрос создания новости.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L2
     * @group authorization
     *
     * @return void
     */
    public function updateNewsWithOutAuth(NewsTester $i): void
    {
        $i->wantTo('N [News] 
        Запрос: обновление сущности без авторизации. 
        Ожидаем: ошибку с пояснением - Доступ запрещен.');
        $newsData         = $i->createNews();
        $preconditionData = $i->preconditionForNews();
        $request          = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => sqs('Test. Title'),
            'summary'    => sqs('Test. Summary.'),
        ];

        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $newsData['id'], $request);

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
        $i->dontSeeInNewsTable($request);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L1
     * @group functional
     *
     * @return void
     */
    public function updateNewsOnlyRequiredFields(NewsTester $i): void
    {
        $i->wantTo('P [News]: 
        Передаем: корректные данные во все обязательные поля.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $newsData         = $i->createNews();
        $preconditionData = $i->preconditionForNews();
        $request          = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => sqs('Test. Title'),
            'summary'    => sqs('Test. Summary.'),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $newsData['id'], $request);
        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeInNewsTable($request);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'id'         => $newsData['id'],
                'newsTypeId' => $request['newsTypeId'],
                'title'      => $request['title'],
                'summary'    => $request['summary'],
            ],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     * @throws Exception
     */
    public function updateNewsNewsTypeIdNotExist(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле newsTypeId}: 
        Передаем: несуществующее значение идентификатора в таблице типов новостей.
        Ожидаем: ошибку с пояснением - Значение «News Type Id» неверно.');
        $request = [
            'newsTypeId' => $i->getNotExistNewsTypeId(),
            'title'      => sqs('Test. Title'),
            'summary'    => sqs('Test. Summary.'),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id'], $request);
        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'newsTypeId' => 'Значение «News Type Id» неверно.',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->dontSeeInNewsTable($request);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function updateNewsNewsTypeIdOverMaxPhysicInt(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле newsTypeId}: тип int(11)
        Передаем: превышение максимального значения типа данных.
        Ожидаем: ошибку с пояснением - Значение «News Type Id» не должно превышать 2147483647.');
        $request = [
            'newsTypeId' => DataTypesValueHelper::OVER_MAX_INT_SIGNED,
            'title'      => sqs('Test. Title'),
            'summary'    => sqs('Test. Summary.'),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id'], $request);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'newsTypeId' => 'Значение «News Type Id» не должно превышать 2147483647.',
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
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function updateNewsNewsTypeIdLessMinLogicInt(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле newsTypeId}: тип int(11)
        Передаем: значение -1.
        Ожидаем: ошибку с пояснением.');
        $request = [
            'newsTypeId' => - 1,
            'title'      => sqs('Test. Title'),
            'summary'    => sqs('Test. Summary.'),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id'], $request);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'newsTypeId' => 'Значение «News Type Id» неверно.',
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
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function updateNewsNewsTypeIdToNull(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле newsTypeId}: 
        Передаем: NULL.
        Ожидаем: ошибку с пояснением - Необходимо заполнить «News Type Id».');
        $request = [
            'newsTypeId' => null,
            'title'      => sqs('Test. Title'),
            'summary'    => sqs('Test. Summary.'),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id'], $request);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'newsTypeId' => 'Необходимо заполнить «News Type Id».',
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
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function updateNewsNewsTypeIdToFloat(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле newsTypeId}: 
        Передаем: значение типа float.
        Ожидаем: ошибку с пояснением - Значение «News Type Id» должно быть целым числом.');
        $request = [
            'newsTypeId' => 4.43434,
            'title'      => sqs('Test. Title'),
            'summary'    => sqs('Test. Summary.'),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id'], $request);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'newsTypeId' => 'Значение «News Type Id» должно быть целым числом.',
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
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function updateNewsNewsTypeIdToString(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле newsTypeId}: 
        Передаем: строка с рандомным набором спецсимволов.
        Ожидаем: ошибку с пояснением - Значение «News Type Id» должно быть целым числом.');
        $request = [
            'newsTypeId' => $i->getRandomString(15, DataTypesValueHelper::CHARACTERS),
            'title'      => sqs('Test. Title'),
            'summary'    => sqs('Test. Summary.'),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id'], $request);
        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'newsTypeId' => 'Значение «News Type Id» должно быть целым числом.',
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
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function updateNewsNewsTypeIdToArray(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле newsTypeId}: 
        Передаем: значение типа array.
        Ожидаем: ошибку с пояснением - Значение «News Type Id» должно быть целым числом.');
        $request = [
            'newsTypeId' => ['array'],
            'title'      => sqs('Test. Title'),
            'summary'    => sqs('Test. Summary.'),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id'], $request);
        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'newsTypeId' => 'Значение «News Type Id» должно быть целым числом.',
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
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function updateNewsNewsTypeIdToSqlQuery(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле newsTypeId}: 
        Передаем: корректный sql запрос.
        Ожидаем: ошибку с пояснением - Значение «News Type Id» должно быть целым числом.');
        $request = [
            'newsTypeId' => DataTypesValueHelper::SQL_QUERY,
            'title'      => sqs('Test. Title'),
            'summary'    => sqs('Test. Summary.'),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id'], $request);
        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'newsTypeId' => 'Значение «News Type Id» должно быть целым числом.',
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
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function updateNewsTitleMaxPhysicLength(NewsTester $i): void
    {
        $i->wantTo('P [News] {поле title}: тип varchar(255)
        Передаем: максимальная длина значения типа данных.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $newsData         = $i->createNews();
        $preconditionData = $i->preconditionForNews();
        $request          = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => $i->getRandomString(255, DataTypesValueHelper::ASCII),
            'summary'    => sqs('Test. Summary.'),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $newsData['id'], $request);
        $i->seeResponseCodeIs(HttpCode::OK);
        $updatedNews = array_merge(['id' => $newsData['id']], $request);
        $i->seeInNewsTable($updatedNews);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => $updatedNews,
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function updateNewsTitleOverMaxPhysicLength(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле title}: тип varchar(255)
        Передаем: превышение максимальной длины значения типа данных.
        Ожидаем: ошибка с пояснением - Значение «Title» должно содержать максимум 255 символов.');
        $preconditionData = $i->preconditionForNews();
        $request          = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => $i->getRandomString(256, DataTypesValueHelper::ASCII),
            'summary'    => sqs('Test. Summary.'),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id'], $request);
        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'title' => 'Значение «Title» должно содержать максимум 255 символов.',
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
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function updateNewsTitleNull(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле title}: 
        Передаем: NULL.
        Ожидаем: ошибка с пояснением - Необходимо заполнить «Title».');
        $preconditionData = $i->preconditionForNews();
        $request          = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => null,
            'summary'    => sqs('Test. Summary.'),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id'], $request);
        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'title' => 'Необходимо заполнить «Title».',
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
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function updateNewsTitleToHieroglyphs(NewsTester $i): void
    {
        $i->wantTo('P [News] {поле title}: 
        Передаем: японские иероглифы.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $newsData = $i->createNews();
        $request  = [
            'newsTypeId' => $newsData['newsTypeId'],
            'title'      => DataTypesValueHelper::JP_HIEROGLYPHS,
            'summary'    => sqs('Test. Summary.'),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $newsData['id'], $request);
        $i->seeResponseCodeIs(HttpCode::OK);
        $updatedNews = array_merge(['id' => $newsData['id']], $request);
        $i->seeInNewsTable($updatedNews);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => $updatedNews,
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function updateNewsTitleToNonAscii(NewsTester $i): void
    {
        $i->wantTo('P [News] {поле title}: 
        Передаем: cтрока с набором non-ascii символов.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $newsData = $i->createNews();
        $request  = [
            'newsTypeId' => $newsData['newsTypeId'],
            'title'      => DataTypesValueHelper::NON_ASCII,
            'summary'    => sqs('Test. Summary.'),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $newsData['id'], $request);
        $updatedNews = array_merge(['id' => $newsData['id']], $request);
        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeInNewsTable($updatedNews);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => $updatedNews,
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function updateNewsTitleToSpecialChars(NewsTester $i): void
    {
        $i->wantTo('P [News] {поле title}: 
        Передаем: строка с набором рандомных спецсимволов.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $newsData = $i->createNews();
        $request  = [
            'newsTypeId' => $newsData['newsTypeId'],
            'title'      => $i->getRandomString(14, DataTypesValueHelper::CHARACTERS),
            'summary'    => sqs('Test. Summary.'),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $newsData['id'], $request);
        $updatedNews = array_merge(['id' => $newsData['id']], $request);
        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeInNewsTable($updatedNews);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => $updatedNews,
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     *
     * @throws
     */
    public function updateNewsTitleToArray(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле title}: 
        Передаем: значение типа array.
        Ожидаем: ошибку с пояснением - Значение «Title» должно быть строкой.');
        $newsData = $i->createNews();
        $request  = [
            'newsTypeId' => $newsData['newsTypeId'],
            'title'      => ['array'],
            'summary'    => sqs('Test. Summary.'),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $newsData['id'], $request);
        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'title' => 'Значение «Title» должно быть строкой.',
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
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     *
     * @throws
     */
    public function updateNewsTitleToSqlQuery(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле title}: 
        Передаем: корректный SQL запрос.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $newsData = $i->createNews();
        $request  = [
            'newsTypeId' => $newsData['newsTypeId'],
            'title'      => DataTypesValueHelper::SQL_QUERY,
            'summary'    => sqs('Test. Summary.'),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $newsData['id'], $request);
        $i->seeResponseCodeIs(HttpCode::OK);
        $updatedNews = array_merge(['id' => $newsData['id']], $request);
        $i->seeInNewsTable($updatedNews);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => $updatedNews,
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function updateNewsSummaryMaxPhysicLength(NewsTester $i): void
    {
        $i->wantTo('P [News] {поле summary}: тип text
        Передаем: максимальную длину значения типа данных.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $newsData = $i->createNews();
        $request  = [
            'newsTypeId' => $newsData['newsTypeId'],
            'title'      => sqs('Test. Title'),
            'summary'    => $i->getRandomString(65535, DataTypesValueHelper::ASCII),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $newsData['id'], $request);
        $i->seeResponseCodeIs(HttpCode::OK);
        $updatedNews = array_merge(['id' => $newsData['id']], $request);
        $i->seeInNewsTable($updatedNews);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function updateNewsSummaryOverMaxPhysicLength(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле summary}: тип text
        Передаем: превышение максимальной длины значения типа данных.
        Ожидаем: ошибка с пояснением - Значение «Summary» должно содержать максимум 65 535 символов.');
        $preconditionData = $i->preconditionForNews();
        $request          = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => sqs('Test. Title'),
            'summary'    => $i->getRandomString(65536, DataTypesValueHelper::ASCII),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id'], $request);
        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'summary' => 'Значение «Summary» должно содержать максимум 65 535 символов.',
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
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function updateNewsSummaryToNull(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле summary}:
        Передаем: NULL.
        Ожидаем: ошибка с пояснением - Необходимо заполнить «Summary».');
        $preconditionData = $i->preconditionForNews();
        $request          = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => sqs('Test. Title'),
            'summary'    => null,
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id'], $request);
        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'summary' => 'Необходимо заполнить «Summary».',
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
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function updateNewsSummaryToHieroglyphs(NewsTester $i): void
    {
        $i->wantTo('P [News] {поле summary}:
        Передаем: японские иероглифы.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $newsData = $i->createNews();
        $request  = [
            'newsTypeId' => $newsData['newsTypeId']['id'],
            'title'      => sqs('Test. Title'),
            'summary'    => $i->getRandomString(10, DataTypesValueHelper::JP_HIEROGLYPHS),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $newsData['id'], $request);
        $i->seeResponseCodeIs(HttpCode::OK);
        $updatedNews = array_merge(['id' => $newsData['id']], $request);
        $i->seeInNewsTable($updatedNews);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => $updatedNews,
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function updateNewsSummaryToNonAscii(NewsTester $i): void
    {
        $i->wantTo('P [News] {поле summary}:
        Передаем: строка с набором non-ascii символов.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $newsData = $i->createNews();
        $request  = [
            'newsTypeId' => $newsData['newsTypeId'],
            'title'      => sqs('Test. Title'),
            'summary'    => $i->getRandomString(10, DataTypesValueHelper::NON_ASCII),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $newsData['id'], $request);
        $i->seeResponseCodeIs(HttpCode::OK);
        $updatedNews = array_merge(['id' => $newsData['id']], $request);
        $i->seeInNewsTable($updatedNews);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => $updatedNews,
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function updateNewsSummarySpecialChars(NewsTester $i): void
    {
        $i->wantTo('P [News] {поле summary}:
        Передаем: строка с набором рандомных спецсимволов.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $newsData = $i->createNews();
        $request  = [
            'newsTypeId' => $newsData['newsTypeId'],
            'title'      => sqs('Test. Title'),
            'summary'    => $i->getRandomString(10, DataTypesValueHelper::CHARACTERS),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id'], $request);
        $i->seeResponseCodeIs(HttpCode::OK);
        $updatedNews = array_merge(['id' => $newsData['id']], $request);
        $i->seeInNewsTable($updatedNews);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => $updatedNews,
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function updateNewsSummaryToArray(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле summary}:
        Передаем: значение типа array.
        Ожидаем: ошибку с пояснением - Значение «Summary» должно быть строкой.');
        $newsData = $i->createNews();
        $request  = [
            'newsTypeId' => $newsData['newsTypeId'],
            'title'      => sqs('Test. Title'),
            'summary'    => ['array'],
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $newsData['id'], $request);
        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'summary' => 'Значение «Summary» должно быть строкой.',
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
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     *
     * @throws
     */
    public function updateNewsSummaryToSqlQuery(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле summary}:
        Передаем: корректный SQL запрос.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $newsData = $i->createNews();
        $request  = [
            'newsTypeId' => $newsData['newsTypeId'],
            'title'      => sqs('Test. Title'),
            'summary'    => sqs('Test. Summary.'),
        ];
        $request['summary'] = DataTypesValueHelper::SQL_QUERY;

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $newsData['id'], $request);
        $i->seeResponseCodeIs(HttpCode::OK);
        $updatedNews = array_merge(['id' => $newsData['id']], $request);
        $i->seeInNewsTable($updatedNews);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => $updatedNews,
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     *
     * @throws
     */
    public function updateNewsIsActiveToBool(NewsTester $i): void
    {
        $i->wantTo('P [News] {поле IsActive}: тип tinyInt(1)
        Передаем: значение true.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $newsData = $i->createNews();
        $preconditionData    = $i->preconditionForNews();
        $request             = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => sqs('Test. Title'),
            'summary'    => sqs('Test. Summary.'),
        ];
        $request['isActive'] = true;

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $newsData['id'], $request);
        $i->seeResponseCodeIs(HttpCode::OK);
        $updatedNews = array_merge(['id' => $newsData['id']], $request);
        $i->seeInNewsTable($updatedNews);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => $updatedNews,
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     *
     * @throws
     */
    public function updateNewsIsActiveToInteger(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле IsActive}: тип tinyInt(1)
        Передаем: значение random_int(2, 300). 
        Ожидаем: ошибку с пояснением - Значение «Is Active» должно быть равно «1» или «0».');
        $preconditionData    = $i->preconditionForNews();
        $request             = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => sqs('Test. Title'),
            'summary'    => sqs('Test. Summary.'),
        ];
        $request['isActive'] = random_int(2, 300);

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id'], $request);
        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'isActive' => 'Значение «Is Active» должно быть равно «1» или «0».',
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
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     *
     * @throws
     */
    public function updateNewsIsActiveToString(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле IsActive}: тип tinyInt(1)
        Передаем: значение типа string. 
        Ожидаем: ошибку с пояснением - Значение «Is Active» должно быть равно «1» или «0».');

        $preconditionData    = $i->preconditionForNews();
        $request             = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => sqs('Test. Title'),
            'summary'    => sqs('Test. Summary.'),
        ];
        $request['isActive'] = sqs('test');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id'], $request);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'isActive' => 'Значение «Is Active» должно быть равно «1» или «0».',
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
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     *
     * @throws
     */
    public function updateNewsIsActiveToFloat(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле IsActive}: тип tinyInt(1)
        Передаем: значение типа float. 
        Ожидаем: ошибку с пояснением - Значение «Is Active» должно быть равно «1» или «0».');
        $preconditionData    = $i->preconditionForNews();
        $request             = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => sqs('Test. Title'),
            'summary'    => sqs('Test. Summary.'),
        ];
        $request['isActive'] = 2.23;

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id'], $request);
        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'isActive' => 'Значение «Is Active» должно быть равно «1» или «0».',
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
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     *
     * @throws
     */
    public function updateNewsPublicDateOverMaxMonth(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле publicDate}: тип date YYYY-MM-DD
        Передаем: превышение максимального значения месяца.
        Ожидаем: ошибка с пояснением - Неверный формат значения «Public Date».');
        $request = [
            'newsTypeId' => $i->createNewsType()['id'],
            'title'      => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'summary'    => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'publicDate' => '2020-13-30',
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id'], $request);
        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'publicDate' => 'Неверный формат значения «Public Date».',
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
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     *
     * @throws
     */
    public function updateNewsPublicDateOverMaxDay(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле publicDate}: тип date YYYY-MM-DD
        Передаем: превышение максимального значения дня.
        Ожидаем: ошибка с пояснением - Неверный формат значения «Public Date».');
        $request = [
            'newsTypeId' => $i->createNewsType()['id'],
            'title'      => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'summary'    => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'publicDate' => '2020-11-32',
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id'], $request);
        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'publicDate' => 'Неверный формат значения «Public Date».',
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
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     *
     * @throws
     */
    public function updateNewsPublicDateMinDataValue(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле publicDate}: тип date YYYY-MM-DD
        Передаем: минимальное значение типа данных. 
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');

        $request = [
            'newsTypeId' => $i->createNewsType()['id'],
            'title'      => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'summary'    => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'publicDate' => '0001-01-01',
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id'], $request);

        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeInNewsTable($request);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'newsTypeId' => $request['newsTypeId'],
                'title'      => $request['title'],
                'summary'    => $request['summary'],
                'publicDate' => $request['publicDate'],
            ],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     *
     * @throws
     */
    public function updateNewsPublicDateLessMinData(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле publicDate}: тип date YYYY-MM-DD
        Передаем: все нули 0000-00-00.
        Ожидаем: ошибка с пояснением - Неверный формат значения «Public Date».');

        $request = [
            'newsTypeId' => $i->createNewsType()['id'],
            'title'      => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'summary'    => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'publicDate' => '0000-00-00',
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id'], $request);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'publicDate' => 'Неверный формат значения «Public Date».',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->dontSeeInNewsTable($request);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     *
     * @throws
     */
    public function updateNewsPublicDatePatternMonthDayOneSymbol(NewsTester $i): void
    {
        $i->wantTo('P [News] {поле publicDate}: тип date YYYY-MM-DD
        Передаем: односимвольное указание месяца. 
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи, в БД сохраняется с добавлением нуля.');

        $request = [
            'newsTypeId' => $i->createNewsType()['id'],
            'title'      => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'summary'    => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'publicDate' => '2020-3-12',
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id'], $request);

        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeInNewsTable($request);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'newsTypeId' => $request['newsTypeId'],
                'title'      => $request['title'],
                'summary'    => $request['summary'],
                'publicDate' => $request['publicDate'],
            ],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     *
     * @throws
     */
    public function updateNewsPublicDateToInt(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле publicDate}: тип date YYYY-MM-DD
        Передаем: значение типа int. 
        Ожидаем: ошибку с пояснением - Неверный формат значения «Public Date».');

        $request = [
            'newsTypeId' => $i->createNewsType()['id'],
            'title'      => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'summary'    => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'publicDate' => 2020 - 03 - 15,
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id'], $request);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'publicDate' => 'Неверный формат значения «Public Date».',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->dontSeeInNewsTable($request);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     *
     * @throws
     */
    public function updateNewsPublicDateToArray(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле publicDate}: тип date YYYY-MM-DD
        Передаем: значение типа array. 
        Ожидаем: ошибку с пояснением - Неверный формат значения «Public Date».');

        $request = [
            'newsTypeId' => $i->createNewsType()['id'],
            'title'      => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'summary'    => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'publicDate' => ['2020-03-15'],
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id'], $request);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'publicDate' => 'Неверный формат значения «Public Date».',
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
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     *
     * @throws
     */
    public function updateNewsPublicDateToString(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле publicDate}: тип date YYYY-MM-DD
        Передаем: рандомный текст в строке. 
        Ожидаем: ошибку с пояснением - Неверный формат значения «Public Date».');

        $request = [
            'newsTypeId' => $i->createNewsType()['id'],
            'title'      => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'summary'    => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'publicDate' => $i->getRandomString(10, DataTypesValueHelper::LATIN_UPPER),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id'], $request);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'publicDate' => 'Неверный формат значения «Public Date».',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->dontSeeInNewsTable($request);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     * Какой конкретно тип time?.
     * http://www.mysql.ru/docs/man/TIME.html.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     *
     * @throws
     */
    public function updateNewsPublicTimeMaxTimeValue(NewsTester $i): void
    {
        $i->wantTo('P [News] {поле publicTime}: тип time HH:MM:SS
        Передаем: максимальное значение типа данных. 
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');

        $request = [
            'newsTypeId' => $i->createNewsType()['id'],
            'title'      => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'summary'    => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'publicTime' => '23:59:59',
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id'], $request);

        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeInNewsTable($request);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'newsTypeId' => $request['newsTypeId'],
                'title'      => $request['title'],
                'summary'    => $request['summary'],
                'publicTime' => $request['publicTime'],
            ],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     * Какой конкретно тип time?.
     * http://www.mysql.ru/docs/man/TIME.html.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     *
     * @throws
     */
    public function updateNewsPublicTimeOverMaxHour(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле publicTime}: тип time HH:MM:SS
        Передаем: больше максимального значения в часах.
        Ожидаем: ошибка с пояснением - Неверный формат значения «Public Time».');

        $request = [
            'newsTypeId' => $i->createNewsType()['id'],
            'title'      => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'summary'    => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'publicTime' => '24:00:01',
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id'], $request);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'publicTime' => 'Неверный формат значения «Public Time».',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->dontSeeInNewsTable($request);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     * Какой конкретно тип time?.
     * http://www.mysql.ru/docs/man/TIME.html.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     *
     * @throws
     */
    public function updateNewsPublicTimeOverMaxMinute(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле publicTime}: тип time HH:MM:SS
        Передаем: больше максимального значения в минутах.
        Ожидаем: ошибка с пояснением - Неверный формат значения «Public Time».');

        $request = [
            'newsTypeId' => $i->createNewsType()['id'],
            'title'      => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'summary'    => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'publicTime' => '23:60:01',
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id'], $request);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'publicTime' => 'Неверный формат значения «Public Time».',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->dontSeeInNewsTable($request);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     * Какой конкретно тип time?.
     * http://www.mysql.ru/docs/man/TIME.html.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     *
     * @throws
     */
    public function updateNewsPublicTimeOverMaxSeconds(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле publicTime}: тип time HH:MM:SS
        Передаем: больше максимального значения в секундах.
        Ожидаем: ошибка с пояснением - Неверный формат значения «Public Time».');

        $request = [
            'newsTypeId' => $i->createNewsType()['id'],
            'title'      => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'summary'    => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'publicTime' => '23:50:60',
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id'], $request);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'publicTime' => 'Неверный формат значения «Public Time».',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->dontSeeInNewsTable($request);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     *
     * @throws
     */
    public function updateNewsPublicTimeLessMinTimeValue(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле publicTime}: тип time HH:MM:SS
        Передаем: отрицательное время. 
        Ожидаем: ошибка с пояснением - Неверный формат значения «Public Time».');

        $request = [
            'newsTypeId' => $i->createNewsType()['id'],
            'title'      => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'summary'    => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'publicTime' => '-00:00:01',
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id'], $request);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'publicTime' => 'Неверный формат значения «Public Time».',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->dontSeeInNewsTable($request);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     *
     * @throws
     */
    public function updateNewsPublicTimePatternHourOneSymbol(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле publicTime}: тип time HH:MM:SS
        Передаем: односимвольное значение часа
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи, в БД сохраняется с добавлением нуля.');

        $request = [
            'newsTypeId' => $i->createNewsType()['id'],
            'title'      => $i->getRandomString(10, DataTypes::ASCII),
            'summary'    => $i->getRandomString(10, DataTypes::ASCII),
            'publicTime' => '4:00:01',
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id'], $request);

        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeInNewsTable($request);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'newsTypeId' => $request['newsTypeId'],
                'title'      => $request['title'],
                'summary'    => $request['summary'],
                'publicTime' => $request['publicTime'],
            ],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     *
     * @throws
     */
    public function updateNewsPublicTimePatternMinuteOneSymbol(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле publicTime}: тип time HH:MM:SS
        Передаем: односимвольное значение минут
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи, в БД сохраняется с добавлением нуля.');

        $request = [
            'newsTypeId' => $i->createNewsType()['id'],
            'title'      => $i->getRandomString(10, DataTypes::ASCII),
            'summary'    => $i->getRandomString(10, DataTypes::ASCII),
            'publicTime' => '14:6:01',
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id'], $request);

        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeInNewsTable($request);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'newsTypeId' => $request['newsTypeId'],
                'title'      => $request['title'],
                'summary'    => $request['summary'],
                'publicTime' => $request['publicTime'],
            ],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     *
     * @throws
     */
    public function updateNewsPublicTimePatternSecondsOneSymbol(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле publicTime}: тип time HH:MM:SS
        Передаем: односимвольное значение секунд
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи, в БД сохраняется с добавлением нуля.');

        $request = [
            'newsTypeId' => $i->createNewsType()['id'],
            'title'      => $i->getRandomString(10, DataTypes::ASCII),
            'summary'    => $i->getRandomString(10, DataTypes::ASCII),
            'publicTime' => '14:26:1',
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id'], $request);

        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeInNewsTable($request);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'newsTypeId' => $request['newsTypeId'],
                'title'      => $request['title'],
                'summary'    => $request['summary'],
                'publicTime' => $request['publicTime'],
            ],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     *
     * @throws
     */
    public function updateNewsPublicTimeToInt(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле publicTime}: тип time HH:MM:SS
        Передаем: значение типа int. 
        Ожидаем: ошибка с пояснением - Неверный формат значения «Public Time».');

        $request = [
            'newsTypeId' => $i->createNewsType()['id'],
            'title'      => $i->getRandomString(10, DataTypes::ASCII),
            'summary'    => $i->getRandomString(10, DataTypes::ASCII),
            'publicTime' => 125536,
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id'], $request);
        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'publicTime' => 'Неверный формат значения «Public Time».',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->dontSeeInNewsTable($request);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     *
     * @throws
     */
    public function updateNewsPublicTimeToArray(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле publicTime}: тип time HH:MM:SS
        Передаем: значение типа array. 
        Ожидаем: ошибка с пояснением - Неверный формат значения «Public Time».');

        $request = [
            'newsTypeId' => $i->createNewsType()['id'],
            'title'      => $i->getRandomString(10, DataTypes::ASCII),
            'summary'    => $i->getRandomString(10, DataTypes::ASCII),
            'publicTime' => ['22:11:56'],
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id'], $request);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'publicTime' => 'Неверный формат значения «Public Time».',
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
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     *
     * @throws
     */
    public function updateNewsPublicTimeToString(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле publicTime}: тип time HH:MM:SS
        Передаем: строку с рандомным текстом.
        Ожидаем: ошибка с пояснением - Неверный формат значения «Public Time».');

        $request = [
            'newsTypeId' => $i->createNewsType()['id'],
            'title'      => $i->getRandomString(10, DataTypes::ASCII),
            'summary'    => $i->getRandomString(10, DataTypes::ASCII),
            'publicTime' => $i->getRandomString(10, DataTypes::LATIN_UPPER),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id'], $request);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'publicTime' => 'Неверный формат значения «Public Time».',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->dontSeeInNewsTable($request);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     *
     * @throws
     */
    public function updateNewsCloseDateTimeMinValue(NewsTester $i): void
    {
        $i->wantTo('P [News] {поле closeDateTime}: тип dateTime YYYY-MM-DD HH:MM:SS
        Передаем: минимальное значение типа.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');

        $request = [
            'newsTypeId'    => $i->createNewsType()['id'],
            'title'         => $i->getRandomString(10, DataTypes::ASCII),
            'summary'       => $i->getRandomString(10, DataTypes::ASCII),
            'closeDateTime' => '0001-01-01 00:00:00',
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id'], $request);

        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeInNewsTable($request);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'newsTypeId'    => $request['newsTypeId'],
                'title'         => $request['title'],
                'summary'       => $request['summary'],
                'closeDateTime' => $request['closeDateTime'],
            ],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     *
     * @throws
     */
    public function updateNewsCloseDateTimeOverMaxMonth(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле closeDateTime}: тип dateTime YYYY-MM-DD HH:MM:SS
        Передаем: превышение максимального значения месяца. 
        Ожидаем: ошибка с пояснением - Неверный формат значения «Close Date Time».');

        $request = [
            'newsTypeId'    => $i->createNewsType()['id'],
            'title'         => $i->getRandomString(10, DataTypes::ASCII),
            'summary'       => $i->getRandomString(10, DataTypes::ASCII),
            'closeDateTime' => '2002-13-10 22:11:12',
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id'], $request);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'closeDateTime' => 'Неверный формат значения «Close Date Time».',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->dontSeeInNewsTable($request);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     *
     * @throws
     */
    public function updateNewsCloseDateTimeOverMaxDay(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле closeDateTime}: тип dateTime YYYY-MM-DD HH:MM:SS
        Передаем: превышение максимального значения дня. 
        Ожидаем: ошибка с пояснением - Неверный формат значения «Close Date Time».');

        $request = [
            'newsTypeId'    => $i->createNewsType()['id'],
            'title'         => $i->getRandomString(10, DataTypes::ASCII),
            'summary'       => $i->getRandomString(10, DataTypes::ASCII),
            'closeDateTime' => '2020-10-32 22:11:16',
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id'], $request);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'closeDateTime' => 'Неверный формат значения «Close Date Time».',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->dontSeeInNewsTable($request);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     *
     * @throws
     */
    public function updateNewsCloseDateTimeOverMaxHour(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле closeDateTime}: тип dateTime YYYY-MM-DD HH:MM:SS
        Передаем: превышение максимального значения часа. 
        Ожидаем: ошибка с пояснением - Неверный формат значения «Close Date Time».');

        $request = [
            'newsTypeId'    => $i->createNewsType()['id'],
            'title'         => $i->getRandomString(10, DataTypes::ASCII),
            'summary'       => $i->getRandomString(10, DataTypes::ASCII),
            'closeDateTime' => '2020-10-22 24:11:16',
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id'], $request);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'closeDateTime' => 'Неверный формат значения «Close Date Time».',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->dontSeeInNewsTable($request);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     *
     * @throws
     */
    public function updateNewsCloseDateTimeOverMaxMinute(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле closeDateTime}: тип dateTime YYYY-MM-DD HH:MM:SS
        Передаем: превышение максимального значения минут. 
        Ожидаем: ошибка с пояснением - Неверный формат значения «Close Date Time».');

        $request = [
            'newsTypeId'    => $i->createNewsType()['id'],
            'title'         => $i->getRandomString(10, DataTypes::ASCII),
            'summary'       => $i->getRandomString(10, DataTypes::ASCII),
            'closeDateTime' => '2020-10-22 21:60:16',
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id'], $request);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'closeDateTime' => 'Неверный формат значения «Close Date Time».',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->dontSeeInNewsTable($request);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     *
     * @throws
     */
    public function updateNewsCloseDateTimeOverMaxSeconds(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле closeDateTime}: тип dateTime YYYY-MM-DD HH:MM:SS
        Передаем: превышение максимального значения секунд. 
        Ожидаем: ошибка с пояснением - Неверный формат значения «Close Date Time».');

        $request = [
            'newsTypeId'    => $i->createNewsType()['id'],
            'title'         => $i->getRandomString(10, DataTypes::ASCII),
            'summary'       => $i->getRandomString(10, DataTypes::ASCII),
            'closeDateTime' => '2020-10-22 21:12:60',
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id'], $request);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'closeDateTime' => 'Неверный формат значения «Close Date Time».',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->dontSeeInNewsTable($request);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     *
     * @throws
     */
    public function updateNewsCloseDateTimePatternOneSymbol(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле closeDateTime}: тип dateTime YYYY-MM-DD HH:MM:SS
        Передаем: односимвольное указание месяца. 
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи, в БД сохраняется с добавлением нуля.');

        $request = [
            'newsTypeId'    => $i->createNewsType()['id'],
            'title'         => $i->getRandomString(10, DataTypes::ASCII),
            'summary'       => $i->getRandomString(10, DataTypes::ASCII),
            'closeDateTime' => '2020-3-12 21:12:12',
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id'], $request);

        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeInNewsTable($request);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'newsTypeId'    => $request['newsTypeId'],
                'title'         => $request['title'],
                'summary'       => $request['summary'],
                'closeDateTime' => $request['closeDateTime'],
            ],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     *
     * @throws
     */
    public function updateNewsCloseDateTimePatternWrongFormat(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле closeDateTime}: тип dateTime YYYY-MM-DD HH:MM:SS
        Передаем: невалидный формат записи времени HHMMSS. 
        Ожидаем: ошибка с пояснением - Неверный формат значения «Close Date Time».');

        $request = [
            'newsTypeId'    => $i->createNewsType()['id'],
            'title'         => $i->getRandomString(10, DataTypes::ASCII),
            'summary'       => $i->getRandomString(10, DataTypes::ASCII),
            'closeDateTime' => '2222-10-10 235655',
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id'], $request);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'closeDateTime' => 'Неверный формат значения «Close Date Time».',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->dontSeeInNewsTable($request);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     *
     * @throws
     */
    public function updateNewsCloseDateTimeToInt(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле closeDateTime}: тип dateTime YYYY-MM-DD HH:MM:SS
        Передаем: значение типа int.
        Ожидаем: ошибка с пояснением - Неверный формат значения «Close Date Time».');

        $request = [
            'newsTypeId'    => $i->createNewsType()['id'],
            'title'         => $i->getRandomString(10, DataTypes::ASCII),
            'summary'       => $i->getRandomString(10, DataTypes::ASCII),
            'closeDateTime' => 2222 - 11 - 10 - 000001,
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id'], $request);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'closeDateTime' => 'Неверный формат значения «Close Date Time».',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->dontSeeInNewsTable($request);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     *
     * @throws
     */
    public function updateNewsCloseDateTimeToArray(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле closeDateTime}: тип dateTime YYYY-MM-DD HH:MM:SS
        Передаем: значение типа array.
        Ожидаем: ошибка с пояснением - Неверный формат значения «Close Date Time».');

        $request = [
            'newsTypeId'    => $i->createNewsType()['id'],
            'title'         => $i->getRandomString(10, DataTypes::ASCII),
            'summary'       => $i->getRandomString(10, DataTypes::ASCII),
            'closeDateTime' => ['2222-11-10 22:12:12'],
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id'], $request);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'closeDateTime' => 'Неверный формат значения «Close Date Time».',
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
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     *
     * @throws
     */
    public function updateNewsCloseDateTimeToString(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле closeDateTime}: тип dateTime YYYY-MM-DD HH:MM:SS
        Передаем: строку с рандомным текстом.
        Ожидаем: ошибка с пояснением - Неверный формат значения «Close Date Time».');

        $request = [
            'newsTypeId'    => $i->createNewsType()['id'],
            'title'         => $i->getRandomString(10, DataTypes::ASCII),
            'summary'       => $i->getRandomString(10, DataTypes::ASCII),
            'closeDateTime' => $i->getRandomString(10, DataTypes::LATIN_UPPER),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id'], $request);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'closeDateTime' => 'Неверный формат значения «Close Date Time».',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->dontSeeInNewsTable($request);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     *
     * @throws
     */
    public function updateNewsIsMainToInteger(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле IsMain}: тип tinyInt(1)
        Передаем: значение rand_int(2,300).
        Ожидаем: ошибку с пояснением - Значение «Is Main» должно быть равно «1» или «0».');

        $preconditionData  = $i->preconditionForNews();
        $request           = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => sqs('Test. Title'),
            'summary'    => sqs('Test. Summary.'),
        ];
        $request['isMain'] = random_int(2, 300);

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id'], $request);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'isMain' => 'Значение «Is Main» должно быть равно «1» или «0».',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->dontSeeInNewsTable($request);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     *
     * @throws
     */
    public function updateNewsIsMainToString(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле IsMain}: тип tinyInt(1)
        Передаем: значение типа string.
        Ожидаем: ошибку с пояснением - Значение «Is Main» должно быть равно «1» или «0».');

        $preconditionData  = $i->preconditionForNews();
        $request           = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => sqs('Test. Title'),
            'summary'    => sqs('Test. Summary.'),
        ];
        $request['isMain'] = sqs('test');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id'], $request);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'isMain' => 'Значение «Is Main» должно быть равно «1» или «0».',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->dontSeeInNewsTable($request);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     *
     * @throws
     */
    public function updateNewsIsMainToFloat(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле IsMain}: тип tinyInt(1)
        Передаем: значение типа float.
        Ожидаем: ошибку с пояснением - Значение «Is Main» должно быть равно «1» или «0».');

        $preconditionData  = $i->preconditionForNews();
        $request           = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => sqs('Test. Title'),
            'summary'    => sqs('Test. Summary.'),
        ];
        $request['isMain'] = 0.123;

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id'], $request);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'isMain' => 'Значение «Is Main» должно быть равно «1» или «0».',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->dontSeeInNewsTable($request);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function updateNewsSrcUrlMaxPhysicLength(NewsTester $i): void
    {
        $i->wantTo('P [News] {поле srcUrl}: тип varchar(255) 
        Передаем: максимальная длина значения типа данных.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');

        $preconditionData  = $i->preconditionForNews();
        $request           = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => sqs('Test. Title'),
            'summary'    => sqs('Test. Summary.'),
        ];
        $request['srcUrl'] = $i->getRandomString(255, DataTypes::ASCII);

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id'], $request);

        $dataUpdatedNews = $i->grabListFromNewsTable($request);
        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeInNewsTable($request);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => $dataUpdatedNews[0],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function updateNewsSrcUrlOverMaxPhysicLength(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле srcUrl}: тип varchar(255)
        Передаем: превышение максимальной длины значения типа данных.
        Ожидаем: ошибка с пояснением - Значение «Src Url» должно содержать максимум 255 символов.');

        $preconditionData  = $i->preconditionForNews();
        $request           = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => sqs('Test. Title'),
            'summary'    => sqs('Test. Summary.'),
        ];
        $request['srcUrl'] = $i->getRandomString(256, DataTypes::ASCII);

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id'], $request);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'srcUrl' => 'Значение «Src Url» должно содержать максимум 255 символов.',
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
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function updateNewsSrcUrlToHieroglyphs(NewsTester $i): void
    {
        $i->wantTo('P [News] {поле srcUrl}: тип varchar(255)
        Передаем: японские иероглифы.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');

        $preconditionData  = $i->preconditionForNews();
        $request           = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => sqs('Test. Title'),
            'summary'    => sqs('Test. Summary.'),
        ];
        $request['srcUrl'] = DataTypes::JP_HIEROGLYPHS;

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id'], $request);
        $i->seeResponseCodeIs(HttpCode::OK);
        $updatedNews = array_merge(['id' => $newsData['id']], $request);
        $i->seeInNewsTable($updatedNews);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => $updatedNews,
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function updateNewsSrcUrlToNonAscii(NewsTester $i): void
    {
        $i->wantTo('P [News] {поле srcUrl}: тип varchar(255)
        Передаем: строка с рандомным набором non-ascii символов.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');

        $preconditionData  = $i->preconditionForNews();
        $request           = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => sqs('Test. Title'),
            'summary'    => sqs('Test. Summary.'),
        ];
        $request['srcUrl'] = DataTypes::NON_ASCII;

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id'], $request);
        $i->seeResponseCodeIs(HttpCode::OK);
        $updatedNews = array_merge(['id' => $newsData['id']], $request);
        $i->seeInNewsTable($updatedNews);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => $updatedNews,
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function updateNewsSrcUrlToSpecialChars(NewsTester $i): void
    {
        $i->wantTo('P [News] {поле srcUrl}: тип varchar(255)
        Передаем: набор рандомных спецсимволов в строке.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');

        $preconditionData  = $i->preconditionForNews();
        $request           = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => sqs('Test. Title'),
            'summary'    => sqs('Test. Summary.'),
        ];
        $request['srcUrl'] = DataTypes::CHARACTERS;

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id'], $request);
        $i->seeResponseCodeIs(HttpCode::OK);
        $updatedNews = array_merge(['id' => $newsData['id']], $request);
        $i->seeInNewsTable($updatedNews);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => $updatedNews,
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     *
     * @throws
     */
    public function updateNewsSrcUrlToArray(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле srcUrl}: тип varchar(255)
        Передаем: значение типа array.
        Ожидаем: ошибку с пояснением - Значение «Src Url» должно быть строкой.');

        $preconditionData  = $i->preconditionForNews();
        $request           = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => sqs('Test. Title'),
            'summary'    => sqs('Test. Summary.'),
        ];
        $request['srcUrl'] = ['array'];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id'], $request);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'srcUrl' => 'Значение «Src Url» должно быть строкой.',
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
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     *
     * @throws
     */
    public function updateNewsSrcUrlToSqlQuery(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле srcUrl}: тип varchar(255)
        Передаем: корректный SQL запрос.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');

        $preconditionData  = $i->preconditionForNews();
        $request           = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => sqs('Test. Title'),
            'summary'    => sqs('Test. Summary.'),
        ];
        $request['srcUrl'] = DataTypes::SQL_QUERY;

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id'], $request);
        $i->seeResponseCodeIs(HttpCode::OK);
        $updatedNews = array_merge(['id' => $newsData['id']], $request);
        $i->seeInNewsTable($updatedNews);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => $updatedNews,
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     * Кодесепшн не может сделать запрос в бд с таким большим значением.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function updateNewsBodyMaxPhysicLength(NewsTester $i): void
    {
        $i->wantTo('P [News] {поле body}: тип text 
        Передаем: максимальная длина значения типа данных.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');

        $preconditionData = $i->preconditionForNews();
        $request          = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => sqs('Test. Title'),
            'summary'    => sqs('Test. Summary.'),
        ];
        $request['body']  = $i->getRandomString(65535, DataTypes::ASCII);
        $newsData         = $i->createNews();

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $newsData['id'], $request);

        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeInNewsTable([
            'id'        => $newsData['id'],
            'body like' => '%' . mb_substr($request['body'], 2, 50, 'UTF-8') . '%',
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function updateNewsBodyOverMaxPhysicLength(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле body}: тип text
        Передаем: превышение максимальной длины значения типа данных.
        Ожидаем: ошибка с пояснением - Значение «Body» должно содержать максимум 65 535 символов.');

        $preconditionData = $i->preconditionForNews();
        $request          = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => sqs('Test. Title'),
            'summary'    => sqs('Test. Summary.'),
        ];
        $request['body']  = $i->getRandomString(65536, DataTypes::ASCII);

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id'], $request);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'body' => 'Значение «Body» должно содержать максимум 65 535 символов.',
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
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function updateNewsBodyToHieroglyphs(NewsTester $i): void
    {
        $i->wantTo('P [News] {поле body}: тип text
        Передаем: японские иероглифы.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');

        $preconditionData = $i->preconditionForNews();
        $request          = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => sqs('Test. Title'),
            'summary'    => sqs('Test. Summary.'),
        ];
        $request['body']  = $i->getRandomString(10, DataTypes::JP_HIEROGLYPHS);

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id'], $request);
        $i->seeResponseCodeIs(HttpCode::OK);
        $updatedNews = array_merge(['id' => $newsData['id']], $request);
        $i->seeInNewsTable($updatedNews);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => $updatedNews,
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function updateNewsBodyToNonAscii(NewsTester $i): void
    {
        $i->wantTo('P [News] {поле body}: тип text
        Передаем: строка с рандомным набором non-ascii символов.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');

        $preconditionData = $i->preconditionForNews();
        $request          = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => sqs('Test. Title'),
            'summary'    => sqs('Test. Summary.'),
        ];
        $request['body']  = $i->getRandomString(10, DataTypes::NON_ASCII);

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id'], $request);
        $i->seeResponseCodeIs(HttpCode::OK);
        $updatedNews = array_merge(['id' => $newsData['id']], $request);
        $i->seeInNewsTable($updatedNews);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => $updatedNews,
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function updateNewsBodyToSpecialChars(NewsTester $i): void
    {
        $i->wantTo('P [News] {поле body}: тип text
        Передаем: строка с рандомным набором cпецсимволов.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');

        $preconditionData = $i->preconditionForNews();
        $request          = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => sqs('Test. Title'),
            'summary'    => sqs('Test. Summary.'),
        ];
        $request['body']  = $i->getRandomString(10, DataTypes::CHARACTERS);

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id'], $request);
        $i->seeResponseCodeIs(HttpCode::OK);
        $updatedNews = array_merge(['id' => $newsData['id']], $request);
        $i->seeInNewsTable($updatedNews);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => $updatedNews,
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     *
     * @throws
     */
    public function updateNewsBodyToArray(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле body}: тип text
        Передаем: значение типа array.
        Ожидаем: ошибку с пояснением - Значение «Body» должно быть строкой.');

        $preconditionData = $i->preconditionForNews();
        $request          = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => sqs('Test. Title'),
            'summary'    => sqs('Test. Summary.'),
        ];
        $request['body']  = ['array'];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id'], $request);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'body' => 'Значение «Body» должно быть строкой.',
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
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     *
     * @throws
     */
    public function updateNewsBodyToSqlQuery(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле body}: тип text
        Передаем: корректный SQL запрос.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');

        $preconditionData = $i->preconditionForNews();
        $request          = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => sqs('Test. Title'),
            'summary'    => sqs('Test. Summary.'),
        ];
        $request['body']  = DataTypes::SQL_QUERY;

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id'], $request);
        $i->seeResponseCodeIs(HttpCode::OK);
        $updatedNews = array_merge(['id' => $newsData['id']], $request);
        $i->seeInNewsTable($updatedNews);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => $updatedNews,
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     *
     * @throws
     */
    public function updateNewsNotExist(NewsTester $i): void
    {
        $i->wantTo('N [News] :
        Передаем: id несуществующей в таблице новости.
        Ожидаем: ошибка с пояснением - Сущность не найдена.');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->getNotExistNewsId(), $i->getDefaultDataRequiredFields());

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
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     *
     * @throws
     */
    public function updateNewsOverMaxPhysicInt(NewsTester $i): void
    {
        $i->wantTo('N [News] :
        Передаем: значение превышающее максимальное значение типа данных.
        Ожидаем: ошибка с пояснением - Значение «Id» не должно превышать 2147483647.');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . DataTypes::OVER_MAX_INT_SIGNED, $i->getDefaultDataRequiredFields());

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

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function updateNewsLessMinLogicInt(NewsTester $i): void
    {
        $i->wantTo('N [News] :
        Передаем: значение 0.
        Ожидаем: ошибка с пояснением - Значение «Id» должно быть не меньше 1.');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . 0, $i->getDefaultDataRequiredFields());

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
}
