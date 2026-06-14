<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
require_admin();

$success = isset($_GET['saved']);
$raw = is_file(DATA_FILE) ? file_get_contents(DATA_FILE) : '{}';
$data = json_decode($raw ?: '{}', true);
if (!is_array($data)) { $data = []; }

function value_get(array $data, string $path, string $default = ''): string {
    $value = $data;
    foreach (explode('.', $path) as $part) {
        if (!is_array($value) || !array_key_exists($part, $value)) return $default;
        $value = $value[$part];
    }
    return is_array($value) ? $default : (string)$value;
}
function checked_get(array $data, string $path): string {
    $value = $data;
    foreach (explode('.', $path) as $part) {
        if (!is_array($value) || !array_key_exists($part, $value)) return '';
        $value = $value[$part];
    }
    return $value ? 'checked' : '';
}

$services = $data['services'] ?? [];
$pricingCards = $data['pricing']['cards'] ?? [];
$customBlocks = $data['custom_blocks'] ?? [];
$blockLabels = [
    'result'=>'Что получит клиент после анализа',
    'services'=>'Услуги',
    'situations'=>'Популярные ситуации',
    'analysis'=>'Бесплатный первичный анализ',
    'mistakes'=>'Ошибки, которых стоит избегать',
    'pricing'=>'Стоимость',
    'installment'=>'Рассрочка',
    'checklist'=>'Получить список документов',
    'documents'=>'Документы',
    'process'=>'Процесс работы',
    'about'=>'О FinStart',
    'reviews'=>'Отзывы / примеры обращений',
    'office'=>'Офис и карта',
    'callback'=>'Перезвоните мне',
    'contacts'=>'Контакты и форма',
];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Админка FinStart</title>
  <link rel="stylesheet" href="assets/admin.css" />
