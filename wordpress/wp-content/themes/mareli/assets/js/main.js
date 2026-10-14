(() => {
  const header = document.querySelector("[data-site-header]");
  const onScroll = () => {
    if (!header) return;
    header.classList.toggle("is-scrolled", window.scrollY > 12);
  };
  onScroll();
  window.addEventListener("scroll", onScroll, { passive: true });

  const menuToggle = document.querySelector("[data-menu-toggle]");
  const mobile = document.querySelector("[data-nav-mobile]");
  if (menuToggle && mobile) {
    menuToggle.addEventListener("click", () => {
      const open = mobile.classList.toggle("is-open");
      menuToggle.setAttribute("aria-expanded", open ? "true" : "false");
    });
  }

  const dropdown = document.querySelector("[data-nav-dropdown]");
  if (dropdown) {
    const btn = dropdown.querySelector("[data-nav-dropdown-btn]");
    const menu = dropdown.querySelector("[data-nav-dropdown-menu]");
    let closeTimer = null;
    const openMenu = () => {
      if (closeTimer) clearTimeout(closeTimer);
      dropdown.classList.add("is-open");
      if (menu) menu.hidden = false;
      if (btn) btn.setAttribute("aria-expanded", "true");
    };
    const closeMenu = () => {
      dropdown.classList.remove("is-open");
      if (menu) menu.hidden = true;
      if (btn) btn.setAttribute("aria-expanded", "false");
    };
    const scheduleClose = () => {
      closeTimer = setTimeout(closeMenu, 120);
    };
    dropdown.addEventListener("mouseenter", openMenu);
    dropdown.addEventListener("mouseleave", scheduleClose);
    if (btn) {
      btn.addEventListener("click", (event) => {
        event.preventDefault();
        if (dropdown.classList.contains("is-open")) closeMenu();
        else openMenu();
      });
      btn.addEventListener("focus", openMenu);
    }
    document.addEventListener("keydown", (event) => {
      if (event.key === "Escape") closeMenu();
    });
    document.addEventListener("click", (event) => {
      if (!dropdown.contains(event.target)) closeMenu();
    });
  }

  const measurePanel = document.querySelector("[data-measure-panel]");
  if (measurePanel) {
    const toggle = measurePanel.querySelector("[data-measure-toggle]");
    const body = measurePanel.querySelector("[data-measure-body]");
    const state = measurePanel.querySelector("[data-measure-state]");
    if (toggle && body) {
      toggle.addEventListener("click", () => {
        const open = body.hasAttribute("hidden");
        if (open) body.removeAttribute("hidden");
        else body.setAttribute("hidden", "");
        measurePanel.classList.toggle("is-open", open);
        toggle.setAttribute("aria-expanded", open ? "true" : "false");
        if (state) state.textContent = open ? "Fechar" : "Abrir";
      });
    }
    measurePanel.querySelectorAll('input[name="fitMode"]').forEach((input) => {
      input.addEventListener("change", () => {
        measurePanel.querySelectorAll(".measure-mode").forEach((label) => {
          const radio = label.querySelector('input[name="fitMode"]');
          label.classList.toggle("is-active", !!(radio && radio.checked));
        });
      });
    });
  }

  const searchToggle = document.querySelector("[data-search-toggle]");
  const searchPanel = document.querySelector("[data-header-search]");
  if (searchToggle && searchPanel) {
    searchToggle.addEventListener("click", () => {
      const open = searchPanel.classList.toggle("is-open");
      if (open) {
        searchPanel.removeAttribute("hidden");
        const input = searchPanel.querySelector("input[type=search]");
        if (input) input.focus();
      } else {
        searchPanel.setAttribute("hidden", "");
      }
    });
  }

  document.querySelectorAll("[data-product-rail]").forEach((rail) => {
    const track = rail.querySelector("[data-rail-track]");
    const prev = rail.querySelector("[data-rail-prev]");
    const next = rail.querySelector("[data-rail-next]");
    if (!track) return;
    const scrollBy = (dir) => {
      track.scrollBy({ left: dir * Math.min(track.clientWidth * 0.8, 560), behavior: "smooth" });
    };
    if (prev) prev.addEventListener("click", () => scrollBy(-1));
    if (next) next.addEventListener("click", () => scrollBy(1));
  });

  const tabs = document.querySelector("[data-carousel-tabs]");
  if (tabs) {
    const section = tabs.closest("section");
    const cards = section ? section.querySelectorAll(".product-card") : [];
    tabs.querySelectorAll("button[data-tab]").forEach((btn) => {
      btn.addEventListener("click", () => {
        tabs.querySelectorAll("button").forEach((b) => b.classList.remove("is-active"));
        btn.classList.add("is-active");
        const tab = (btn.getAttribute("data-tab") || "").toLowerCase();
        let visible = 0;
        cards.forEach((card) => {
          const cat = (card.getAttribute("data-category") || "").toLowerCase();
          const match =
            !tab ||
            cat.includes(tab.slice(0, -1)) ||
            cat.includes(tab) ||
            (tab === "blazers" && cat.includes("blazer")) ||
            (tab === "camisas" && cat.includes("camisa")) ||
            (tab === "vestidos" && cat.includes("vestido")) ||
            (tab === "sapatos" && cat.includes("sapato")) ||
            (tab === "bolsas" && cat.includes("bolsa"));
          card.style.display = match ? "" : "none";
          if (match) visible += 1;
        });
        if (visible === 0) {
          cards.forEach((card) => {
            card.style.display = "";
          });
        }
      });
    });
  }

  const filtersToggle = document.querySelector("[data-filters-toggle]");
  const filtersPanel = document.querySelector("[data-filters-panel]");
  if (filtersToggle && filtersPanel) {
    filtersToggle.addEventListener("click", () => {
      const open = filtersPanel.classList.toggle("is-open");
      filtersToggle.setAttribute("aria-expanded", open ? "true" : "false");
    });
  }

  document.querySelectorAll("[data-filters-collapse]").forEach((wrap) => {
    const toggle = wrap.querySelector("[data-filters-collapse-toggle]");
    const body = wrap.querySelector("[data-filters-collapse-body]");
    if (!toggle || !body) return;
    toggle.addEventListener("click", () => {
      const open = body.hasAttribute("hidden");
      if (open) body.removeAttribute("hidden");
      else body.setAttribute("hidden", "");
      wrap.classList.toggle("is-open", open);
      toggle.setAttribute("aria-expanded", open ? "true" : "false");
    });
  });

  const testimonials = document.querySelector("[data-testimonials]");
  if (testimonials) {
    const track = testimonials.querySelector("[data-testimonials-track]");
    const slides = track ? Array.from(track.querySelectorAll(".testimonial")) : [];
    const prev = testimonials.querySelector("[data-testimonials-prev]");
    const next = testimonials.querySelector("[data-testimonials-next]");
    let index = 0;
    const go = (nextIndex) => {
      if (!track || !slides.length) return;
      index = (nextIndex + slides.length) % slides.length;
      track.style.transform = `translateX(-${index * 100}%)`;
      slides.forEach((slide, i) => slide.classList.toggle("is-active", i === index));
    };
    if (prev) prev.addEventListener("click", () => go(index - 1));
    if (next) next.addEventListener("click", () => go(index + 1));
  }

  document.querySelectorAll("[data-reveal]").forEach((el, i) => {
    el.style.opacity = "0";
    el.style.transform = "translateY(14px)";
    el.style.transition = `opacity .55s ease ${i * 0.05}s, transform .55s ease ${i * 0.05}s`;
    const io = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            el.style.opacity = "1";
            el.style.transform = "none";
            io.disconnect();
          }
        });
      },
      { threshold: 0.12 }
    );
    io.observe(el);
  });

  const curadoria = document.querySelector("[data-curadoria]");
  if (curadoria) {
    const cards = Array.from(curadoria.querySelectorAll("[data-curadoria-card]"));
    const qInput = curadoria.querySelector("[data-curadoria-q]");
    const catSelect = curadoria.querySelector("[data-curadoria-category]");
    const styleSelect = curadoria.querySelector("[data-curadoria-style]");
    const sortSelect = curadoria.querySelector("[data-curadoria-sort]");
    const empty = curadoria.querySelector("[data-curadoria-empty]");
    const grid = curadoria.querySelector("[data-curadoria-grid]");
    const favCount = curadoria.querySelector("[data-fav-count]");
    const priceBtns = Array.from(curadoria.querySelectorAll("[data-curadoria-price]"));
    let price = "all";
    let favorites = new Set();

    const matchesPrice = (cents) => {
      if (price === "low") return cents <= 200000;
      if (price === "mid") return cents >= 200100 && cents <= 400000;
      if (price === "high") return cents > 400000;
      return true;
    };

    const updateFavLabel = () => {
      if (!favCount) return;
      const n = favorites.size;
      favCount.textContent = `${n} ${n === 1 ? "favorito" : "favoritos"}`;
    };

    const apply = () => {
      const q = (qInput?.value || "").trim().toLowerCase();
      const cat = catSelect?.value || "";
      const style = styleSelect?.value || "";
      const sort = sortSelect?.value || "featured";

      let visible = cards.filter((card) => {
        const name = card.getAttribute("data-name") || "";
        const cardCat = card.getAttribute("data-category") || "";
        const cardStyle = card.getAttribute("data-style") || "";
        const cents = Number(card.getAttribute("data-price") || "0");
        if (q && !name.includes(q)) return false;
        if (cat && cardCat !== cat) return false;
        if (style && cardStyle !== style) return false;
        if (!matchesPrice(cents)) return false;
        return true;
      });

      if (sort === "low" || sort === "high") {
        visible = [...visible].sort((a, b) => {
          const pa = Number(a.getAttribute("data-price") || "0");
          const pb = Number(b.getAttribute("data-price") || "0");
          return sort === "low" ? pa - pb : pb - pa;
        });
      }

      cards.forEach((card) => {
        card.hidden = true;
      });
      visible.forEach((card) => {
        card.hidden = false;
        if (grid) grid.appendChild(card);
      });

      if (empty) empty.hidden = visible.length > 0;
      if (grid) grid.hidden = visible.length === 0;
    };

    [qInput, catSelect, styleSelect, sortSelect].forEach((el) => {
      if (!el) return;
      el.addEventListener("input", apply);
      el.addEventListener("change", apply);
    });

    priceBtns.forEach((btn) => {
      btn.addEventListener("click", () => {
        price = btn.getAttribute("data-curadoria-price") || "all";
        priceBtns.forEach((b) => b.classList.toggle("is-active", b === btn));
        apply();
      });
    });

    const clearBtn = curadoria.querySelector("[data-curadoria-clear]");
    if (clearBtn) {
      clearBtn.addEventListener("click", () => {
        if (qInput) qInput.value = "";
        if (catSelect) catSelect.value = "";
        if (styleSelect) styleSelect.value = "";
        if (sortSelect) sortSelect.value = "featured";
        price = "all";
        priceBtns.forEach((b) => b.classList.toggle("is-active", b.getAttribute("data-curadoria-price") === "all"));
        apply();
      });
    }

    curadoria.querySelectorAll("[data-fav]").forEach((btn, index) => {
      const card = btn.closest("[data-curadoria-card]");
      const id = card?.getAttribute("data-name") || String(index);
      btn.addEventListener("click", () => {
        if (favorites.has(id)) favorites.delete(id);
        else favorites.add(id);
        btn.classList.toggle("is-liked", favorites.has(id));
        updateFavLabel();
      });
    });

    updateFavLabel();
    apply();
  }
})();
