<?php

namespace UserstoryTemp\NewsNegativeTestRest\tests\support\Step\Api;

use UserstoryTemp\NewsNegativeTestRest\tests\support\ApiTester;

/**
 * Класс текущего актора-тестера на сущность "Новость".
 */
class NewsTester extends ApiTester
{
    /**
     * Свойство хранит массив предусловий.
     *
     * @var array
     */
    protected $defaultDataRequiredFields = [];

    /**
     * Метод устанавливает массив c предусловиями.
     *
     * @param array|null $value Новое значение.
     *
     * @return self
     */
    public function setDefaultDataRequiredFields(?array $value): self
    {
        $this->defaultDataRequiredFields = $value;
        return $this;
    }

    /**
     * Метод возвращает массив с предусловиями.
     *
     * @return array
     */
    public function getDefaultDataRequiredFields(): array
    {
        return $this->defaultDataRequiredFields;
    }

    /**
     * Метод создает две разные между собой группы новостей, но одинаковые параметры внутри каждой.
     *
     * @return array
     */
    public function createTwoGroupNews($countNewsFirstGroup = 3, $countNewsSecondGroup = 5): array
    {
        $dataFirstGroupNews  = $this->createNewsList($countNewsFirstGroup, 1, [
            'isActive'      => 1,
            'publicDate'    => '2005-10-11',
            'publicTime'    => '11:10:05',
            'closeDateTime' => '2010-11-12 05:40:40',
            'isMain'        => 1,
            'title'         => sqs('FirstTitle'),
            'srcUrl'        => sqs('FirstSrcUrl'),
            'summary'       => sqs('FirstSummary'),
            'body'          => sqs('FirstBody'),
        ]);
        $dataSecondGroupNews = $this->createNewsList($countNewsSecondGroup, 1, [
            'isActive'      => 0,
            'publicDate'    => '2010-08-08',
            'publicTime'    => '08:08:08',
            'closeDateTime' => '2018-03-03 05:40:40',
            'isMain'        => 0,
            'title'         => sqs('SecondTitle'),
            'srcUrl'        => sqs('SecondSrcUrl'),
            'summary'       => sqs('SecondSummary'),
            'body'          => sqs('SecondBody'),
        ]);
        $dataAllAddedNews    = array_merge($dataFirstGroupNews, $dataSecondGroupNews);
        $dataRandomAddedNews = $dataAllAddedNews[array_rand($dataAllAddedNews, 1)];
        return [
            'oneRandomAddedNews' => $dataRandomAddedNews,
            'allAddedNews'       => $dataAllAddedNews,
        ];
    }
}
