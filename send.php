<?php
declare(strict_types=1);

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo "Method not allowed";
    exit;
}

/*
|--------------------------------------------------------------------------
| TELEGRAM
|--------------------------------------------------------------------------
| 1. Создай бота через @BotFather.
| 2. Вставь токен бота в TELEGRAM_BOT_TOKEN.
| 3. Узнай chat_id и вставь в TELEGRAM_CHAT_ID.
*/

$TELEGRAM_BOT_TOKEN = "ВСТАВЬ_СЮДА_ТОКЕН_БОТА";
$TELEGRAM_CHAT_ID = "ВСТАВЬ_СЮДА_CHAT_ID";

/*
|--------------------------------------------------------------------------
| BITRIX24 — опционально
|--------------------------------------------------------------------------
| Сюда позже можно вставить webhook на crm.lead.add.json.
*/

$BITRIX_WEBHOOK_URL = "";

/*
|--------------------------------------------------------------------------
| Простая антиспам-проверка
|--------------------------------------------------------------------------
*/

$honeypot = trim($_POST["website"] ?? "");

if ($honeypot !== "") {
    http_response_code(200);
    echo "OK";
    exit;
}

/*
|--------------------------------------------------------------------------
| Данные формы
|--------------------------------------------------------------------------
*/

$name = trim($_POST["name"] ?? "");
$phone = trim($_POST["phone"] ?? "");
$service = trim($_POST["service"] ?? "");
$message = trim($_POST["message"] ?? "");
$formType = trim($_POST["form_type"] ?? "Заявка");

$clientType = trim($_POST["client_type"] ?? "");
$hasOverdue = trim($_POST["has_overdue"] ?? "");
$hasArrest = trim($_POST["has_arrest"] ?? "");
$hasEnforcement = trim($_POST["has_enforcement"] ?? "");
$debtAmount = trim($_POST["debt_amount"] ?? "");
$hasProperty = trim($_POST["has_property"] ?? "");
$priority = trim($_POST["priority"] ?? "");
$contactMethod = trim($_POST["contact_method"] ?? "");

if ($service === "" && $formType === "Мини-тест") {
    $service = "Мини-тест по банкротству / долгам";
}

if ($name === "" || $phone === "" || $service === "") {
    http_response_code(400);
    echo "Заполните имя, телефон и услугу.";
    exit;
}

$nameSafe = htmlspecialchars($name, ENT_QUOTES, "UTF-8");
$phoneSafe = htmlspecialchars($phone, ENT_QUOTES, "UTF-8");
$serviceSafe = htmlspecialchars($service, ENT_QUOTES, "UTF-8");
$messageSafe = htmlspecialchars($message, ENT_QUOTES, "UTF-8");
$formTypeSafe = htmlspecialchars($formType, ENT_QUOTES, "UTF-8");

$clientTypeSafe = htmlspecialchars($clientType, ENT_QUOTES, "UTF-8");
$hasOverdueSafe = htmlspecialchars($hasOverdue, ENT_QUOTES, "UTF-8");
$hasArrestSafe = htmlspecialchars($hasArrest, ENT_QUOTES, "UTF-8");
$hasEnforcementSafe = htmlspecialchars($hasEnforcement, ENT_QUOTES, "UTF-8");
$debtAmountSafe = htmlspecialchars($debtAmount, ENT_QUOTES, "UTF-8");
$hasPropertySafe = htmlspecialchars($hasProperty, ENT_QUOTES, "UTF-8");
$prioritySafe = htmlspecialchars($priority, ENT_QUOTES, "UTF-8");
$contactMethodSafe = htmlspecialchars($contactMethod, ENT_QUOTES, "UTF-8");

$site = $_SERVER["HTTP_HOST"] ?? "FinStart";
$date = date("d.m.Y H:i");

$text =
"🟢 Новая заявка с сайта FinStart\n\n" .
"Тип формы: {$formTypeSafe}\n" .
"Дата: {$date}\n" .
"Сайт: {$site}\n\n" .
"Имя: {$nameSafe}\n" .
"Телефон: {$phoneSafe}\n" .
"Услуга: {$serviceSafe}\n" .
"Сообщение: {$messageSafe}\n";

