<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_admin();
verify_csrf();

function clean_string(mixed $value): string
{
    return trim((string)$value);
}

function clean_lines(mixed $value): array
{
    $text = str_replace(["\r\n", "\r"], "\n", (string)$value);
    $lines = array_map('trim', explode("\n", $text));
    return array_values(array_filter($lines, static fn($line) => $line !== ''));
}

$oldData = [];
if (is_file(DATA_FILE)) {
    $raw = file_get_contents(DATA_FILE);
    $decoded = json_decode($raw ?: '{}', true);
    if (is_array($decoded)) {
        $oldData = $decoded;
    }
}

$newData = $oldData;
$newData['site'] = $oldData['site'] ?? ['brand' => 'FINSTART', 'subtitle' => 'Юридическая и финансовая защита'];

$newData['hero'] = [
    'badge' => clean_string($_POST['hero']['badge'] ?? ''),
    'title' => clean_string($_POST['hero']['title'] ?? ''),
    'slogan' => clean_string($_POST['hero']['slogan'] ?? ''),
    'text' => clean_string($_POST['hero']['text'] ?? ''),
    'primary_button' => clean_string($_POST['hero']['primary_button'] ?? ''),
];

$newData['contact'] = [
    'phone_display' => clean_string($_POST['contact']['phone_display'] ?? ''),
    'phone_tel' => clean_string($_POST['contact']['phone_tel'] ?? ''),
    'whatsapp_url' => clean_string($_POST['contact']['whatsapp_url'] ?? ''),
    'address_line_1' => clean_string($_POST['contact']['address_line_1'] ?? ''),
    'address_line_2' => clean_string($_POST['contact']['address_line_2'] ?? ''),
    'address_line_3' => clean_string($_POST['contact']['address_line_3'] ?? ''),
    'address_line_4' => clean_string($_POST['contact']['address_line_4'] ?? ''),
];

$allowedBlocks = [
    'result','services','situations','analysis','mistakes','pricing','installment',
    'checklist','documents','process','about','reviews','office','callback','contacts'
];

$newData['blocks'] = [];
foreach ($allowedBlocks as $block) {
    $newData['blocks'][$block] = isset($_POST['blocks'][$block]);
}

$newData['services'] = [];
$services = $_POST['services'] ?? [];
for ($i = 0; $i < 8; $i++) {
    $service = $services[$i] ?? [];
    $newData['services'][] = [
        'enabled' => isset($service['enabled']),
        'title' => clean_string($service['title'] ?? ''),
        'description' => clean_string($service['description'] ?? ''),
        'link' => clean_string($service['link'] ?? ''),
    ];
}

$newData['pricing'] = ['cards' => []];
$pricingCards = $_POST['pricing']['cards'] ?? [];
for ($i = 0; $i < 3; $i++) {
    $card = $pricingCards[$i] ?? [];
    $newData['pricing']['cards'][] = [
        'title' => clean_string($card['title'] ?? ''),
        'price' => clean_string($card['price'] ?? ''),
        'items' => clean_lines($card['items'] ?? ''),
    ];
}

$newData['installment'] = [
    'title' => clean_string($_POST['installment']['title'] ?? ''),
    'text' => clean_string($_POST['installment']['text'] ?? ''),
    'note' => clean_string($_POST['installment']['note'] ?? ''),
];

$newData['custom_blocks'] = [];
$customBlocks = $_POST['custom_blocks'] ?? [];
for ($i = 0; $i < 3; $i++) {
    $block = $customBlocks[$i] ?? [];
    $newData['custom_blocks'][] = [
        'enabled' => isset($block['enabled']),
        'title' => clean_string($block['title'] ?? ''),
        'text' => clean_string($block['text'] ?? ''),
    ];
}

if (!is_dir(BACKUP_DIR)) {
    mkdir(BACKUP_DIR, 0755, true);
}

if (is_file(DATA_FILE)) {
    $backupName = BACKUP_DIR . '/site_' . date('Ymd_His') . '.json';
    copy(DATA_FILE, $backupName);
}

$json = json_encode($newData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
if ($json === false) {
    http_response_code(500);
    echo 'Не удалось подготовить JSON.';
    exit;
}

if (file_put_contents(DATA_FILE, $json . PHP_EOL, LOCK_EX) === false) {
    http_response_code(500);
    echo 'Не удалось сохранить файл data/site.json. Проверьте права доступа.';
    exit;
}

header('Location: index.php?saved=1');
exit;
