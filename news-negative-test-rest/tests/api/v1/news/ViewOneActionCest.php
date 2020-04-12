<?php

declare( strict_types = 1 );

namespace UserstoryTemp\NewsNegativeTestRest\tests\api\v1\news;

use Codeception\Util\HttpCode;
use UserstoryTemp\NewsNegativeTestRest\tests\support\Step\Api\NewsTester;

/**
 * Класс тестирования REST API: просмотр одной сущности "Новости".
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
                'publicDate'    => 'string',
                'publicTime'    => 'string',
                'closeDateTime' => 'string',
                'isMain'        => 'boolean',
                'title'         => 'string',
                'srcUrl'        => 'string',
                'summary'       => 'string',
                'body'          => 'string',
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
     *
     * @throws
     */
    public function _before(NewsTester $i): void
    {
        $i->flushCache();
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L2
     * @group authorization
     *
     * @return void
     */
    public function viewOneNewsWithoutAuth(NewsTester $i): void
    {
        $i->wantTo('N [News]: 
        Передаем: запрос без авторизации .
        Ожидаем: ошибку с пояснением - Доступ запрещен.');

        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->createNews()['id']);
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
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L1
     * @group functional
     *
     * @return void
     */
    public function viewOneNewsCorrect(NewsTester $i): void
    {
        $i->wantTo('P [News]: 
        Передаем: корректный id сущности.
        Ожидаем: массив с информацией по запрашиваемому обьекту.');

        $dataCreatedNews = $i->createNews();

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $dataCreatedNews['id']);

        $i->seeResponseCodeIs(HttpCode::OK);

        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => $dataCreatedNews,
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
    public function viewOneNewsNotExist(NewsTester $i): void
    {
        $i->wantTo('N [News]: 
        Передаем: несуществующий в таблице идентификатор.
        Ожидаем: ошибку с поянением - Сущность не найдена.');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->getNotExistNewsId());

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
     */
    public function viewOneNewsOverMaxPhysicInt(NewsTester $i): void
    {
        $i->wantTo('N [News]: тип int(11)
        Передаем: превышение максимального значения типа данных.
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
    public function viewOneNewsLessMinLogicInt(NewsTester $i): void
    {
        $i->wantTo('N [News]: тип int(11)
        Передаем: значение 0.
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
}
