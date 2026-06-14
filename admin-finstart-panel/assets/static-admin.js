const STORAGE_KEY = "finstart_site_data";
const form = document.getElementById("staticAdminForm");
const savedAlert = document.getElementById("savedAlert");
const servicesEditor = document.getElementById("servicesEditor");
const pricingEditor = document.getElementById("pricingEditor");

let siteData = {};

function getPathValue(source, path, fallback = "") {
  return path.split(".").reduce((value, key) => {
    if (value && Object.prototype.hasOwnProperty.call(value, key)) return value[key];
    return fallback;
  }, source);
}

function setPathValue(target, path, value) {
  const parts = path.split(".");
  const last = parts.pop();
  const parent = parts.reduce((object, key) => {
    object[key] = object[key] || {};
    return object[key];
  }, target);
  parent[last] = value;
}

function escapeHtml(value) {
  return String(value)
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;");
}

function fillNamedFields() {
  form.querySelectorAll("[name]").forEach((field) => {
    if (field.name.includes("services.") || field.name.includes("pricing.")) return;
    field.value = getPathValue(siteData, field.name, "");
  });
}

function renderServices() {
  servicesEditor.innerHTML = "";
  const services = Array.isArray(siteData.services) ? siteData.services : [];

  services.slice(0, 8).forEach((service, index) => {
    const block = document.createElement("div");
    block.className = "service-editor";
    block.innerHTML = `
      <label class="checkbox-row"><input type="checkbox" name="services.${index}.enabled" ${service.enabled === false ? "" : "checked"} />Показывать услугу №${index + 1}</label>
      <label>Название <input name="services.${index}.title" value="${escapeHtml(service.title || "")}" /></label>
      <label>Описание <textarea name="services.${index}.description">${escapeHtml(service.description || "")}</textarea></label>
      <label>Ссылка <input name="services.${index}.link" value="${escapeHtml(service.link || "")}" /></label>
    `;
    servicesEditor.appendChild(block);
  });
}

function renderPricing() {
  pricingEditor.innerHTML = "";
  const cards = Array.isArray(siteData.pricing?.cards) ? siteData.pricing.cards : [];

  cards.slice(0, 3).forEach((card, index) => {
    const block = document.createElement("div");
    block.className = "price-editor";
    block.innerHTML = `
      <h3>Карточка №${index + 1}</h3>
      <label>Название <input name="pricing.cards.${index}.title" value="${escapeHtml(card.title || "")}" /></label>
      <label>Цена / подпись <input name="pricing.cards.${index}.price" value="${escapeHtml(card.price || "")}" /></label>
      <label>Пункты, каждый с новой строки <textarea name="pricing.cards.${index}.items">${escapeHtml((card.items || []).join("\n"))}</textarea></label>
    `;
    pricingEditor.appendChild(block);
  });
}

function readForm() {
  const data = structuredClone(siteData);

  form.querySelectorAll("[name]").forEach((field) => {
    const name = field.name;

    if (name.startsWith("services.")) {
      const [, index, key] = name.split(".");
      data.services[index][key] = field.type === "checkbox" ? field.checked : field.value.trim();
      return;
    }

    if (name.startsWith("pricing.cards.")) {
      const [, , index, key] = name.split(".");
      data.pricing.cards[index][key] = key === "items"
        ? field.value.split("\n").map((item) => item.trim()).filter(Boolean)
        : field.value.trim();
      return;
    }

    setPathValue(data, name, field.value.trim());
  });

  return data;
}

function showSaved(message = "Изменения сохранены. Откройте сайт или обновите страницу.") {
  savedAlert.hidden = false;
  savedAlert.textContent = message;
  window.scrollTo({ top: 0, behavior: "smooth" });
}

async function loadData() {
  const stored = localStorage.getItem(STORAGE_KEY);
  if (stored) {
    siteData = JSON.parse(stored);
  } else {
    const response = await fetch("../data/site.json", { cache: "no-store" });
    siteData = await response.json();
  }

  fillNamedFields();
  renderServices();
  renderPricing();
}

form.addEventListener("submit", (event) => {
  event.preventDefault();
  siteData = readForm();
  localStorage.setItem(STORAGE_KEY, JSON.stringify(siteData));
  showSaved();
});

document.getElementById("resetData").addEventListener("click", () => {
  localStorage.removeItem(STORAGE_KEY);
  location.reload();
});

loadData().catch(() => {
  showSaved("Не удалось загрузить data/site.json.");
});
