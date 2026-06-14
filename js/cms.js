document.addEventListener("DOMContentLoaded", () => {
  loadCmsData()
    .then((data) => {
      if (data) applyCmsData(data);
    })
    .catch(() => {
      // Static fallback remains visible if dynamic content is unavailable.
    });
});

function getStoredCmsData() {
  try {
    const stored = localStorage.getItem("finstart_site_data");
    return stored ? JSON.parse(stored) : null;
  } catch {
    return null;
  }
}

function fetchJson(url) {
  return fetch(url, { cache: "no-store" }).then((response) => {
    if (!response.ok) throw new Error("Content unavailable");
    return response.json();
  });
}

function loadCmsData() {
  const stored = getStoredCmsData();
  if (stored) return Promise.resolve(stored);

  return fetchJson("content.php").catch(() => fetchJson("data/site.json"));
}

function setText(selector, value) {
  if (value === undefined || value === null) return;
  document.querySelectorAll(selector).forEach((element) => {
    element.textContent = value;
  });
}

function setHref(selector, value) {
  if (!value) return;
  document.querySelectorAll(selector).forEach((element) => {
    element.setAttribute("href", value);
  });
}

function applyCmsData(data) {
  setText('[data-cms="site.brand"]', data.site?.brand);
  setText('[data-cms="site.subtitle"]', data.site?.subtitle);
  setText('[data-cms="hero.badge"]', data.hero?.badge);
  setText('[data-cms="hero.title"]', data.hero?.title);
  setText('[data-cms="hero.slogan"]', data.hero?.slogan);
  setText('[data-cms="hero.text"]', data.hero?.text);
  setText('[data-cms="hero.primary_button"]', data.hero?.primary_button);
  setText('[data-cms="contact.phone_display"]', data.contact?.phone_display);
  setText('[data-cms="contact.address_line_1"]', data.contact?.address_line_1);
  setText('[data-cms="contact.address_line_2"]', data.contact?.address_line_2);
  setText('[data-cms="contact.address_line_3"]', data.contact?.address_line_3);
  setText('[data-cms="contact.address_line_4"]', data.contact?.address_line_4);

  const telHref = data.contact?.phone_tel ? `tel:${data.contact.phone_tel}` : "";
  setHref('a[href^="tel:"]', telHref);
  setHref('a[href*="wa.me"]', data.contact?.whatsapp_url);

  if (data.blocks) {
    Object.entries(data.blocks).forEach(([blockId, enabled]) => {
      const block = document.getElementById(blockId);
      if (block) block.style.display = enabled ? "" : "none";
    });
  }

  if (Array.isArray(data.services)) {
    data.services.forEach((service, index) => {
      const card = document.querySelector(`[data-service-card="${index}"]`);
      if (!card) return;
      card.style.display = service.enabled === false ? "none" : "";
      setText(`[data-service-title="${index}"]`, service.title);
      setText(`[data-service-description="${index}"]`, service.description);
      document.querySelectorAll(`[data-service-link="${index}"]`).forEach((link) => {
        if (service.link) link.setAttribute("href", service.link);
      });
    });
  }

  if (data.pricing && Array.isArray(data.pricing.cards)) {
    data.pricing.cards.forEach((card, index) => {
      setText(`[data-price-title="${index}"]`, card.title);
      setText(`[data-price-value="${index}"]`, card.price);
      const list = document.querySelector(`[data-price-items="${index}"]`);
      if (list && Array.isArray(card.items)) {
        list.innerHTML = "";
        card.items.forEach((item) => {
          const li = document.createElement("li");
          li.textContent = item;
          list.appendChild(li);
        });
      }
    });
  }

  setText('[data-cms="installment.title"]', data.installment?.title);
  setText('[data-cms="installment.text"]', data.installment?.text);
  setText('[data-cms="installment.note"]', data.installment?.note);
  renderCustomBlocks(data.custom_blocks || []);
}

function renderCustomBlocks(blocks) {
  const container = document.getElementById("custom-blocks");
  if (!container) return;
  container.innerHTML = "";
  blocks
    .filter((block) => block && block.enabled && (block.title || block.text))
    .forEach((block) => {
      const section = document.createElement("section");
      section.className = "section reveal is-visible";
      const inner = document.createElement("div");
      inner.className = "container";
      const head = document.createElement("div");
      head.className = "section-head";
      const label = document.createElement("span");
      label.className = "section-label";
      label.textContent = "Дополнительно";
      const title = document.createElement("h2");
      title.textContent = block.title || "Дополнительная информация";
      const text = document.createElement("p");
      text.textContent = block.text || "";
      head.appendChild(label);
      head.appendChild(title);
      head.appendChild(text);
      inner.appendChild(head);
      section.appendChild(inner);
      container.appendChild(section);
    });
}
