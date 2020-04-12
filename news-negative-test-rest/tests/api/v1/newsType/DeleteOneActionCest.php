<?php

declare(strict_types = 1);

namespace UserstoryTemp\NewsNegativeTestRest\tests\api\v1\newsType;

use Codeception\Util\HttpCode;
use UserstoryTemp\NewsNegativeTest\tests\support\Helpers\DataTypesValueHelper;
use UserstoryTemp\NewsNegativeTestRest\tests\support\Step\Api\NewsTypeTester;
use function count;

/**
 * Класс тестирования REST API: удаление сущности "Типы новостей".
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
    public static $URI = 'v1/news-type/';

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
     * @param NewsTypeTester $i Объект текущего тестировщика.
     *
     * @return void
     */
    public function _after(NewsTypeTester $i): void
    {
    }

    /**
     * Метод для предварительных инициализаций перед тестами.
     *
     * @param NewsTypeTester $i Объект текущего тестировщика.
     *
     * @return void
     */
    public function _before(NewsTypeTester $i): void
    {
        $i->flushCache();
    }

    /**
     * Метод проверяет запрос удаления типа новости.
     *
     * @param NewsTypeTester $i Объект текущего тестировщика.
     *
     * @group L2
     * @group authorization
     *
     * @return void
     */
    public function deleteNewsTypeWithOutAuth(NewsTypeTester $i): void
    {
        $i->wantTo('N [NewsType] 
        Запрос: удаление сущности без авторизации. 
        Ожидаем: Ошибку с пояснением - Доступ запрещен');
        $newsTypeData = $i->createNewsType();

        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $newsTypeData['id']);

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
        $i->seeNewsTypeInTable($newsTypeData);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет запрос удаления типа новости.
     *
     * @param NewsTypeTester $i Объект текущего тестировщика.
     *
     * @group L1
     * @group functional
     *
     * @return void
     */
    public function deleteNewsTypeCorrect(NewsTypeTester $i): void
    {
        $i->wantTo('P [NewsType] тип int(11)
        Передаем: корректный идентификатор типа новостей. 
        Ожидаем: успешное удаление.');
        $newsTypeData = $i->createNewsType();

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $newsTypeData['id']);

        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'success' => true,
            ],
        ]);
        $i->dontSeeNewsTypeInTable($newsTypeData);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет запрос удаления типа новости.
     *
     * @param NewsTypeTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws \Exception
     *
     * @return void
     */
    public function deleteNewsTypeNotExist(NewsTypeTester $i): void
    {
        $i->wantTo('N [NewsType] тип int(11)
        Передаем: несуществующий в таблице идентификатор типа новости.
        Ожидаем: Успешный результат выполнения запроса. Количество записей в базе не изменено');
        $newsTypeIds         = $i->grabColumnFromNewsTypeTable('id');
        $notExistId          = $i->getNotExistNewsTypeId();
        $newsTypeCountBefore = count($newsTypeIds);

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
        $i->seeNumRecordsInNewsTypeTable($newsTypeCountBefore);
    }

    /**
     * Метод проверяет запрос удаления типа новости.
     *
     * @param NewsTypeTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function deleteNewsTypeIdLessMinLogicInt(NewsTypeTester $i): void
    {
        $i->wantTo('N [NewsTypes] тип int(11)
        Передаем: значение 0.
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
     * @param NewsTypeTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function deleteNewsTypeIdOverMaxPhysicInt(NewsTypeTester $i): void
    {
        $i->wantTo('N [NewsTypes] тип int(11)
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
