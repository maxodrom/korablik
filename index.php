<?php

spl_autoload_register(function ($class_name) {
    require_once __DIR__ . DIRECTORY_SEPARATOR . $class_name . '.php';
});

$urlYandex = 'https://yandex.ru';
$urlGoogle = 'https://google.ru';
$urlYii = 'https://raw.githubusercontent.com/yiisoft/yii2/master/docs/guide-ru/tutorial-mailing.md';

$formatFunc = function ($subject) {
    echo '<pre>';
    print_r($subject);
    echo '</pre>';
};

try {
    /**
     * По условиям задачи инстанцирование потомка должно быть реализовано используя только интерфейс
     * базового (родительского) класса.
     * И если мы пытаемся создать экземлпяр, используя класс-потомок, генерируется исключение
     * (см. реализацию для подробностей).
     */
    // здесь будет выброшено исключение
    // $inverseDoc = InverseDocumentProcessor::getInstance(DocumentProcessor::TYPE_INVERSE, $url);
    // тут тоже не получится (уже по синтаксическим причинам - вызов private конструктура невозможен)
    // $inverseDoc = new InverseDocumentProcessor();

    // создаем экземпляр базового класса => создается корректно
    //$baseDoc = DocumentProcessor::getInstance(null, $urlYandex);
    //$formatFunc($baseDoc);

    // создаем экземпляр класса-потомка через его базовый класс => создается корректно
    //$inverseDoc = DocumentProcessor::getInstance(DocumentProcessor::TYPE_INVERSE, $urlGoogle);
    //$formatFunc($inverseDoc);


    ///////////////////////////////////////////////
    // DocumentProcessor USING
    ///////////////////////////////////////////////
    echo '<hr/><h1>DocumentProcessor USING</h1><hr/>';

    $baseDoc = DocumentProcessor::getInstance(null, $urlYii);
    $formatFunc($baseDoc);
    $response = $baseDoc->fetch();
    if (isset($response['status_code']) && $response['status_code'] != 200) {
        // обработка ошибок
    }
    $baseDoc->setContent($response['content']);
    $content = $baseDoc
        // замена "класс" на "квас"
        ->replaceUsingPair('класс', 'квас')
        // выховы ниже вызовут порочный круг и будет выброщено исключение (see phpdoc).
        //->replaceUsingPair('может быть', 'не может быть')
        //->replaceUsingPair('метод', 'методика')
        // colorize processing results directly via $replacement
        ->replaceUsingPair(
            'метод',
            '<span style="background-color:#F89406;">method</span>'
        )
        // using mapping and user-defined callback (see phpdoc).
        ->replaceUsingMapping(
            [
                'использовать' => '"юзать"',
                'данные' => 'контент',
            ],
            function ($value) {
                return '<span style="background-color:#C12E2A;color:#FFFFFF;">' . $value . '</span>';
            }
        )
        ->replaceUsingMapping([
            'а вот и нет!' => 'а вот и да!',
        ])
        ->getContent();

    $formatFunc($content);


    ///////////////////////////////////////////////
    // InverseDocumentProcessor USING
    // Инверсивные операции.
    ///////////////////////////////////////////////
    echo '<hr/><h1>InverseDocumentProcessor USING</h1><hr/>';
    $inverseDoc = DocumentProcessor::getInstance(DocumentProcessor::TYPE_INVERSE, $urlYii);

    $inverseDoc->setContent($content);
    $inversedContent = $inverseDoc
        ->inverseReplaceUsingMapping([
            'метод' => 'method',
            'использовать' => '"юзать"'
        ])
        ->getContent();

    $formatFunc($inverseDoc);
    $formatFunc($inversedContent);

} catch (\Exception $e) {
    echo $e->getMessage();
    $formatFunc($e->getTrace());
}
