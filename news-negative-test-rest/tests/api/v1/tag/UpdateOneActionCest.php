<?php

declare(strict_types = 1);

namespace UserstoryTemp\NewsNegativeTestRest\tests\api\v1\tag;

use Codeception\Util\HttpCode;
use UserstoryTemp\NewsNegativeTest\tests\support\Helpers\DataTypesValueHelper;
use UserstoryTemp\NewsNegativeTestRest\tests\support\Step\Api\TagTester;

/**
 * Класс тестирования REST API: обновление сущности "Тэги".
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
    public static $URI = 'v1/tag/';

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
                'name'       => 'string',
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
     * @param TagTester $i Объект текущего тестировщика.
     *
     * @return void
     */
    public function _after(TagTester $i): void
    {
    }

    /**
     * Метод для предварительных инициализаций перед тестами.
     *
     * @param TagTester $i Объект текущего тестировщика.
     *
     * @return void
     */
    public function _before(TagTester $i): void
    {
        $i->flushCache();
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param TagTester $i Объект текущего тестировщика.
     *
     * @group L2
     * @group authorization
     *
     * @return void
     */
    public function updateTagWithOutAuth(TagTester $i): void
    {
        $i->wantTo('N [Tag]: 
        Передаем: запрос на обновление сущности без авторизации.
        Ожидаем: ошибка с пояснением - Доступ запрещен.');
        $tagData = [
            'name' => sqs('name'),
        ];

        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $tagData);

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
        $i->dontSeeInTagTable($tagData);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param TagTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function updateTagWithoutRequiredFields(TagTester $i): void
    {
        $i->wantTo('N [Tag]: 
        Передаем: Запрос без указания параметров.
        Ожидаем: ошибка с пояснением - Необходимо заполнить «name».');
        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'name' => 'Необходимо заполнить «Name».',
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
     * @param TagTester $i Объект текущего тестировщика.
     *
     * @group L1
     * @group functional
     *
     * @return void
     */
    public function updateTagNameCorrect(TagTester $i): void
    {
        $i->wantTo('P [Tag] {поле name}: 
        Передаем: Запрос на обновление сущности со сгенерированными корректными случайными данными.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $tagData = $i->createTag();
        $request = [
            'name' => sqs('name'),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $tagData['id'], $request);

        $i->seeResponseCodeIs(HttpCode::OK);
        $tagData = $i->grabOneFromTagTable($request);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'id'   => $tagData['id'],
                'name' => $tagData['name'],
            ],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param TagTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function updateTagNameUnique(TagTester $i): void
    {
        $i->wantTo('N [Tag] {поле name}: 
        Передаем: cуществующее в таблице значение поля.
        Ожидаем: ошибку с пояснением - Запись с таким значением уже существует в таблице.');
        $tagData = $i->createTag();
        $request = [
            'name' => $tagData['name'],
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $tagData['id'], $request);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'name' => 'Запись с таким значением уже существует в таблице.',
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
     * @param TagTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function updateTagNameMaxPhysicLength(TagTester $i): void
    {
        $i->wantTo('P [Tag] {поле name}: тип varchar(255)
        Передаем: максимальная длина значения типа данных.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $tagData = $i->createTag();
        $request = [
            'name' => $i->getRandomString(255, DataTypesValueHelper::ASCII),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $tagData['id'], $request);

        $i->seeResponseCodeIs(HttpCode::OK);
        $tagData = $i->grabOneFromTagTable($request);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'id'   => $tagData['id'],
                'name' => $tagData['name'],
            ],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param TagTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function updateTagNameOverMaxPhysicLength(TagTester $i): void
    {
        $i->wantTo('N [Tag] {поле name}: тип varchar(255)
        Передаем: превышение максимальной длины значения типа данных.
        Ожидаем: ошибка с пояснением - Значение «name» должно содержать максимум 255 символов.');
        $tagData = $i->createTag();
        $request = [
            'name' => $i->getRandomString(256, DataTypesValueHelper::ASCII),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $tagData['id'], $request);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'name' => 'Значение «Name» должно содержать максимум 255 символов.',
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
     * @param TagTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function updateTagNameHieroglyphs(TagTester $i): void
    {
        $i->wantTo('P [Tag] {поле name}: тип varchar(255)
        Передаем: строка с рандомным набором японских иероглифоф.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $tagData = $i->createTag();
        $request = [
            'name' => $i->getRandomString(255, DataTypesValueHelper::JP_HIEROGLYPHS),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $tagData['id'], $request);

        $i->seeResponseCodeIs(HttpCode::OK);
        $tagData = $i->grabOneFromTagTable($request);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'id'   => $tagData['id'],
                'name' => $tagData['name'],
            ],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param TagTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function updateTagNameNonAscii(TagTester $i): void
    {
        $i->wantTo('P [Tag] {поле name}: тип varchar(255)
        Передаем: строка с рандомным набором non-ascii символов.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $tagData = $i->createTag();
        $request = [
            'name' => $i->getRandomString(10, DataTypesValueHelper::NON_ASCII),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $tagData['id'], $request);

        $i->seeResponseCodeIs(HttpCode::OK);
        $tagData = $i->grabOneFromTagTable($request);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'id'   => $tagData['id'],
                'name' => $tagData['name'],
            ],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param TagTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function updateTagNameSpecialchars(TagTester $i): void
    {
        $i->wantTo('P [Tag] {поле name}: тип varchar(255)
        Передаем: строка с рандомным набором спецсимволов.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $tagData = $i->createTag();
        $request = [
            'name' => $i->getRandomString(10, DataTypesValueHelper::CHARACTERS),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $tagData['id'], $request);

        $i->seeResponseCodeIs(HttpCode::OK);
        $tagData = $i->grabOneFromTagTable($request);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'id'   => $tagData['id'],
                'name' => $tagData['name'],
            ],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param TagTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function updateTagNameToArray(TagTester $i): void
    {
        $i->wantTo('N [Tag] {поле name}: тип varchar(255)
        Передаем: значение типа array.
        Ожидаем:  ошибку с пояснением - Значение «name» должно быть строкой.');
        $tagData = $i->createTag();
        $request = [
            'name' => ['name'],
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $tagData['id'], $request);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'name' => 'Значение «Name» должно быть строкой.',
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
     * @param TagTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function updateTagNameToSqlQuery(TagTester $i): void
    {
        $i->wantTo('N [Tag] {поле name}: тип varchar(255)
        Передаем: корректный SQL запрос.
        Ожидаем:  положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $tagData = $i->createTag();
        $request = [
            'name' => $i->getRandomString(10, DataTypesValueHelper::SQL_QUERY),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $tagData['id'], $request);

        $i->seeResponseCodeIs(HttpCode::OK);
        $tagData = $i->grabOneFromTagTable($request);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'id'   => $tagData['id'],
                'name' => $tagData['name'],
            ],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param TagTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function updateTagNotExist(TagTester $i): void
    {
        $i->wantTo('N [Tag]
        Передаем: несуществующий в таблице тегов идентификатор.
        Ожидаем:  ошибку с пояснением - Сущность не найдена.');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->getNotExistTagId(), ['name' => sqs('name')]);

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
     * @param TagTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function updateTagOverMaxPhysicInt(TagTester $i): void
    {
        $i->wantTo('N [Tag] 
        Передаем: превышающее максимальное физическое ограничение значение идентификатора.
        Ожидаем:  ошибку с пояснением - Значение «Id» не должно превышать 2147483647.');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . DataTypesValueHelper::OVER_MAX_INT_SIGNED, ['name' => sqs('name')]);

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
     * @param TagTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function updateTagLessMinLogicInt(TagTester $i): void
    {
        $i->wantTo('N [Tag] :
        Передаем: значение 0.
        Ожидаем: ошибка с пояснением - Значение «Id» должно быть не меньше 1.');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . 0, ['name' => sqs('name')]);

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
