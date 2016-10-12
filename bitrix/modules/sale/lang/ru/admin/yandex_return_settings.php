<?php
$MESS['SALE_YANDEX_RETURN_TITLE'] = "Настройка возврата оплат для Яндекс.Кассы";

$MESS['SALE_YANDEX_RETURN_SUBTITLE'] = "Настройка взаимодействия по протоколу MWS (<a target=\"_blank\" href=\"https://tech.yandex.ru/money/doc/payment-solution/payment-management/payment-management-about-docpage/\">Merchant Web Services</a>)";
$MESS['SALE_YANDEX_RETURN_HELP'] = "Для работы с MWS необходимо получить в Яндекс.Деньгах специальный сертификат и загрузить его на этой странице.";
$MESS['SALE_YANDEX_RETURN_IP_DESC'] = "С этого IP будут отправляться запросы на возврат.";
$MESS['SALE_YANDEX_RETURN_ACTIVE'] = "Работа модуля";

$MESS['SALE_YANDEX_RETURN_PT_BY_DEFAULT'] = "По умолчанию";
$MESS['SALE_YANDEX_RETURN_ERROR_SHOP_ID'] = "Прежде, чем настраивать возвраты, необходимо заполнить настройки обработчика платежной системы для данного типа плательщика:<br> <ul><li>Идентификатор магазина в ЦПП (ShopID)</li></ul>";
$MESS['SALE_YANDEX_RETURN_CERT'] = "SSL-сертификат";
$MESS['SALE_YANDEX_RETURN_TEXT_SUCCESS'] = "Сертификат загружен.";
$MESS['SALE_YANDEX_RETURN_TEXT_CLEAR'] = "Удалить сертификат ";

$MESS['SALE_YANDEX_RETURN_HOW'] = "Как получить сертификат";
$MESS['SALE_YANDEX_RETURN_HOW_ITEM1'] = "Скачайте <a href='%s'>готовый запрос на сертификат</a> (файл в формате .csr).";
$MESS['SALE_YANDEX_RETURN_HOW_ITEM2'] = "Скачайте <a target=\"_blank\" href=\"https://money.yandex.ru/i/html-letters/SSL_Cert_Form.doc\">заявку на сертификат</a>.";
$MESS['SALE_YANDEX_RETURN_HOW_ITEM3'] = "В заявке заполните таблицу данными с этой страницы (внизу), поставьте подпись и печать компании.";
$MESS['SALE_YANDEX_RETURN_HOW_ITEM4'] = "Напишите менеджеру Яндекс.Кассы на <a href=\"mailto:merchants@yamoney.ru\">merchants@yamoney.ru</a>, что вам нужен сертификат для возвратов по MWS. К письму приложите:".
                                "<ul>
                                    <li>файл запроса,</li>
                                    <li>скан заполненной заявки,</li>
                                    <li>IP, с которого будут приходить запросы на возврат.</li>
                                </ul>";
$MESS['SALE_YANDEX_RETURN_HOW_ITEM5'] = "Дождитесь сертификата и загрузите его на этой странице.";

$MESS['SALE_YANDEX_RETURN_STATEMENT'] = "Данные для заполнения заявки";
$MESS['SALE_YANDEX_RETURN_STATEMENT_INTRO'] = "Скопируйте эти данные в таблицу. Остальные строчки заполните самостоятельно.";
$MESS['SALE_YANDEX_RETURN_STATEMENT_CN'] = "CN";
$MESS['SALE_YANDEX_RETURN_STATEMENT_SIGN'] = "Электронная подпись на сертификат";
$MESS['SALE_YANDEX_RETURN_STATEMENT_CAUSE'] = "Причина запроса";
$MESS['SALE_YANDEX_RETURN_STATEMENT_CAUSE_VAL'] = "Первоначальный";
$MESS['SPSN_2FLIST'] = "Вернуться к платежной системе";

$MESS['SALE_YANDEX_RETURN_SAVE'] = "Сохранить";