</head>
<body>
  <header class="admin-header">
    <div class="admin-container admin-header-inner">
      <div>
        <h1>FinStart Admin</h1>
        <p>Управление текстами, блоками, услугами и контактами сайта.</p>
      </div>
      <div class="admin-actions">
        <a class="button secondary" href="../index.html" target="_blank">Открыть сайт</a>
        <a class="button" href="logout.php">Выйти</a>
      </div>
    </div>
  </header>

  <main class="admin-main">
    <form method="POST" action="save.php">
      <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>" />
      <div class="admin-container admin-grid">
        <?php if ($success): ?><div class="alert success">Изменения сохранены. Обновите сайт в браузере.</div><?php endif; ?>

        <section class="admin-card">
          <h2>Главный экран</h2>
          <label>Бейдж <input name="hero[badge]" value="<?= h(value_get($data, 'hero.badge')) ?>" /></label>
          <label>Главный заголовок <textarea name="hero[title]"><?= h(value_get($data, 'hero.title')) ?></textarea></label>
          <label>Слоган <input name="hero[slogan]" value="<?= h(value_get($data, 'hero.slogan')) ?>" /></label>
          <label>Описание <textarea name="hero[text]"><?= h(value_get($data, 'hero.text')) ?></textarea></label>
          <label>Текст главной кнопки <input name="hero[primary_button]" value="<?= h(value_get($data, 'hero.primary_button')) ?>" /></label>
        </section>

        <section class="admin-card">
          <h2>Контакты</h2>
          <div class="two-cols">
            <label>Телефон для отображения <input name="contact[phone_display]" value="<?= h(value_get($data, 'contact.phone_display')) ?>" /></label>
            <label>Телефон для ссылки tel: <input name="contact[phone_tel]" value="<?= h(value_get($data, 'contact.phone_tel')) ?>" /></label>
          </div>
          <label>WhatsApp-ссылка <input name="contact[whatsapp_url]" value="<?= h(value_get($data, 'contact.whatsapp_url')) ?>" /></label>
          <div class="two-cols">
            <label>Адрес строка 1 <input name="contact[address_line_1]" value="<?= h(value_get($data, 'contact.address_line_1')) ?>" /></label>
            <label>Адрес строка 2 <input name="contact[address_line_2]" value="<?= h(value_get($data, 'contact.address_line_2')) ?>" /></label>
            <label>Адрес строка 3 <input name="contact[address_line_3]" value="<?= h(value_get($data, 'contact.address_line_3')) ?>" /></label>
            <label>Адрес строка 4 <input name="contact[address_line_4]" value="<?= h(value_get($data, 'contact.address_line_4')) ?>" /></label>
          </div>
        </section>

        <section class="admin-card">
          <h2>Показывать / скрывать блоки главной страницы</h2>
          <div class="three-cols">
            <?php foreach ($blockLabels as $key => $label): ?>
              <label class="checkbox-row"><input type="checkbox" name="blocks[<?= h($key) ?>]" value="1" <?= checked_get($data, 'blocks.' . $key) ?> /><?= h($label) ?></label>
            <?php endforeach; ?>
          </div>
        </section>

        <section class="admin-card">
          <h2>Услуги</h2>
          <p class="help-text">Можно менять название, краткое описание, ссылку и скрывать отдельные услуги.</p>
          <?php for ($i = 0; $i < max(8, count($services)); $i++): ?>
            <?php $service = $services[$i] ?? ['title'=>'','description'=>'','link'=>'','enabled'=>false]; ?>
            <div class="service-editor">
              <label class="checkbox-row"><input type="checkbox" name="services[<?= $i ?>][enabled]" value="1" <?= !empty($service['enabled']) ? 'checked' : '' ?> />Показывать услугу №<?= $i + 1 ?></label>
              <label>Название <input name="services[<?= $i ?>][title]" value="<?= h((string)($service['title'] ?? '')) ?>" /></label>
              <label>Описание <textarea name="services[<?= $i ?>][description]"><?= h((string)($service['description'] ?? '')) ?></textarea></label>
              <label>Ссылка <input name="services[<?= $i ?>][link]" value="<?= h((string)($service['link'] ?? '')) ?>" /></label>
            </div>
          <?php endfor; ?>
        </section>

        <section class="admin-card">
          <h2>Стоимость</h2>
          <?php for ($i = 0; $i < 3; $i++): ?>
            <?php $card = $pricingCards[$i] ?? ['title'=>'','price'=>'','items'=>[]]; ?>
            <div class="price-editor">
              <h3>Карточка №<?= $i + 1 ?></h3>
              <label>Название <input name="pricing[cards][<?= $i ?>][title]" value="<?= h((string)($card['title'] ?? '')) ?>" /></label>
              <label>Цена / подпись <input name="pricing[cards][<?= $i ?>][price]" value="<?= h((string)($card['price'] ?? '')) ?>" /></label>
              <label>Пункты, каждый с новой строки <textarea name="pricing[cards][<?= $i ?>][items]"><?= h(implode("\n", $card['items'] ?? [])) ?></textarea></label>
            </div>
          <?php endfor; ?>
        </section>

        <section class="admin-card">
          <h2>Рассрочка</h2>
          <label>Заголовок <input name="installment[title]" value="<?= h(value_get($data, 'installment.title')) ?>" /></label>
          <label>Основной текст <textarea name="installment[text]"><?= h(value_get($data, 'installment.text')) ?></textarea></label>
          <label>Пояснение <textarea name="installment[note]"><?= h(value_get($data, 'installment.note')) ?></textarea></label>
        </section>

        <section class="admin-card">
          <h2>Дополнительные блоки</h2>
          <p class="help-text">Можно добавить до 3 простых дополнительных блоков на главную страницу.</p>
          <?php for ($i = 0; $i < 3; $i++): ?>
            <?php $block = $customBlocks[$i] ?? ['enabled'=>false,'title'=>'','text'=>'']; ?>
            <div class="custom-editor">
              <label class="checkbox-row"><input type="checkbox" name="custom_blocks[<?= $i ?>][enabled]" value="1" <?= !empty($block['enabled']) ? 'checked' : '' ?> />Показывать блок №<?= $i + 1 ?></label>
              <label>Заголовок <input name="custom_blocks[<?= $i ?>][title]" value="<?= h((string)($block['title'] ?? '')) ?>" /></label>
              <label>Текст <textarea name="custom_blocks[<?= $i ?>][text]"><?= h((string)($block['text'] ?? '')) ?></textarea></label>
            </div>
          <?php endfor; ?>
        </section>
      </div>

      <div class="save-bar">
        <div class="admin-container"><button type="submit">Сохранить изменения</button></div>
      </div>
    </form>
  </main>
</body>
</html>
