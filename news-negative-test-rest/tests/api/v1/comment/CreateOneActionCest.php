<?php

declare( strict_types = 1 );

namespace UserstoryTemp\NewsNegativeTestRest\tests\api\v1\comment;

use Codeception\Util\HttpCode;
use Exception;
use UserstoryTemp\NewsNegativeTest\tests\support\Helpers\DataTypesValueHelper;
use UserstoryTemp\NewsNegativeTestRest\tests\support\Step\Api\CommentTester;

/**
 * Класс тестирования REST API: создание новой сущности "Комментарии к новостям".
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
    public static $URI = 'v1/comment';

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
     * Метод проверяет позитивный кейс.
     *
     * @param CommentTester $i Объект текущего тестировщика.
     *
     * @group L2
     * @group authorization
     *
     * @return void
     */
    public function createCommentWithOutAuth(CommentTester $i): void
    {
        $i->wantTo('P [Comment]: 
            Передаем: запрос на создание сущности без авторизации.
            Ожидаем: ошибка с пояснением - Доступ запрещен.');
        $preconditionData = $i->preconditionForComment();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $commentData = [
            'body'   => 'Текст комментария',
            'newsId' => $preconditionData['newsData']['id'],
        ];
        $i->sendPOST(self::$URI, $commentData);
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
        $i->dontSeeInCommentTable($commentData);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param CommentTester $i Объект текущего тестировщика.
     *
     * @group L1
     * @group functional
     *
     * @return void
     */
    public function createCommentCorrect(CommentTester $i): void
    {
        $i->wantTo('P [Comment]: 
            Передаем: Запрос на создание сущности со сгенерированными корректными случайными данными.
            Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $preconditionData = $i->preconditionForComment();
        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $commentData = [
            'body'   => sqs('Test comment body'),
            'newsId' => $preconditionData['newsData']['id'],
        ];
        $i->sendPOST(self::$URI, $commentData);
        $i->seeResponseCodeIs(HttpCode::OK);
        $commentData = $i->grabOneFromCommentTable($commentData);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'id'     => $commentData['id'],
                'newsId' => $commentData['newsId'],
                'body'   => $commentData['body'],
            ],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param CommentTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function createCommentWithoutRequiredFields(CommentTester $i): void
    {
        $i->wantTo('N [Comment]: 
        Передаем: Запрос без указания параметров.
        Ожидаем: ошибку с пояснением о необходимости заполнить обязательные поля.');
        $i->loginAsAdmin();

        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, []);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'newsId' => 'Необходимо заполнить «News Id».',
                    ],
                ],
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'body' => 'Необходимо заполнить «Body».',
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
     * @param CommentTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function createCommentBodyMaxPhysicLength(CommentTester $i): void
    {
        $i->wantTo('P [Comment] {поле body}: тип text
        Передаем: максимальная длина значения типа данных.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $preconditionData = $i->preconditionForComment();
        $commentData      = [
            'body'   => $i->getRandomString(65535, DataTypesValueHelper::ASCII),
            'newsId' => $preconditionData['newsData']['id'],
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $commentData);
        $i->seeResponseCodeIs(HttpCode::OK);
        $commentData = $i->grabOneFromCommentTable($commentData);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'id'     => $commentData['id'],
                'newsId' => $commentData['newsId'],
                'body'   => $commentData['body'],
            ],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param CommentTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function createCommentBodyOverMaxPhysicLength(CommentTester $i): void
    {
        $i->wantTo('N [Comment] {поле body}: тип text
        Передаем: превышение максимальной длины значения типа данных.
        Ожидаем: ошибка с пояснением - Значение «Body» должно содержать максимум 65 535 символов.');
        $preconditionData = $i->preconditionForComment();
        $commentData      = [
            'body'   => $i->getRandomString(65536, DataTypesValueHelper::ASCII),
            'newsId' => $preconditionData['newsData']['id'],
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $commentData);
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
        $i->dontSeeInCommentTable($commentData);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param CommentTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function createCommentBodyHieroglyphs(CommentTester $i): void
    {
        $i->wantTo('P [Comment] {поле body}:
        Передаем: строка с рандомным набором японских иероглифоф.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $preconditionData = $i->preconditionForComment();
        $commentData      = [
            'body'   => $i->getRandomString(10, DataTypesValueHelper::JP_HIEROGLYPHS),
            'newsId' => $preconditionData['newsData']['id'],
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $commentData);
        $i->seeResponseCodeIs(HttpCode::OK);
        $commentData = $i->grabOneFromCommentTable($commentData);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'id'     => $commentData['id'],
                'newsId' => $commentData['newsId'],
                'body'   => $commentData['body'],
            ],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param CommentTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function createCommentBodyNonAscii(CommentTester $i): void
    {
        $i->wantTo('P [Comment] {поле body}:
        Передаем: строка с рандомным набором non-ascii символов.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $preconditionData = $i->preconditionForComment();
        $commentData      = [
            'body'   => $i->getRandomString(10, DataTypesValueHelper::NON_ASCII),
            'newsId' => $preconditionData['newsData']['id'],
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $commentData);
        $i->seeResponseCodeIs(HttpCode::OK);
        $commentData = $i->grabOneFromCommentTable($commentData);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'id'     => $commentData['id'],
                'newsId' => $commentData['newsId'],
                'body'   => $commentData['body'],
            ],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param CommentTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function createCommentBodySpecialchars(CommentTester $i): void
    {
        $i->wantTo('P [Comment] {поле body}:
        Передаем: строка с рандомным набором спецсимволов.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $preconditionData = $i->preconditionForComment();
        $commentData      = [
            'body'   => $i->getRandomString(10, DataTypesValueHelper::CHARACTERS),
            'newsId' => $preconditionData['newsData']['id'],
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $commentData);
        $i->seeResponseCodeIs(HttpCode::OK);
        $commentData = $i->grabOneFromCommentTable($commentData);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'id'     => $commentData['id'],
                'newsId' => $commentData['newsId'],
                'body'   => $commentData['body'],
            ],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param CommentTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function createCommentBodyArray(CommentTester $i): void
    {
        $i->wantTo('N [Comment] {поле body}:
        Передаем: значение типа array.
        Ожидаем: ошибку с пояснением - Значение «Body» должно быть строкой.');
        $preconditionData = $i->preconditionForComment();
        $i->loginAsAdmin();
        $commentData = [
            'body'   => ['array'],
            'newsId' => $preconditionData['newsData']['id'],
        ];

        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $commentData);
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
     * @param CommentTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function createCommentBodyToSqlQuery(CommentTester $i): void
    {
        $i->wantTo('N [Comment] {поле body}:
        Передаем: корректный SQL запрос.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $preconditionData = $i->preconditionForComment();
        $commentData      = [
            'body'   => DataTypesValueHelper::SQL_QUERY,
            'newsId' => $preconditionData['newsData']['id'],
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $commentData);
        $i->seeResponseCodeIs(HttpCode::OK);
        $commentData = $i->grabOneFromCommentTable($commentData);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'id'     => $commentData['id'],
                'newsId' => $commentData['newsId'],
                'body'   => $commentData['body'],
            ],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param CommentTester $i Объект текущего тестировщика
     *
     * @group L3
     * @group validation
     *
     * @throws Exception
     *
     * @return void
     */
    public function createCommentNewsIdNotExist(CommentTester $i): void
    {
        $i->wantTo('N [Comment] {поле newsId}: 
        Передаем: идентификатор несуществующей новости в таблице новостей.
        Ожидаем: ошибку с пояснением - Значение «News Id» неверно.');
        $notExistedId = $i->getNotExistCommentId();
        $commentData  = [
            'body'   => sqs('Test comment body'),
            'newsId' => $notExistedId,
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $commentData);
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
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->dontSeeInCommentTable($commentData);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param CommentTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function createCommentNewsIdOverMaxPhysicInt(CommentTester $i): void
    {
        $i->wantTo('N [Comment] {поле newsId}: тип int(11)
        Передаем: превышение максимального значения типа данных.
        Ожидаем: ошибку с пояснением - Значение «News Id» не должно превышать 2147483647.');
        $i->loginAsAdmin();
        $commentData = [
            'body'   => sqs('Test comment body'),
            'newsId' => DataTypesValueHelper::OVER_MAX_INT_SIGNED,
        ];

        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $commentData);
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
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param CommentTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function createCommentNewsIdLessMinLogicInt(CommentTester $i): void
    {
        $i->wantTo('N [Comment] {поле newsId}: тип int(11)
        Передаем: значение -1.
        Ожидаем: ошибку с пояснением - Значение «News Id» неверно..');
        $commentData = [
            'body'   => sqs('Test comment body'),
            'newsId' => - 1,
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $commentData);
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
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->dontSeeInCommentTable($commentData);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param CommentTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function createCommentNewsIdFloat(CommentTester $i): void
    {
        $i->wantTo('N [Comment] {поле newsId}: 
        Передаем: значение типа float.
        Ожидаем: ошибку с пояснением - Значение «News Id» должно быть целым числом.');
        $commentData = [
            'body'   => sqs('Test comment body'),
            'newsId' => 5.65656,
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $commentData);
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
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param CommentTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function createCommentNewsIdString(CommentTester $i): void
    {
        $i->wantTo('N [Comment] {поле newsId}: 
        Передаем: рандомный набор ASCII символов в строке.
        Ожидаем: ошибку с пояснением - Значение «News Id» должно быть целым числом.');
        $commentData = [
            'body'   => sqs('Test comment body'),
            'newsId' => $i->getRandomString(10, DataTypesValueHelper::ASCII),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $commentData);
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
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param CommentTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function createCommentNewsIdArray(CommentTester $i): void
    {
        $i->wantTo('N [Comment] {поле newsId}: 
            Передаем: значение типа array.
            Ожидаем: ошибку с пояснением - Значение «News Id» должно быть целым числом.');
        $commentData = [
            'body'   => sqs('Test comment body'),
            'newsId' => ['array'],
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $commentData);
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
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param CommentTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function createCommentNewsIdSqlQuery(CommentTester $i): void
    {
        $i->wantTo('N [Comment] {поле newsId}: 
            Передаем: корректный sql запрос.
            Ожидаем: ошибку с пояснением - Значение «News Id» должно быть целым числом.');
        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $commentData = [
            'body'   => sqs('Test comment body'),
            'newsId' => DataTypesValueHelper::SQL_QUERY,
        ];
        $i->sendPOST(self::$URI, $commentData);
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
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }
}
