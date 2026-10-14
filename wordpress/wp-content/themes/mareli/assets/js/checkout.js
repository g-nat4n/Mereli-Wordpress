(function ($) {
  function formatCpf(v) {
    v = (v || "").replace(/\D/g, "").slice(0, 11);
    return v
      .replace(/(\d{3})(\d)/, "$1.$2")
      .replace(/(\d{3})(\d)/, "$1.$2")
      .replace(/(\d{3})(\d{1,2})$/, "$1-$2");
  }
  function formatPhone(v) {
    v = (v || "").replace(/\D/g, "").slice(0, 11);
    if (v.length > 10) return v.replace(/(\d{2})(\d{5})(\d{4})/, "($1) $2-$3");
    return v.replace(/(\d{2})(\d{4})(\d{0,4})/, "($1) $2-$3");
  }
  function formatCep(v) {
    v = (v || "").replace(/\D/g, "").slice(0, 8);
    return v.replace(/(\d{5})(\d{1,3})/, "$1-$2");
  }

  $(document).on("input", "#billing_cpf", function () {
    this.value = formatCpf(this.value);
  });
  $(document).on("input", "#billing_phone", function () {
    this.value = formatPhone(this.value);
  });
  $(document).on("input", "#billing_postcode", function () {
    this.value = formatCep(this.value);
  });

  // Coupon on checkout
  $(document).on("click", "#mareli_apply_coupon", function (e) {
    e.preventDefault();
    if (typeof MareliCart === "undefined") return;
    const code = $("#mareli_checkout_coupon").val();
    const $msg = $("#mareli_coupon_msg");
    $.post(MareliCart.ajaxUrl, {
      action: "mareli_apply_coupon",
      nonce: MareliCart.nonce,
      coupon: code,
    })
      .done(function (res) {
        if (!res || !res.success) {
          $msg.text((res && res.data && res.data.message) || "Cupom inválido").prop("hidden", false);
          return;
        }
        $msg.text(res.data.message).prop("hidden", false);
        $("body").trigger("update_checkout");
      })
      .fail(function (xhr) {
        const msg =
          (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) ||
          "Cupom inválido";
        $msg.text(msg).prop("hidden", false);
      });
  });

  // Qty +/- in order summary
  $(document).on("click", "[data-qty-minus], [data-qty-plus]", function () {
    const wrap = $(this).closest("[data-qty]");
    const key = wrap.data("qty");
    const isPlus = $(this).is("[data-qty-plus]");
    const $span = wrap.find("span");
    let qty = parseInt($span.text(), 10) || 1;
    qty = isPlus ? qty + 1 : Math.max(1, qty - 1);
    $span.text(qty);
    $.post(wc_checkout_params.ajax_url || (window.MareliCart && MareliCart.ajaxUrl), {
      action: "mareli_update_qty",
      nonce: MareliCart.nonce,
      cart_item_key: key,
      quantity: qty,
    }).done(function () {
      $("body").trigger("update_checkout");
    });
  });

  // After CEP fill from Mareli-correios, refresh shipping UI copy
  $(document.body).on("updated_checkout", function () {
    const $methods = $("#Mareli-shipping-methods");
    if ($methods.find(".woocommerce-shipping-methods, .Mareli-shipping-table tr, .woocommerce-shipping-methods li").length === 0) {
      if (!$methods.find(".Mareli-checkout__hint").length) {
        $methods.html(
          '<p class="Mareli-checkout__hint">Informe o CEP para ver as opções reais de envio.</p>'
        );
      }
    }
  });

  function MareliLoaderLogoUrl() {
    if (window.MareliCheckout && MareliCheckout.logoUrl) return MareliCheckout.logoUrl;
    return "";
  }

  function MareliEnsureCheckoutLoader() {
    if (document.getElementById("Mareli-checkout-loader")) return;
    const logo = MareliLoaderLogoUrl();
    const logoHtml = logo
      ? '<img class="Mareli-checkout-loader__logo" src="' +
        logo +
        '" alt="mareli" width="72" height="72" />'
      : '<span class="Mareli-checkout-loader__logo-fallback" aria-hidden="true">V</span>';
    const el = document.createElement("div");
    el.id = "Mareli-checkout-loader";
    el.className = "Mareli-checkout-loader";
    el.setAttribute("hidden", "");
    el.setAttribute("aria-live", "assertive");
    el.setAttribute("aria-busy", "true");
    el.innerHTML =
      '<div class="Mareli-checkout-loader__card">' +
      '<div class="Mareli-checkout-loader__logo-wrap" aria-hidden="true">' +
      logoHtml +
      '<span class="Mareli-checkout-loader__ring"></span>' +
      "</div>" +
      '<p class="Mareli-checkout-loader__brand">Mareli</p>' +
      '<p class="Mareli-checkout-loader__text">Processando pagamento…</p>' +
      '<p class="Mareli-checkout-loader__hint">Não feche esta página. Estamos confirmando com o Mercado Pago.</p>' +
      "</div>";
    document.body.appendChild(el);
  }

  var MareliLoaderTimer = null;
  var MareliLoaderWatch = null;
  var MareliLoaderArmed = false;

  function MareliShowCheckoutLoader(message) {
    MareliEnsureCheckoutLoader();
    const el = document.getElementById("Mareli-checkout-loader");
    if (!el) return;
    const text = el.querySelector(".Mareli-checkout-loader__text");
    const hint = el.querySelector(".Mareli-checkout-loader__hint");
    if (text) text.textContent = message || "Processando pagamento…";
    if (hint) {
      hint.textContent = "Não feche esta página. Estamos confirmando com o Mercado Pago.";
    }
    el.removeAttribute("hidden");
    document.body.classList.add("Mareli-checkout-loading");
  }

  function MareliHideCheckoutLoader(opts) {
    opts = opts || {};
    MareliLoaderArmed = false;
    if (MareliLoaderTimer) {
      clearTimeout(MareliLoaderTimer);
      MareliLoaderTimer = null;
    }
    if (MareliLoaderWatch) {
      clearInterval(MareliLoaderWatch);
      MareliLoaderWatch = null;
    }
    const el = document.getElementById("Mareli-checkout-loader");
    if (el) el.setAttribute("hidden", "");
    document.body.classList.remove("Mareli-checkout-loading", "processing");
    const $btn = $("#place_order");
    if ($btn.length) {
      $btn.text($btn.data("orig") || $btn.data("value") || "Finalizar pedido").prop("disabled", false);
    }
    if (opts.focusErrors) {
      const err = document.querySelector(
        ".woocommerce-error, .woocommerce-NoticeGroup-checkout, .mp-alert, .mp-form-error, .mp-error, [data-checkout='cardNumber'], #form-checkout__cardNumber"
      );
      if (err && typeof err.scrollIntoView === "function") {
        err.scrollIntoView({ behavior: "smooth", block: "center" });
      }
    }
  }

  function MareliCheckoutHasErrors() {
    const groups = document.querySelectorAll(
      ".woocommerce-error, .woocommerce-NoticeGroup-checkout .woocommerce-error, .woocommerce-notices-wrapper .woocommerce-error"
    );
    for (let i = 0; i < groups.length; i++) {
      if (groups[i].children.length || (groups[i].textContent || "").trim()) return true;
    }
    // Alertas do Mercado Pago (cartão inválido / negado / campos)
    const mpErr = document.querySelector(
      ".mp-alert-message, .mp-form-message--error, .mp-input-error, .mp-error, .mp-checkout-custom-alert, .mp-status-red"
    );
    if (mpErr && (mpErr.offsetParent !== null || (mpErr.textContent || "").trim())) return true;
    return false;
  }

  function MareliMpCardLooksFilled() {
    if (!MareliIsMpCustomSelected()) return true;
    const num =
      document.querySelector("#form-checkout__cardNumber input, #form-checkout__cardNumber, [data-checkout='cardNumber'] input") ||
      document.querySelector("input[name='mercadopago_custom[cardNumber]']");
    const name =
      document.querySelector("#form-checkout__cardholderName, input[name='mercadopago_custom[cardholderName]']");
    const rawNum = num ? String(num.value || num.textContent || "").replace(/\D/g, "") : "";
    const rawName = name ? String(name.value || "").trim() : "";
    // Secure Fields às vezes não expõem value — se iframe existe, deixa seguir
    const hasIframe = !!document.querySelector(
      "#form-checkout__cardNumber iframe, .mp-checkout-custom-card-form iframe"
    );
    if (hasIframe && rawNum.length < 4) {
      // iframe: não dá para ler; só bloqueia se campos obrigatórios vizinhos vazios e sem token
      const token = document.querySelector("input[name='mercadopago_custom[token]'], #MPHiddenInputToken");
      if (token && token.value) return true;
      if (!rawName) return false;
      return true;
    }
    if (rawNum.length > 0 && rawNum.length < 12) return false;
    if (!hasIframe && rawNum.length < 12) return false;
    if (!hasIframe && !rawName) return false;
    return true;
  }

  function MareliArmPayLoader() {
    if (MareliLoaderArmed) return;
    MareliLoaderArmed = true;
    const $btn = $("#place_order");
    if ($btn.length) {
      if (!$btn.data("orig")) $btn.data("orig", $btn.text());
      $btn.text("Processando…").prop("disabled", true);
    }
    MareliShowCheckoutLoader("Processando pagamento…");

    // Timeout de segurança — nunca fica infinito
    if (MareliLoaderTimer) clearTimeout(MareliLoaderTimer);
    MareliLoaderTimer = setTimeout(function () {
      MareliHideCheckoutLoader({ focusErrors: true });
    }, 22000);

    // Observa erro do Woo / MP e fecha na hora
    if (MareliLoaderWatch) clearInterval(MareliLoaderWatch);
    MareliLoaderWatch = setInterval(function () {
      if (!document.body.classList.contains("Mareli-checkout-loading")) {
        clearInterval(MareliLoaderWatch);
        MareliLoaderWatch = null;
        return;
      }
      if (MareliCheckoutHasErrors()) {
        MareliHideCheckoutLoader({ focusErrors: true });
      }
    }, 350);
  }

  MareliEnsureCheckoutLoader();

  // Só arma o loader quando o Woo realmente inicia o pedido (validação ok).
  $(document.body).on("checkout_place_order", function () {
    if (MareliIsMpCustomSelected() && !MareliMpCardLooksFilled()) {
      MareliHideCheckoutLoader({ focusErrors: true });
      return true; // deixa o MP/Woo mostrar o erro do campo
    }
    MareliArmPayLoader();
  });

  // PIX / order-pay: submit real
  $(document).on("submit", "form.checkout, form#order_review", function () {
    if (MareliCheckoutHasErrors()) {
      MareliHideCheckoutLoader({ focusErrors: true });
      return;
    }
    if (MareliIsMpCustomSelected() && !MareliMpCardLooksFilled()) {
      // Não trava a tela se o cartão não foi preenchido
      setTimeout(function () {
        if (MareliCheckoutHasErrors() || !document.body.classList.contains("processing")) {
          MareliHideCheckoutLoader({ focusErrors: true });
        }
      }, 400);
      return;
    }
    MareliArmPayLoader();
  });

  $(document).on("click", "#place_order, .Mareli-place-order, .Mareli-pay__btn", function () {
    // Se crédito sem dados, não abre overlay infinito
    if (MareliIsMpCustomSelected() && !MareliMpCardLooksFilled()) {
      setTimeout(function () {
        MareliHideCheckoutLoader({ focusErrors: true });
      }, 200);
      return;
    }
  });

  $(document.body).on("checkout_error", function () {
    MareliHideCheckoutLoader({ focusErrors: true });
  });

  // Clique em "fechar" / escape no overlay (só se erro)
  $(document).on("keydown", function (e) {
    if (e.key === "Escape" && document.body.classList.contains("Mareli-checkout-loading")) {
      MareliHideCheckoutLoader({ focusErrors: true });
    }
  });

  /**
   * Parcelas sempre visíveis no cartão (MP às vezes esconde o select por race).
   */
  var MareliMpLastBin = "";
  var MareliMpFetchBusy = false;

  function MareliIsMpCustomSelected() {
    return (
      document.querySelector('input[name="payment_method"]:checked')?.value ===
      "woo-mercado-pago-custom"
    );
  }

  function MareliFormatBrl(n) {
    return n.toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
  }

  function MareliGetCheckoutAmount() {
    const amountEl = document.getElementById("mp-amount");
    const raw = String((amountEl && amountEl.value) || "0").replace(",", ".");
    const n = parseFloat(raw);
    return Number.isFinite(n) && n > 0 ? n : 0;
  }

  function MareliShowInstallmentsCard() {
    const card = document.getElementById("mp-checkout-custom-installments-card");
    if (!card) return;
    card.classList.remove("mp-checkout-custom-installments-display-none");
    card.classList.add("mp-checkout-custom-installments");
    card.style.setProperty("display", "block", "important");
    card.style.setProperty("visibility", "visible", "important");
    card.style.setProperty("height", "auto", "important");
    card.style.setProperty("margin-top", "1rem", "important");
  }

  function MareliFillFallbackInstallments() {
    const sel = document.getElementById("form-checkout__installments");
    const amount = MareliGetCheckoutAmount();
    if (!sel || amount <= 0) return;
    const hasReal = sel.querySelectorAll("option[datsobre-real='1']").length > 0;
    if (hasReal) return;
    const existing = sel.querySelectorAll("option:not([data-placeholder]):not([disabled])").length;
    if (existing >= 2 && !sel.querySelector("option[datsobre-fallback]")) return;

    const max = amount >= 50 ? 12 : 1;
    sel.innerHTML = "";
    const ph = document.createElement("option");
    ph.textContent = "Selecione as parcelas";
    ph.disabled = true;
    ph.selected = true;
    ph.setAttribute("data-placeholder", "");
    sel.appendChild(ph);
    for (let i = 1; i <= max; i++) {
      const opt = document.createElement("option");
      opt.value = String(i);
      opt.setAttribute("datsobre-fallback", "1");
      const parcela = amount / i;
      opt.textContent =
        i === 1
          ? "1x de " + MareliFormatBrl(amount)
          : i + "x de " + MareliFormatBrl(parcela) + " (sem juros*)";
      sel.appendChild(opt);
    }
    const hidden = document.getElementById("cardInstallments");
    if (hidden && !hidden.value) hidden.value = "1";
  }

  function MareliPatchMpInstallments() {
    if (typeof CheckoutPage === "undefined" || CheckoutPage.__MareliInstallmentsPatched) {
      return typeof CheckoutPage !== "undefined";
    }
    CheckoutPage.__MareliInstallmentsPatched = true;
    CheckoutPage.installmentsEnabled = true;

    const originalSet = CheckoutPage.setChangeEventOnInstallments.bind(CheckoutPage);
    CheckoutPage.setChangeEventOnInstallments = function (data) {
      CheckoutPage.installmentsEnabled = true;
      const out = originalSet(data);
      MareliShowInstallmentsCard();
      const sel = document.getElementById("form-checkout__installments");
      if (sel) {
        Array.prototype.forEach.call(sel.options, function (opt) {
          if (opt.value && !opt.disabled) opt.setAttribute("datsobre-real", "1");
        });
      }
      return out;
    };

    const originalShould = CheckoutPage.shouldEnableInstallmentsComponent.bind(CheckoutPage);
    CheckoutPage.shouldEnableInstallmentsComponent = function (type) {
      originalShould(type);
      if (type !== "debit_card") {
        CheckoutPage.installmentsEnabled = true;
        MareliShowInstallmentsCard();
      }
    };

    if (typeof CheckoutPage.setElementDisplay === "function") {
      const originalDisplay = CheckoutPage.setElementDisplay.bind(CheckoutPage);
      CheckoutPage.setElementDisplay = function (key, value) {
        if (key === "mpInstallmentsCard" && value === "none" && MareliIsMpCustomSelected()) {
          return originalDisplay(key, "block");
        }
        return originalDisplay(key, value);
      };
    }

    return true;
  }

  function MareliReadMpBin() {
    const cf =
      (window.mpEventHandler && window.mpEventHandler.cardForm) ||
      (window.mpCustomCheckoutHandler && window.mpCustomCheckoutHandler.cardForm);
    const fromForm = (cf && (cf.currentBin || cf.lastVerdictBin)) || "";
    if (fromForm && String(fromForm).length >= 6) return String(fromForm).replace(/\D/g, "").slice(0, 8);

    const pm = document.getElementById("paymentMethodId");
    // Sem BIN no form: usa Mastercard comum só para listar opções até o cartão real chegar.
    if (pm && pm.value) return MareliMpLastBin || "51629222";
    return MareliMpLastBin || "";
  }

  function MareliFetchMpInstallments(forceBin) {
    if (!MareliIsMpCustomSelected()) return;
    if (typeof MercadoPago === "undefined" && !window.mpSdkInstance) return;
    if (typeof wc_mercadopago_custom_checkout_params === "undefined") return;

    const amount = MareliGetCheckoutAmount();
    if (amount <= 0) return;

    const bin = (forceBin || MareliReadMpBin() || "51629222").replace(/\D/g, "").slice(0, 8);
    if (bin.length < 6 || MareliMpFetchBusy) return;
    if (!forceBin && bin === MareliMpLastBin && document.querySelector("#form-checkout__installments option[datsobre-real='1']")) {
      MareliShowInstallmentsCard();
      return;
    }

    MareliMpFetchBusy = true;
    MareliMpLastBin = bin;
    const mp =
      window.mpSdkInstance ||
      new MercadoPago(wc_mercadopago_custom_checkout_params.public_key, {
        locale: wc_mercadopago_custom_checkout_params.locale || "pt-BR",
      });

    if (typeof CheckoutPage !== "undefined") {
      CheckoutPage.installmentsEnabled = true;
    }

    mp.getInstallments({ amount: String(amount), bin: bin })
      .then(function (res) {
        if (res && res[0] && typeof CheckoutPage !== "undefined") {
          CheckoutPage.setChangeEventOnInstallments(res[0]);
        } else {
          MareliFillFallbackInstallments();
          MareliShowInstallmentsCard();
        }
      })
      .catch(function () {
        MareliFillFallbackInstallments();
        MareliShowInstallmentsCard();
      })
      .finally(function () {
        MareliMpFetchBusy = false;
      });
  }

  function MareliEnsureInstallmentsVisible() {
    if (!MareliIsMpCustomSelected()) return;
    MareliPatchMpInstallments();
    MareliShowInstallmentsCard();
    MareliFillFallbackInstallments();
    MareliFetchMpInstallments();
  }

  let mpPatchTries = 0;
  const mpPatchTimer = setInterval(function () {
    const ready = MareliPatchMpInstallments();
    if (MareliIsMpCustomSelected()) MareliEnsureInstallmentsVisible();
    if (ready || ++mpPatchTries > 60) clearInterval(mpPatchTimer);
  }, 100);

  /**
   * PIX e cartão ficam abertos (sem acordeão).
   * O MP Secure Fields bugava ao montar com display:none → abrir depois.
   */
  function MareliPaymentRoot() {
    return document.querySelector(".Mareli-pay-methods, #payment .payment_methods");
  }

  function MareliOpenAllPaymentBoxes() {
    const root = MareliPaymentRoot();
    if (!root) return;
    root.classList.remove("Mareli-pay-accordion");
    root.classList.add("Mareli-pay-open");
    root.querySelectorAll(".wc_payment_method").forEach(function (li) {
      li.classList.add("is-open");
      const box = li.querySelector(".payment_box");
      if (box) {
        box.style.display = "block";
        box.style.visibility = "visible";
        box.style.opacity = "1";
        box.style.height = "auto";
        box.style.overflow = "visible";
      }
    });
  }

  function MareliRefreshMpCardForm() {
    const box = document.querySelector(
      ".payment_method_woo-mercado-pago-custom .payment_box, li.payment_method_woo-mercado-pago-custom .payment_box"
    );
    if (box) {
      box.style.display = "block";
      box.style.visibility = "visible";
      box.style.opacity = "1";
      box.style.height = "auto";
      box.style.overflow = "visible";
    }
    try {
      window.dispatchEvent(new Event("resize"));
    } catch (e) {}
    if (MareliIsMpCustomSelected()) {
      MareliEnsureInstallmentsVisible();
      setTimeout(MareliEnsureInstallmentsVisible, 300);
      setTimeout(MareliEnsureInstallmentsVisible, 900);
    }
  }

  $(document.body).on("updated_checkout payment_method_selected", function () {
    setTimeout(MareliOpenAllPaymentBoxes, 40);
    setTimeout(MareliOpenAllPaymentBoxes, 250);
    setTimeout(MareliRefreshMpCardForm, 300);
  });

  $(document).on("change", 'input[name="payment_method"]', function () {
    MareliOpenAllPaymentBoxes();
    setTimeout(MareliRefreshMpCardForm, 100);
    setTimeout(MareliEnsureInstallmentsVisible, 200);
  });

  setTimeout(MareliOpenAllPaymentBoxes, 80);
  setTimeout(MareliOpenAllPaymentBoxes, 400);
  setTimeout(MareliRefreshMpCardForm, 500);

  setInterval(function () {
    if (!MareliIsMpCustomSelected()) return;
    MareliShowInstallmentsCard();
    const cf =
      (window.mpEventHandler && window.mpEventHandler.cardForm) ||
      (window.mpCustomCheckoutHandler && window.mpCustomCheckoutHandler.cardForm);
    const bin = cf && (cf.currentBin || cf.lastVerdictBin);
    if (bin && String(bin).length >= 6 && String(bin) !== MareliMpLastBin) {
      MareliFetchMpInstallments(String(bin));
    } else if (!document.querySelector("#form-checkout__installments option:not([disabled])")) {
      MareliEnsureInstallmentsVisible();
    }
  }, 700);
})(jQuery);
