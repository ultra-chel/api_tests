<?php

declare( strict_types = 1 );

namespace UserstoryTemp\NewsNegativeTestRest\tests\api\v1\news;

use Codeception\Util\HttpCode;
use UserstoryTemp\NewsNegativeTest\tests\support\Helpers\DataTypesValueHelper;
use UserstoryTemp\NewsNegativeTestRest\tests\support\Step\Api\NewsTester;

/**
 * Класс тестирования REST API: создание новой сущности "Новости".
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
    public static $URI = 'v1/news';

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
     * @return void
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
    public function createNewsWithOutAuth(NewsTester $i): void
    {
        $i->wantTo('N [News] 
        Передаем: создание сущности без авторизации. 
        Ожидаем: Ошибку с пояснением - Доступ запрещен');
        $preconditionData = $i->preconditionForNews();
        $newsData         = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => sqs('Test. Title'),
            'summary'    => sqs('Test. Summary.'),
        ];

        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
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
        $i->dontSeeInNewsTable($newsData);
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
    public function createNewsOnlyRequiredFields(NewsTester $i): void
    {
        $i->wantTo('P [News]: 
        Передаем: корректные данные в обязательных полях.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $preconditionData = $i->preconditionForNews();
        $newsData         = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => sqs('Test. Title'),
            'summary'    => sqs('Test. Summary.'),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
        $i->seeResponseCodeIs(HttpCode::OK);
        $newsData = $i->grabOneFromNewsTable($newsData);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'id'            => $newsData['id'],
                'newsTypeId'    => $newsData['newsTypeId'],
                'isActive'      => $newsData['isActive'],
                'publicDate'    => $newsData['publicDate'],
                'publicTime'    => $newsData['publicTime'],
                'closeDateTime' => $newsData['closeDateTime'],
                'isMain'        => $newsData['isMain'],
                'title'         => $newsData['title'],
                'srcUrl'        => $newsData['srcUrl'],
                'summary'       => $newsData['summary'],
                'body'          => $newsData['body'],
            ],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
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
    public function createNewsAllFields(NewsTester $i): void
    {
        $i->wantTo('P [News]: 
        Передаем: корректные данные в полях.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $preconditionData = $i->preconditionForNews();
        $newsData         = [
            'newsTypeId'    => $preconditionData['newsTypeData']['id'],
            'isActive'      => true,
            'isMain'        => true,
            'publicDate'    => '2020-01-01',
            'publicTime'    => '01:01:01',
            'closeDateTime' => '2001-02-22 08:37:34',
            'title'         => sqs('Test. Title'),
            'srcUrl'        => sqs('Test. srcUrl'),
            'summary'       => sqs('Test. Summary.'),
            'body'          => sqs('Test. body'),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
        $i->seeResponseCodeIs(HttpCode::OK);
        $newsData = $i->grabOneFromNewsTable($newsData);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'id'            => $newsData['id'],
                'newsTypeId'    => $newsData['newsTypeId'],
                'isActive'      => $newsData['isActive'],
                'publicDate'    => $newsData['publicDate'],
                'publicTime'    => $newsData['publicTime'],
                'closeDateTime' => $newsData['closeDateTime'],
                'isMain'        => $newsData['isMain'],
                'title'         => $newsData['title'],
                'srcUrl'        => $newsData['srcUrl'],
                'summary'       => $newsData['summary'],
                'body'          => $newsData['body'],
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
     * @throws \Exception
     */
    public function createNewsNewsTypeIdNotExist(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле newsTypeId}: 
        Передаем: несуществующий идентификатор в таблице типов новостей.
        Ожидаем: ошибку с пояснением - Значение «News Type Id» неверно.');
        $newsData = [
            'newsTypeId' => $i->getNotExistNewsTypeId(),
            'title'      => sqs('Test. Title'),
            'summary'    => sqs('Test. Summary.'),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
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
        $i->dontSeeInNewsTable($newsData);
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
    public function createNewsNewsTypeIdOverMaxPhysicInt(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле newsTypeId}: тип int(11)
        Передаем: превышение максимального значения типа данных.
        Ожидаем: ошибку с пояснением - Значение «News Type Id» не должно превышать 2147483647.');
        $newsData = [
            'newsTypeId' => DataTypesValueHelper::OVER_MAX_INT_SIGNED,
            'title'      => sqs('Test. Title'),
            'summary'    => sqs('Test. Summary.'),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
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
    public function createNewsNewsTypeIdLessMinLogicInt(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле newsTypeId}: тип int(11)
        Передаем: значение -1.
        Ожидаем: ошибку с пояснением - Значение «News Type Id» неверно.');
        $newsData = [
            'newsTypeId' => - 1,
            'title'      => sqs('Test. Title'),
            'summary'    => sqs('Test. Summary.'),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
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
        $i->dontSeeInNewsTable($newsData);
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
    public function createNewsNewsTypeIdFloat(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле newsTypeId}: 
        Передаем: значение типа float.
        Ожидаем: ошибку с пояснением - Значение «News Type Id» должно быть целым числом.');
        $newsData = [
            'newsTypeId' => 4.43434,
            'title'      => sqs('Test. Title'),
            'summary'    => sqs('Test. Summary.'),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
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
    public function createNewsNewsTypeIdString(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле newsTypeId}: 
        Передаем: набор ASCII символов в строке.
        Ожидаем: ошибку с пояснением - Значение «News Type Id» должно быть целым числом.');
        $newsData = [
            'newsTypeId' => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'title'      => sqs('Test. Title'),
            'summary'    => sqs('Test. Summary.'),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);

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
    public function createNewsNewsTypeIdArray(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле newsTypeId}: 
        Передаем: значение типа array.
        Ожидаем: ошибку с пояснением - Значение «News Type Id» должно быть целым числом.');
        $newsData = [
            'newsTypeId' => ['array'],
            'title'      => sqs('Test. Title'),
            'summary'    => sqs('Test. Summary.'),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);

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
    public function createNewsNewsTypeIdSqlQuery(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле newsTypeId}: 
        Передаем: корректный sql запрос.
        Ожидаем: ошибку с пояснением - Значение «News Type Id» должно быть целым числом.');
        $newsData = [
            'newsTypeId' => DataTypesValueHelper::SQL_QUERY,
            'title'      => sqs('Test. Title'),
            'summary'    => sqs('Test. Summary.'),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
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
    public function createNewsTitleMaxPhysicLength(NewsTester $i): void
    {
        $i->wantTo('P [News] {поле title}: varchar(255)
        Передаем: максимальная длина значения типа данных.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $preconditionData = $i->preconditionForNews();
        $newsData         = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => $i->getRandomString(255, DataTypesValueHelper::ASCII),
            'summary'    => sqs('Test. Summary.'),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
        $i->seeResponseCodeIs(HttpCode::OK);
        $newsData = $i->grabOneFromNewsTable($newsData);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'id'            => $newsData['id'],
                'newsTypeId'    => $newsData['newsTypeId'],
                'isActive'      => $newsData['isActive'],
                'publicDate'    => $newsData['publicDate'],
                'publicTime'    => $newsData['publicTime'],
                'closeDateTime' => $newsData['closeDateTime'],
                'isMain'        => $newsData['isMain'],
                'title'         => $newsData['title'],
                'srcUrl'        => $newsData['srcUrl'],
                'summary'       => $newsData['summary'],
                'body'          => $newsData['body'],
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
     */
    public function createNewsTitleOverMaxPhysicLength(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле title}: varchar(255)
        Передаем: превышение максимальной длины значения типа данных. 
        Ожидаем: ошибка с пояснением - Значение «Title» должно содержать максимум 255 символов.');
        $preconditionData = $i->preconditionForNews();
        $newsData         = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => $i->getRandomString(256, DataTypesValueHelper::ASCII),
            'summary'    => sqs('Test. Summary.'),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);

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
    public function createNewsTitleHieroglyphs(NewsTester $i): void
    {
        $i->wantTo('P [News] {поле title}: 
        Передаем: японские иероглифы.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $preconditionData = $i->preconditionForNews();
        $newsData         = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => DataTypesValueHelper::JP_HIEROGLYPHS,
            'summary'    => sqs('Test. Summary.'),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
        $i->seeResponseCodeIs(HttpCode::OK);
        $newsData = $i->grabOneFromNewsTable($newsData);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'id'            => $newsData['id'],
                'newsTypeId'    => $newsData['newsTypeId'],
                'isActive'      => $newsData['isActive'],
                'publicDate'    => $newsData['publicDate'],
                'publicTime'    => $newsData['publicTime'],
                'closeDateTime' => $newsData['closeDateTime'],
                'isMain'        => $newsData['isMain'],
                'title'         => $newsData['title'],
                'srcUrl'        => $newsData['srcUrl'],
                'summary'       => $newsData['summary'],
                'body'          => $newsData['body'],
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
     */
    public function createNewsTitleNonAscii(NewsTester $i): void
    {
        $i->wantTo('P [News] {поле title}: 
        Передаем: строка с рандомными non-ascii символами.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $preconditionData = $i->preconditionForNews();
        $newsData         = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => DataTypesValueHelper::NON_ASCII,
            'summary'    => sqs('Test. Summary.'),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
        $i->seeResponseCodeIs(HttpCode::OK);
        $newsData = $i->grabOneFromNewsTable($newsData);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'id'            => $newsData['id'],
                'newsTypeId'    => $newsData['newsTypeId'],
                'isActive'      => $newsData['isActive'],
                'publicDate'    => $newsData['publicDate'],
                'publicTime'    => $newsData['publicTime'],
                'closeDateTime' => $newsData['closeDateTime'],
                'isMain'        => $newsData['isMain'],
                'title'         => $newsData['title'],
                'srcUrl'        => $newsData['srcUrl'],
                'summary'       => $newsData['summary'],
                'body'          => $newsData['body'],
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
     */
    public function createNewsTitleSpecialChars(NewsTester $i): void
    {
        $i->wantTo('P [News] {поле title}: 
        Передаем: набор рандомных спецсимволов.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $preconditionData = $i->preconditionForNews();
        $newsData         = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => DataTypesValueHelper::CHARACTERS,
            'summary'    => sqs('Test. Summary.'),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
        $i->seeResponseCodeIs(HttpCode::OK);
        $newsData = $i->grabOneFromNewsTable($newsData);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'id'            => $newsData['id'],
                'newsTypeId'    => $newsData['newsTypeId'],
                'isActive'      => $newsData['isActive'],
                'publicDate'    => $newsData['publicDate'],
                'publicTime'    => $newsData['publicTime'],
                'closeDateTime' => $newsData['closeDateTime'],
                'isMain'        => $newsData['isMain'],
                'title'         => $newsData['title'],
                'srcUrl'        => $newsData['srcUrl'],
                'summary'       => $newsData['summary'],
                'body'          => $newsData['body'],
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
    public function createNewsTitleArray(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле title}: 
        Передаем: значение типа array.
        Ожидаем: ошибку с пояснением - Значение «Title» должно быть строкой.');
        $preconditionData = $i->preconditionForNews();
        $newsData         = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => ['array'],
            'summary'    => sqs('Test. Summary.'),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
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
    public function createNewsTitleToSqlQuery(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле title}: 
        Передаем: корректный SQL запрос.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $i->loginAsAdmin();
        $preconditionData = $i->preconditionForNews();
        $newsData         = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => DataTypesValueHelper::SQL_QUERY,
            'summary'    => sqs('Test. Summary.'),
        ];

        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
        $i->seeResponseCodeIs(HttpCode::OK);
        $newsData = $i->grabOneFromNewsTable($newsData);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'id'            => $newsData['id'],
                'newsTypeId'    => $newsData['newsTypeId'],
                'isActive'      => $newsData['isActive'],
                'publicDate'    => $newsData['publicDate'],
                'publicTime'    => $newsData['publicTime'],
                'closeDateTime' => $newsData['closeDateTime'],
                'isMain'        => $newsData['isMain'],
                'title'         => $newsData['title'],
                'srcUrl'        => $newsData['srcUrl'],
                'summary'       => $newsData['summary'],
                'body'          => $newsData['body'],
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
     */
    public function createNewsSummaryMaxPhysicLength(NewsTester $i): void
    {
        $i->wantTo('P [News] {поле summary}: тип text
        Передаем: максимальная длина значения типа данных.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $preconditionData = $i->preconditionForNews();
        $newsData         = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => sqs('Test. Title'),
            'summary'    => $i->getRandomString(65535, DataTypesValueHelper::ASCII),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
        $i->seeResponseCodeIs(HttpCode::OK);
        $newsData = $i->grabOneFromNewsTable([
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => sqs('Test. Title'),
            'summary like' => '%' . mb_substr($newsData['summary'], 2, 50, 'UTF-8') . '%'
        ]);
        print_r($newsData);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'id'            => $newsData['id'],
                'newsTypeId'    => $newsData['newsTypeId'],
                'isActive'      => $newsData['isActive'],
                'publicDate'    => $newsData['publicDate'],
                'publicTime'    => $newsData['publicTime'],
                'closeDateTime' => $newsData['closeDateTime'],
                'isMain'        => $newsData['isMain'],
                'title'         => $newsData['title'],
                'srcUrl'        => $newsData['srcUrl'],
                'summary'       => $newsData['summary'],
                'body'          => $newsData['body'],
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
     */
    public function createNewsSummaryOverMaxPhysicLength(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле summary}: тип text
        Передаем: превышение максимальной длины значения типа данных.
        Ожидаем: ошибка с пояснением - Значение «Summary» должно содержать максимум 65 535 символов.');
        $preconditionData = $i->preconditionForNews();
        $newsData         = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => sqs('Test. Title'),
            'summary'    => $i->getRandomString(65536, DataTypesValueHelper::ASCII),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
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
    public function createNewsSummaryHieroglyphs(NewsTester $i): void
    {
        $i->wantTo('P [News] {поле summary}:
        Передаем: японские иероглифы.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $preconditionData = $i->preconditionForNews();
        $newsData         = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => sqs('Test. Title'),
            'summary'    => $i->getRandomString(10, DataTypesValueHelper::JP_HIEROGLYPHS),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
        $i->seeResponseCodeIs(HttpCode::OK);
        $newsData = $i->grabOneFromNewsTable($newsData);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'id'            => $newsData['id'],
                'newsTypeId'    => $newsData['newsTypeId'],
                'isActive'      => $newsData['isActive'],
                'publicDate'    => $newsData['publicDate'],
                'publicTime'    => $newsData['publicTime'],
                'closeDateTime' => $newsData['closeDateTime'],
                'isMain'        => $newsData['isMain'],
                'title'         => $newsData['title'],
                'srcUrl'        => $newsData['srcUrl'],
                'summary'       => $newsData['summary'],
                'body'          => $newsData['body'],
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
     */
    public function createNewsSummaryNonAscii(NewsTester $i): void
    {
        $i->wantTo('P [News] {поле summary}:
        Передаем: строка с рандомными non-ascii символами.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $preconditionData = $i->preconditionForNews();
        $newsData         = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => sqs('Test. Title'),
            'summary'    => $i->getRandomString(10, DataTypesValueHelper::NON_ASCII),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
        $i->seeResponseCodeIs(HttpCode::OK);
        $newsData = $i->grabOneFromNewsTable($newsData);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'id'            => $newsData['id'],
                'newsTypeId'    => $newsData['newsTypeId'],
                'isActive'      => $newsData['isActive'],
                'publicDate'    => $newsData['publicDate'],
                'publicTime'    => $newsData['publicTime'],
                'closeDateTime' => $newsData['closeDateTime'],
                'isMain'        => $newsData['isMain'],
                'title'         => $newsData['title'],
                'srcUrl'        => $newsData['srcUrl'],
                'summary'       => $newsData['summary'],
                'body'          => $newsData['body'],
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
     */
    public function createNewsSummarySpecialChars(NewsTester $i): void
    {
        $i->wantTo('P [News] {поле summary}:
        Передаем: набор рандомных спецсимволов.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $preconditionData = $i->preconditionForNews();
        $newsData         = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => sqs('Test. Title'),
            'summary'    => $i->getRandomString(10, DataTypesValueHelper::CHARACTERS),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
        $i->seeResponseCodeIs(HttpCode::OK);
        $newsData = $i->grabOneFromNewsTable($newsData);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'id'            => $newsData['id'],
                'newsTypeId'    => $newsData['newsTypeId'],
                'isActive'      => $newsData['isActive'],
                'publicDate'    => $newsData['publicDate'],
                'publicTime'    => $newsData['publicTime'],
                'closeDateTime' => $newsData['closeDateTime'],
                'isMain'        => $newsData['isMain'],
                'title'         => $newsData['title'],
                'srcUrl'        => $newsData['srcUrl'],
                'summary'       => $newsData['summary'],
                'body'          => $newsData['body'],
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
    public function createNewsSummaryArray(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле summary}:
        Передаем: значение типа array.
        Ожидаем: ошибку с пояснением -Значение «Summary» должно быть строкой.');
        $preconditionData = $i->preconditionForNews();
        $newsData         = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => sqs('Test. Title'),
            'summary'    => ['array'],
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
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
    public function createNewsSummaryToSqlQuery(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле summary}:
        Передаем: корректный SQL запрос.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $preconditionData = $i->preconditionForNews();
        $newsData         = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => sqs('Test. Title'),
            'summary'    => DataTypesValueHelper::SQL_QUERY,
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
        $i->seeResponseCodeIs(HttpCode::OK);
        $newsData = $i->grabOneFromNewsTable($newsData);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'id'            => $newsData['id'],
                'newsTypeId'    => $newsData['newsTypeId'],
                'isActive'      => $newsData['isActive'],
                'publicDate'    => $newsData['publicDate'],
                'publicTime'    => $newsData['publicTime'],
                'closeDateTime' => $newsData['closeDateTime'],
                'isMain'        => $newsData['isMain'],
                'title'         => $newsData['title'],
                'srcUrl'        => $newsData['srcUrl'],
                'summary'       => $newsData['summary'],
                'body'          => $newsData['body'],
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
    public function createNewsIsActiveBool(NewsTester $i): void
    {
        $i->wantTo('P [News] {поле IsActive}: тип tinyInt(1)
        Передаем: значение true.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $preconditionData     = $i->preconditionForNews();
        $newsData             = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => sqs('Test. Title'),
            'summary'    => sqs('Test. Summary.'),
        ];
        $newsData['isActive'] = true;

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
        $i->seeResponseCodeIs(HttpCode::OK);
        $newsData = $i->grabOneFromNewsTable($newsData);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'id'            => $newsData['id'],
                'newsTypeId'    => $newsData['newsTypeId'],
                'isActive'      => $newsData['isActive'],
                'publicDate'    => $newsData['publicDate'],
                'publicTime'    => $newsData['publicTime'],
                'closeDateTime' => $newsData['closeDateTime'],
                'isMain'        => $newsData['isMain'],
                'title'         => $newsData['title'],
                'srcUrl'        => $newsData['srcUrl'],
                'summary'       => $newsData['summary'],
                'body'          => $newsData['body'],
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
    public function createNewsIsActiveOverMaxLogicInteger(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле IsActive}: тип tinyInt(1)
        Передаем: значение random_int(2, 300). 
        Ожидаем: ошибку с пояснением - Значение «Is Active» должно быть равно «1» или «0».');
        $preconditionData     = $i->preconditionForNews();
        $newsData             = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => sqs('Test. Title'),
            'summary'    => sqs('Test. Summary.'),
        ];
        $newsData['isActive'] = 2;

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
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
    public function createNewsIsActiveString(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле IsActive}: тип tinyInt(1)
        Передаем: значение типа string.
        Ожидаем: ошибку с пояснением - Значение «Is Active» должно быть равно «1» или «0».');
        $preconditionData     = $i->preconditionForNews();
        $newsData             = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => sqs('Test. Title'),
            'summary'    => sqs('Test. Summary.'),
        ];
        $newsData['isActive'] = sqs('test');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);

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
    public function createNewsIsActiveArray(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле IsActive}: тип tinyInt(1)
        Передаем: значение типа array.
        Ожидаем: ошибку с пояснением - Значение «Is Active» должно быть равно «1» или «0».');
        $preconditionData     = $i->preconditionForNews();
        $newsData             = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => sqs('Test. Title'),
            'summary'    => sqs('Test. Summary.'),
        ];
        $newsData['isActive'] = ['array'];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
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
    public function createNewsPublicDateOverMaxMonth(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле publicDate}: тип date YYYY-MM-DD
        Передаем: превышение максимального значения месяца.
        Ожидаем: ошибка с пояснением - Неверный формат значения «Public Date».');
        $newsData = [
            'newsTypeId' => $i->createNewsType()['id'],
            'title'      => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'summary'    => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'publicDate' => '2020-13-30',
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
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
    public function createNewsPublicDateOverMaxDay(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле publicDate}: тип date YYYY-MM-DD
        Передаем: превышение максимального значения дня.
        Ожидаем: ошибка с пояснением - Неверный формат значения «Public Date».');
        $i->loginAsAdmin();
        $newsData = [
            'newsTypeId' => $i->createNewsType()['id'],
            'title'      => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'summary'    => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'publicDate' => '2020-11-31',
        ];

        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);

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
    public function createNewsPublicDateMinDataValue(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле publicDate}: тип date YYYY-MM-DD
        Передаем: минимальное значение типа данных. 
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $i->loginAsAdmin();
        $newsData = [
            'newsTypeId' => $i->createNewsType()['id'],
            'title'      => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'summary'    => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'publicDate' => '0001-01-01',
        ];

        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
        $i->seeResponseCodeIs(HttpCode::OK);
        $newsData = $i->grabOneFromNewsTable($newsData);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'id'            => $newsData['id'],
                'newsTypeId'    => $newsData['newsTypeId'],
                'isActive'      => $newsData['isActive'],
                'publicDate'    => $newsData['publicDate'],
                'publicTime'    => $newsData['publicTime'],
                'closeDateTime' => $newsData['closeDateTime'],
                'isMain'        => $newsData['isMain'],
                'title'         => $newsData['title'],
                'srcUrl'        => $newsData['srcUrl'],
                'summary'       => $newsData['summary'],
                'body'          => $newsData['body'],
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
    public function createNewsPublicDateLessMinData(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле publicDate}: тип date YYYY-MM-DD
        Передаем: все нули 0000-00-00.
        Ожидаем: ошибка с пояснением - Неверный формат значения «Public Date».');
        $newsData = [
            'newsTypeId' => $i->createNewsType()['id'],
            'title'      => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'summary'    => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'publicDate' => '0000-00-00',
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
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
    public function createNewsPublicPatternMonthDayOneSymbol(NewsTester $i): void
    {
        $i->wantTo('P [News] {поле publicDate}: тип date YYYY-MM-DD
        Передаем: корректная дата с односимвольным значением месяца и дня.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи, в БД сохраняется с добавлением нуля.');
        $newsData = [
            'newsTypeId' => $i->createNewsType()['id'],
            'title'      => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'summary'    => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'publicDate' => '2020-3-1',
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeInNewsTable($newsData);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'newsTypeId' => $newsData['newsTypeId'],
                'title'      => $newsData['title'],
                'summary'    => $newsData['summary'],
                'publicDate' => $newsData['publicDate'],
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
    public function createNewsPublicDateToInt(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле publicDate}: тип date YYYY-MM-DD
        Передаем: значение типа int. 
        Ожидаем: ошибку с пояснением - Неверный формат значения «Public Date».');
        $newsData = [
            'newsTypeId' => $i->createNewsType()['id'],
            'title'      => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'summary'    => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'publicDate' => 2020,
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
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
    public function createNewsPublicDateToArray(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле publicDate}: тип date YYYY-MM-DD
        Передаем: значение типа array. 
        Ожидаем: ошибку с пояснением - Неверный формат значения «Public Date».');
        $newsData = [
            'newsTypeId' => $i->createNewsType()['id'],
            'title'      => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'summary'    => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'publicDate' => ['2020-03-15'],
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
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
    public function createNewsPublicDateToString(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле publicDate}: тип date YYYY-MM-DD
        Передаем: рандомный текст в строке. 
        Ожидаем: ошибку с пояснением - Неверный формат значения «Public Date».');
        $i->loginAsAdmin();
        $newsData = [
            'newsTypeId' => $i->createNewsType()['id'],
            'title'      => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'summary'    => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'publicDate' => $i->getRandomString(10, DataTypesValueHelper::ASCII),
        ];

        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
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
    public function createNewsPublicTimeMaxTimeValue(NewsTester $i): void
    {
        $i->wantTo('P [News] {поле publicTime}: тип time HH:MM:SS
        Передаем: максимальное значение типа time. 
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $newsData = [
            'newsTypeId' => $i->createNewsType()['id'],
            'title'      => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'summary'    => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'publicTime' => '23:59:59',
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
        $i->seeResponseCodeIs(HttpCode::OK);
        $newsData = $i->grabOneFromNewsTable($newsData);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'id'            => $newsData['id'],
                'newsTypeId'    => $newsData['newsTypeId'],
                'isActive'      => $newsData['isActive'],
                'publicDate'    => $newsData['publicDate'],
                'publicTime'    => $newsData['publicTime'],
                'closeDateTime' => $newsData['closeDateTime'],
                'isMain'        => $newsData['isMain'],
                'title'         => $newsData['title'],
                'srcUrl'        => $newsData['srcUrl'],
                'summary'       => $newsData['summary'],
                'body'          => $newsData['body'],
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
    public function createNewsPublicTimeOverMaxHour(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле publicTime}: тип time HH:MM:SS
        Передаем: больше максимального значения в часах.
        Ожидаем: ошибка с пояснением - Неверный формат значения «Public Time».');
        $newsData = [
            'newsTypeId' => $i->createNewsType()['id'],
            'title'      => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'summary'    => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'publicTime' => '24:00:01',
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);

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
    public function createNewsPublicTimeOverMaxMinute(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле publicTime}: тип time HH:MM:SS
        Передаем: больше максимального значения в минутах.
        Ожидаем: ошибка с пояснением - Неверный формат значения «Public Time».');
        $newsData = [
            'newsTypeId' => $i->createNewsType()['id'],
            'title'      => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'summary'    => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'publicTime' => '23:60:01',
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
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
    public function createNewsPublicTimeOverMaxSeconds(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле publicTime}: тип time HH:MM:SS
        Передаем: больше максимального значения в секундах.
        Ожидаем: ошибка с пояснением - Неверный формат значения «Public Time».');
        $newsData = [
            'newsTypeId' => $i->createNewsType()['id'],
            'title'      => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'summary'    => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'publicTime' => '23:50:60',
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
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
    public function createNewsPublicTimeLessMinTimeValue(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле publicTime}: тип time HH:MM:SS
        Передаем: отрицательное время. 
        Ожидаем: ошибка с пояснением - Неверный формат значения «Public Time».');
        $newsData = [
            'newsTypeId' => $i->createNewsType()['id'],
            'title'      => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'summary'    => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'publicTime' => '-00:00:01',
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
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
    public function createNewsPublicTimePatternHourOneSymbol(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле publicTime}: тип time HH:MM:SS
        Передаем: корректное время с односимвольным значением часа.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи, в БД сохраняется с добавлением нуля.');
        $newsData = [
            'newsTypeId' => $i->createNewsType()['id'],
            'title'      => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'summary'    => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'publicTime' => '4:02:01',
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);

        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeInNewsTable($newsData);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'newsTypeId' => $newsData['newsTypeId'],
                'title'      => $newsData['title'],
                'summary'    => $newsData['summary'],
                'publicTime' => $newsData['publicTime'],
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
    public function createNewsPublicTimePatternMinuteOneSymbol(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле publicTime}: тип time HH:MM:SS
        Передаем: корректное время с односимвольным значением минут.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи, в БД сохраняется с добавлением нуля.');
        $i->loginAsAdmin();
        $newsData = [
            'newsTypeId' => $i->createNewsType()['id'],
            'title'      => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'summary'    => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'publicTime' => '14:6:01',
        ];

        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
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
    public function createNewsPublicTimePatternSecondsOneSymbol(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле publicTime}: тип time HH:MM:SS
        Передаем: корректное время с односимвольным значением секунд.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи, в БД сохраняется с добавлением нуля.');
        $newsData = [
            'newsTypeId' => $i->createNewsType()['id'],
            'title'      => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'summary'    => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'publicTime' => '14:26:1',
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
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
    public function createNewsPublicTimeToInt(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле publicTime}: тип time HH:MM:SS
        Передаем: значение типа int. 
        Ожидаем: ошибка с пояснением - Неверный формат значения «Public Time».');
        $newsData = [
            'newsTypeId' => $i->createNewsType()['id'],
            'title'      => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'summary'    => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'publicTime' => 125536,
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
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
    public function createNewsPublicTimeToArray(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле publicTime}: тип time HH:MM:SS
        Передаем: значение типа array. 
        Ожидаем: ошибка с пояснением - Неверный формат значения «Public Time».');
        $newsData = [
            'newsTypeId' => $i->createNewsType()['id'],
            'title'      => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'summary'    => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'publicTime' => ['22:11:56'],
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
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
    public function createNewsPublicTimeToString(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле publicTime}: тип time HH:MM:SS
        Передаем: строку с рандомным текстом.
        Ожидаем: ошибка с пояснением - Неверный формат значения «Public Time».');
        $newsData = [
            'newsTypeId' => $i->createNewsType()['id'],
            'title'      => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'summary'    => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'publicTime' => $i->getRandomString(10, DataTypesValueHelper::LATIN_UPPER),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
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
    public function createNewsCloseDateTimeWrongFormat(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле closeDateTime}: тип dateTime YYYY-MM-DD HH:MM:SS
        Передаем: дата и время корректного формата без пробела между датой и временем.
        Ожидаем: ошибка с пояснением - Неверный формат значения «Close Date Time».');
        $newsData = [
            'newsTypeId'    => $i->createNewsType()['id'],
            'title'         => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'summary'       => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'closeDateTime' => '2001-02-22/08:37:34 ',
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
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
    public function createNewsCloseDateTimeMinValue(NewsTester $i): void
    {
        $i->wantTo('P [News] {поле closeDateTime}: тип dateTime YYYY-MM-DD HH:MM:SS
        Передаем: минимальное значение типа.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $i->loginAsAdmin();
        $newsData = [
            'newsTypeId'    => $i->createNewsType()['id'],
            'title'         => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'summary'       => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'closeDateTime' => '0000-01-01 00:00:00',
        ];

        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeInNewsTable($newsData);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'newsTypeId'    => $newsData['newsTypeId'],
                'title'         => $newsData['title'],
                'summary'       => $newsData['summary'],
                'closeDateTime' => $newsData['closeDateTime'],
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
    public function createNewsCloseDateTimeOverMaxMonth(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле closeDateTime}: тип dateTime YYYY-MM-DD HH:MM:SS
        Передаем: превышение максимального значения месяца. 
        Ожидаем: ошибка с пояснением - Неверный формат значения «Close Date Time».');
        $newsData = [
            'newsTypeId'    => $i->createNewsType()['id'],
            'title'         => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'summary'       => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'closeDateTime' => '2002-13-10 22:11:12',
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
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
    public function createNewsCloseDateTimeOverMaxDay(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле closeDateTime}: тип dateTime YYYY-MM-DD HH:MM:SS
        Передаем: превышение максимального значения дня. 
        Ожидаем: ошибка с пояснением - Неверный формат значения «Close Date Time».');
        $newsData = [
            'newsTypeId'    => $i->createNewsType()['id'],
            'title'         => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'summary'       => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'closeDateTime' => '2020-11-31 22:11:16',
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
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
    public function createNewsCloseDateTimeOverMaxHour(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле closeDateTime}: тип dateTime YYYY-MM-DD HH:MM:SS
        Передаем: превышение максимального значения часа. 
        Ожидаем: ошибка с пояснением - Неверный формат значения «Close Date Time».');
        $newsData = [
            'newsTypeId'    => $i->createNewsType()['id'],
            'title'         => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'summary'       => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'closeDateTime' => '2020-10-22 24:11:16',
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);

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
    public function createNewsCloseDateTimeOverMaxMinute(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле closeDateTime}: тип dateTime YYYY-MM-DD HH:MM:SS
        Передаем: превышение максимального значения минут. 
        Ожидаем: ошибка с пояснением - Неверный формат значения «Close Date Time».');
        $newsData = [
            'newsTypeId'    => $i->createNewsType()['id'],
            'title'         => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'summary'       => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'closeDateTime' => '2020-10-22 21:60:16',
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
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
    public function createNewsCloseDateTimeOverMaxSeconds(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле closeDateTime}: тип dateTime YYYY-MM-DD HH:MM:SS
        Передаем: превышение максимального значения секунд. 
        Ожидаем: ошибка с пояснением - Неверный формат значения «Close Date Time».');
        $newsData = [
            'newsTypeId'    => $i->createNewsType()['id'],
            'title'         => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'summary'       => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'closeDateTime' => '2020-10-22 21:12:60',
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
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
    public function createNewsCloseDateTimePatternOneSymbol(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле closeDateTime}: тип dateTime YYYY-MM-DD HH:MM:SS
        Передаем: корректные дата и время c односимвольным указанием месяца.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи, в БД сохраняется с добавлением нуля.');
        $newsData = [
            'newsTypeId'    => $i->createNewsType()['id'],
            'title'         => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'summary'       => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'closeDateTime' => '2020-3-1 1:10:12',
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);

        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeInNewsTable($newsData);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'newsTypeId'    => $newsData['newsTypeId'],
                'title'         => $newsData['title'],
                'summary'       => $newsData['summary'],
                'closeDateTime' => $newsData['closeDateTime'],
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
    public function createNewsCloseDateToInt(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле closeDateTime}: тип dateTime YYYY-MM-DD HH:MM:SS
        Передаем: значение типа int.
        Ожидаем: ошибка с пояснением - Неверный формат значения «Close Date Time».');
        $newsData = [
            'newsTypeId'    => $i->createNewsType()['id'],
            'title'         => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'summary'       => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'closeDateTime' => 2222,
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
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
    public function createNewsCloseDateToArray(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле closeDateTime}: тип dateTime YYYY-MM-DD HH:MM:SS
        Передаем: значение типа array.
        Ожидаем: ошибка с пояснением - Неверный формат значения «Close Date Time».');
        $newsData = [
            'newsTypeId'    => $i->createNewsType()['id'],
            'title'         => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'summary'       => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'closeDateTime' => ['2222-11-10 22:12:12'],
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
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
    public function createNewsCloseDateToString(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле closeDateTime}: тип dateTime YYYY-MM-DD HH:MM:SS
        Передаем: строку с рандомным набором ASCII символов.
        Ожидаем: ошибка с пояснением - Неверный формат значения «Close Date Time».');
        $newsData = [
            'newsTypeId'    => $i->createNewsType()['id'],
            'title'         => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'summary'       => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'closeDateTime' => $i->getRandomString(10, DataTypesValueHelper::ASCII),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
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
    public function createNewsCloseDateToSql(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле closeDateTime}: тип dateTime YYYY-MM-DD HH:MM:SS
        Передаем: корректный sql запрос.
        Ожидаем: ошибка с пояснением - Неверный формат значения «Close Date Time».');
        $newsData = [
            'newsTypeId'    => $i->createNewsType()['id'],
            'title'         => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'summary'       => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            'closeDateTime' => DataTypesValueHelper::SQL_QUERY,
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
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
    public function createNewsIsMainToInteger(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле IsMain}: тип tinyInt(1)
        Передаем: значение random_int(2, 300).
        Ожидаем: ошибку с пояснением - Значение «Is Main» должно быть равно «1» или «0».');
        $preconditionData = $i->preconditionForNews();
        $newsData         = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => sqs('Test. Title'),
            'summary'    => sqs('Test. Summary.'),
        ];
        $newsData['isMain'] = random_int(2, 300);

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
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
    public function createNewsIsMainToString(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле IsMain}: тип tinyInt(1)
        Передаем: строка с рандомными ASCII символами.
        Ожидаем: ошибку с пояснением - Значение «Is Main» должно быть равно «1» или «0».');
        $preconditionData = $i->preconditionForNews();
        $newsData         = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => sqs('Test. Title'),
            'summary'    => sqs('Test. Summary.'),
        ];
        $newsData['isMain'] = sqs('test');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
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
    public function createNewsIsMainToFloat(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле IsMain}: тип tinyInt(1)
        Передаем: значение типа float. 
        Ожидаем: ошибку с пояснением - Значение «Is Main» должно быть равно «1» или «0».');
        $preconditionData = $i->preconditionForNews();
        $newsData         = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => sqs('Test. Title'),
            'summary'    => sqs('Test. Summary.'),
        ];
        $newsData['isMain'] = 0.123;

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
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
    public function createNewsSrcUrlMaxPhysicLength(NewsTester $i): void
    {
        $i->wantTo('P [News] {поле srcUrl}: varchar(255)
        Передаем: максимальная длина значения типа данных.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $preconditionData = $i->preconditionForNews();
        $newsData         = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => sqs('Test. Title'),
            'summary'    => sqs('Test. Summary.'),
        ];
        $newsData['srcUrl'] = $i->getRandomString(255, DataTypesValueHelper::ASCII);

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
        $i->seeResponseCodeIs(HttpCode::OK);
        $newsData = $i->grabOneFromNewsTable($newsData);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'id'            => $newsData['id'],
                'newsTypeId'    => $newsData['newsTypeId'],
                'isActive'      => $newsData['isActive'],
                'publicDate'    => $newsData['publicDate'],
                'publicTime'    => $newsData['publicTime'],
                'closeDateTime' => $newsData['closeDateTime'],
                'isMain'        => $newsData['isMain'],
                'title'         => $newsData['title'],
                'srcUrl'        => $newsData['srcUrl'],
                'summary'       => $newsData['summary'],
                'body'          => $newsData['body'],
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
     */
    public function createNewsSrcUrlOverMaxPhysicLength(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле srcUrl}: varchar(255)
        Передаем: превышение максимальной длины значения типа данных.
        Ожидаем: ошибка с пояснением - Значение «Src Url» должно содержать максимум 255 символов.');
        $preconditionData = $i->preconditionForNews();
        $newsData         = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => sqs('Test. Title'),
            'summary'    => sqs('Test. Summary.'),
        ];
        $newsData['srcUrl'] = $i->getRandomString(256, DataTypesValueHelper::ASCII);

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
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
    public function createNewsSrcUrlHieroglyphs(NewsTester $i): void
    {
        $i->wantTo('P [News] {поле srcUrl}: varchar(255)
        Передаем: строку с рандомным набором японских иероглифоф.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $preconditionData = $i->preconditionForNews();
        $newsData         = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => sqs('Test. Title'),
            'summary'    => sqs('Test. Summary.'),
        ];
        $newsData['srcUrl'] = $i->getRandomString(15, DataTypesValueHelper::JP_HIEROGLYPHS);

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
        $i->seeResponseCodeIs(HttpCode::OK);
        $newsData = $i->grabOneFromNewsTable($newsData);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'id'            => $newsData['id'],
                'newsTypeId'    => $newsData['newsTypeId'],
                'isActive'      => $newsData['isActive'],
                'publicDate'    => $newsData['publicDate'],
                'publicTime'    => $newsData['publicTime'],
                'closeDateTime' => $newsData['closeDateTime'],
                'isMain'        => $newsData['isMain'],
                'title'         => $newsData['title'],
                'srcUrl'        => $newsData['srcUrl'],
                'summary'       => $newsData['summary'],
                'body'          => $newsData['body'],
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
     */
    public function createNewsSrcUrlNonAscii(NewsTester $i): void
    {
        $i->wantTo('P [News] {поле srcUrl}: varchar(255)
        Передаем: строку с рандомным набором  non-ascii символов.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $preconditionData = $i->preconditionForNews();
        $newsData         = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => sqs('Test. Title'),
            'summary'    => sqs('Test. Summary.'),
        ];
        $newsData['srcUrl'] = $i->getRandomString(15, DataTypesValueHelper::NON_ASCII);

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
        $i->seeResponseCodeIs(HttpCode::OK);
        $newsData = $i->grabOneFromNewsTable($newsData);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'id'            => $newsData['id'],
                'newsTypeId'    => $newsData['newsTypeId'],
                'isActive'      => $newsData['isActive'],
                'publicDate'    => $newsData['publicDate'],
                'publicTime'    => $newsData['publicTime'],
                'closeDateTime' => $newsData['closeDateTime'],
                'isMain'        => $newsData['isMain'],
                'title'         => $newsData['title'],
                'srcUrl'        => $newsData['srcUrl'],
                'summary'       => $newsData['summary'],
                'body'          => $newsData['body'],
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
     */
    public function createNewsSrcUrlSpecialChars(NewsTester $i): void
    {
        $i->wantTo('P [News] {поле srcUrl}: varchar(255)
        Передаем: строку с рандомным набором спецсимволов.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $preconditionData = $i->preconditionForNews();
        $newsData         = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => sqs('Test. Title'),
            'summary'    => sqs('Test. Summary.'),
        ];
        $newsData['srcUrl'] = $i->getRandomString(15, DataTypesValueHelper::CHARACTERS);

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
        $i->seeResponseCodeIs(HttpCode::OK);
        $newsData = $i->grabOneFromNewsTable($newsData);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'id'            => $newsData['id'],
                'newsTypeId'    => $newsData['newsTypeId'],
                'isActive'      => $newsData['isActive'],
                'publicDate'    => $newsData['publicDate'],
                'publicTime'    => $newsData['publicTime'],
                'closeDateTime' => $newsData['closeDateTime'],
                'isMain'        => $newsData['isMain'],
                'title'         => $newsData['title'],
                'srcUrl'        => $newsData['srcUrl'],
                'summary'       => $newsData['summary'],
                'body'          => $newsData['body'],
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
    public function createNewsSrcUrlToArray(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле srcUrl}: varchar(255)
        Передаем: значение типа array.
        Ожидаем: ошибку с пояснением - Значение «Src Url» должно быть строкой.');
        $preconditionData = $i->preconditionForNews();
        $newsData         = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => sqs('Test. Title'),
            'summary'    => sqs('Test. Summary.'),
        ];
        $newsData['srcUrl'] = ['array'];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
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
    public function createNewsSrcUrlToSqlQuery(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле srcUrl}: varchar(255)
        Передаем: корректный SQL запрос.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $preconditionData = $i->preconditionForNews();
        $newsData         = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => sqs('Test. Title'),
            'summary'    => sqs('Test. Summary.'),
        ];
        $newsData['srcUrl'] = DataTypesValueHelper::SQL_QUERY;

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
        $i->seeResponseCodeIs(HttpCode::OK);
        $newsData = $i->grabOneFromNewsTable($newsData);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'id'            => $newsData['id'],
                'newsTypeId'    => $newsData['newsTypeId'],
                'isActive'      => $newsData['isActive'],
                'publicDate'    => $newsData['publicDate'],
                'publicTime'    => $newsData['publicTime'],
                'closeDateTime' => $newsData['closeDateTime'],
                'isMain'        => $newsData['isMain'],
                'title'         => $newsData['title'],
                'srcUrl'        => $newsData['srcUrl'],
                'summary'       => $newsData['summary'],
                'body'          => $newsData['body'],
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
     */
    public function createNewsBodyMaxPhysicLength(NewsTester $i): void
    {
        $i->wantTo('P [News] {поле body}: тип text
        Передаем: максимальная длина значения типа данных.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $preconditionData = $i->preconditionForNews();
        $newsData         = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => sqs('Test. Title'),
            'summary'    => sqs('Test. Summary.'),
        ];
        $newsData['body'] = $i->getRandomString(65535, DataTypesValueHelper::ASCII);

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
        $i->seeResponseCodeIs(HttpCode::OK);
        $newsData = $i->grabOneFromNewsTable($newsData);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'id'            => $newsData['id'],
                'newsTypeId'    => $newsData['newsTypeId'],
                'isActive'      => $newsData['isActive'],
                'publicDate'    => $newsData['publicDate'],
                'publicTime'    => $newsData['publicTime'],
                'closeDateTime' => $newsData['closeDateTime'],
                'isMain'        => $newsData['isMain'],
                'title'         => $newsData['title'],
                'srcUrl'        => $newsData['srcUrl'],
                'summary'       => $newsData['summary'],
                'body'          => $newsData['body'],
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
     */
    public function createNewsBodyOverMaxPhysicLength(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле body}: тип text
        Передаем: превышение максимальной длины значения типа данных.
        Ожидаем: ошибка с пояснением - Значение «Body» должно содержать максимум 65 535 символов.');
        $preconditionData = $i->preconditionForNews();
        $newsData         = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => sqs('Test. Title'),
            'summary'    => sqs('Test. Summary.'),
        ];
        $newsData['body'] = $i->getRandomString(65536, DataTypesValueHelper::ASCII);

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
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
    public function createNewsBodyHieroglyphs(NewsTester $i): void
    {
        $i->wantTo('P [News] {поле body}:
        Передаем: строка с рандомным набором японских иероглифоф.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $preconditionData = $i->preconditionForNews();
        $newsData         = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => sqs('Test. Title'),
            'summary'    => sqs('Test. Summary.'),
        ];
        $newsData['body'] = $i->getRandomString(10, DataTypesValueHelper::JP_HIEROGLYPHS);

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
        $i->seeResponseCodeIs(HttpCode::OK);
        $newsData = $i->grabOneFromNewsTable($newsData);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'id'            => $newsData['id'],
                'newsTypeId'    => $newsData['newsTypeId'],
                'isActive'      => $newsData['isActive'],
                'publicDate'    => $newsData['publicDate'],
                'publicTime'    => $newsData['publicTime'],
                'closeDateTime' => $newsData['closeDateTime'],
                'isMain'        => $newsData['isMain'],
                'title'         => $newsData['title'],
                'srcUrl'        => $newsData['srcUrl'],
                'summary'       => $newsData['summary'],
                'body'          => $newsData['body'],
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
     */
    public function createNewsBodyNonAscii(NewsTester $i): void
    {
        $i->wantTo('P [News] {поле body}:
        Передаем: строка с рандомным набором non-ascii символов.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $preconditionData = $i->preconditionForNews();
        $newsData         = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => sqs('Test. Title'),
            'summary'    => sqs('Test. Summary.'),
        ];
        $newsData['body'] = $i->getRandomString(10, DataTypesValueHelper::NON_ASCII);

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
        $i->seeResponseCodeIs(HttpCode::OK);
        $newsData = $i->grabOneFromNewsTable($newsData);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'id'            => $newsData['id'],
                'newsTypeId'    => $newsData['newsTypeId'],
                'isActive'      => $newsData['isActive'],
                'publicDate'    => $newsData['publicDate'],
                'publicTime'    => $newsData['publicTime'],
                'closeDateTime' => $newsData['closeDateTime'],
                'isMain'        => $newsData['isMain'],
                'title'         => $newsData['title'],
                'srcUrl'        => $newsData['srcUrl'],
                'summary'       => $newsData['summary'],
                'body'          => $newsData['body'],
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
     */
    public function createNewsBodySpecialChars(NewsTester $i): void
    {
        $i->wantTo('P [News] {поле body}:
        Передаем: строка с рандомным набором спецсимволов.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $preconditionData = $i->preconditionForNews();
        $newsData         = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => sqs('Test. Title'),
            'summary'    => sqs('Test. Summary.'),
        ];
        $newsData['body'] = $i->getRandomString(10, DataTypesValueHelper::CHARACTERS);

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
        $i->seeResponseCodeIs(HttpCode::OK);
        $newsData = $i->grabOneFromNewsTable($newsData);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'id'            => $newsData['id'],
                'newsTypeId'    => $newsData['newsTypeId'],
                'isActive'      => $newsData['isActive'],
                'publicDate'    => $newsData['publicDate'],
                'publicTime'    => $newsData['publicTime'],
                'closeDateTime' => $newsData['closeDateTime'],
                'isMain'        => $newsData['isMain'],
                'title'         => $newsData['title'],
                'srcUrl'        => $newsData['srcUrl'],
                'summary'       => $newsData['summary'],
                'body'          => $newsData['body'],
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
    public function createNewsBodyToArray(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле body}:
        Передаем: значение типа array.
        Ожидаем: ошибку с пояснением - Значение «Body» должно быть строкой.');
        $preconditionData = $i->preconditionForNews();
        $newsData         = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => sqs('Test. Title'),
            'summary'    => sqs('Test. Summary.'),
        ];
        $newsData['body'] = ['array'];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
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
     */
    public function createNewsBodyToSqlQuery(NewsTester $i): void
    {
        $i->wantTo('N [News] {поле body}:
        Передаем: корректный SQL запрос.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $preconditionData = $i->preconditionForNews();
        $newsData         = [
            'newsTypeId' => $preconditionData['newsTypeData']['id'],
            'title'      => sqs('Test. Title'),
            'summary'    => sqs('Test. Summary.'),
        ];
        $newsData['body'] = DataTypesValueHelper::SQL_QUERY;

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsData);
        $i->seeResponseCodeIs(HttpCode::OK);
        $newsData = $i->grabOneFromNewsTable($newsData);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'id'            => $newsData['id'],
                'newsTypeId'    => $newsData['newsTypeId'],
                'isActive'      => $newsData['isActive'],
                'publicDate'    => $newsData['publicDate'],
                'publicTime'    => $newsData['publicTime'],
                'closeDateTime' => $newsData['closeDateTime'],
                'isMain'        => $newsData['isMain'],
                'title'         => $newsData['title'],
                'srcUrl'        => $newsData['srcUrl'],
                'summary'       => $newsData['summary'],
                'body'          => $newsData['body'],
            ],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }
}