if ($contactMethodSafe !== "") {
    $text .= "Удобный способ связи: {$contactMethodSafe}\n";
}

if ($formType === "Мини-тест") {
    $text .=
    "\nОтветы мини-теста:\n" .
    "Клиент: {$clientTypeSafe}\n" .
    "Что важнее: {$prioritySafe}\n" .
    "Просрочки: {$hasOverdueSafe}\n" .
    "Аресты: {$hasArrestSafe}\n" .
    "Исполнительное производство: {$hasEnforcementSafe}\n" .
    "Сумма долга: {$debtAmountSafe}\n" .
    "Имущество: {$hasPropertySafe}\n";
}

/*
|--------------------------------------------------------------------------
| Отправка в Telegram
|--------------------------------------------------------------------------
*/

$telegramOk = false;

if ($TELEGRAM_BOT_TOKEN !== "ВСТАВЬ_СЮДА_ТОКЕН_БОТА" && $TELEGRAM_CHAT_ID !== "ВСТАВЬ_СЮДА_CHAT_ID") {
    $telegramUrl = "https://api.telegram.org/bot{$TELEGRAM_BOT_TOKEN}/sendMessage";

    $telegramData = [
        "chat_id" => $TELEGRAM_CHAT_ID,
        "text" => $text
    ];

    $telegramOptions = [
        "http" => [
            "method" => "POST",
            "header" => "Content-Type: application/x-www-form-urlencoded\r\n",
            "content" => http_build_query($telegramData),
            "timeout" => 10
        ]
    ];

    $telegramContext = stream_context_create($telegramOptions);
    $telegramResult = @file_get_contents($telegramUrl, false, $telegramContext);

    if ($telegramResult !== false) {
        $telegramOk = true;
    }
}

/*
|--------------------------------------------------------------------------
| Отправка в Bitrix24
|--------------------------------------------------------------------------
*/

$bitrixOk = false;

if ($BITRIX_WEBHOOK_URL !== "") {
    $bitrixData = [
        "fields" => [
            "TITLE" => "Заявка с сайта FinStart",
            "NAME" => $name,
            "PHONE" => [
                [
                    "VALUE" => $phone,
                    "VALUE_TYPE" => "WORK"
                ]
            ],
            "COMMENTS" =>
                "Тип формы: {$formType}\n" .
                "Услуга: {$service}\n\n" .
                "Сообщение: {$message}\n\n" .
                "Мини-тест:\n" .
                "Клиент: {$clientType}\n" .
                "Просрочки: {$hasOverdue}\n" .
                "Аресты: {$hasArrest}\n" .
                "Исполнительное производство: {$hasEnforcement}\n" .
                "Сумма долга: {$debtAmount}\n" .
                "Имущество: {$hasProperty}\n" .
                "Что важнее: {$priority}\n" .
                "Удобный способ связи: {$contactMethod}\n",
            "SOURCE_ID" => "WEB"
        ],
        "params" => [
            "REGISTER_SONET_EVENT" => "Y"
        ]
    ];

    $bitrixOptions = [
        "http" => [
            "method" => "POST",
            "header" => "Content-Type: application/json\r\n",
            "content" => json_encode($bitrixData, JSON_UNESCAPED_UNICODE),
            "timeout" => 10
        ]
    ];

    $bitrixContext = stream_context_create($bitrixOptions);
    $bitrixResult = @file_get_contents($BITRIX_WEBHOOK_URL, false, $bitrixContext);

    if ($bitrixResult !== false) {
        $bitrixOk = true;
    }
}

if ($telegramOk || $bitrixOk) {
    header("Location: thank-you.html");
    exit;
}

if ($TELEGRAM_BOT_TOKEN === "ВСТАВЬ_СЮДА_ТОКЕН_БОТА" && $BITRIX_WEBHOOK_URL === "") {
    http_response_code(500);
    echo "Форма создана, но Telegram или Bitrix24 ещё не настроены.";
    exit;
}

http_response_code(500);
echo "Не удалось отправить заявку. Попробуйте написать в WhatsApp.";
exit;
