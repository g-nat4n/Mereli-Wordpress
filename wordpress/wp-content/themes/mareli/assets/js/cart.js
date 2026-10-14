(function ($) {
  if (typeof MareliCart === "undefined") return;

  const drawer = document.querySelector("[data-cart-drawer]");
  const body = document.body;

  function openDrawer() {
    if (!drawer) return;
    drawer.hidden = false;
    requestAnimationFrame(() => drawer.classList.add("is-open"));
    body.classList.add("Mareli-drawer-open");
  }

  function closeDrawer() {
    if (!drawer) return;
    drawer.classList.remove("is-open");
    body.classList.remove("Mareli-drawer-open");
    setTimeout(() => {
      if (!drawer.classList.contains("is-open")) drawer.hidden = true;
    }, 420);
  }

  function applyFragments(fragments) {
    if (!fragments) return;
    Object.keys(fragments).forEach((selector) => {
      const html = fragments[selector];
      const $targets = $(selector);
      if ($targets.length) {
        $targets.replaceWith(html);
      }
    });
    $(document.body).trigger("wc_fragments_refreshed");
  }

  function addToCart(productId, opts) {
    const options = opts || {};
    return $.post(MareliCart.ajaxUrl, {
      action: "mareli_add_to_cart",
      nonce: MareliCart.nonce,
      product_id: productId,
      quantity: options.quantity || 1,
      buy_now: options.buyNow ? 1 : 0,
    }).then(function (res) {
      if (!res || !res.success) {
        const msg = (res && res.data && res.data.message) || MareliCart.i18n.error;
        throw new Error(msg);
      }
      applyFragments(res.data.fragments);
      if (res.data.redirect) {
        window.open(res.data.redirect, "_blank", "noopener,noreferrer");
        return res.data;
      }
      if (res.data.open !== false) openDrawer();
      return res.data;
    });
  }

  function removeFromCart(key) {
    return $.post(MareliCart.ajaxUrl, {
      action: "mareli_remove_from_cart",
      nonce: MareliCart.nonce,
      cart_item_key: key,
    }).then(function (res) {
      if (!res || !res.success) throw new Error(MareliCart.i18n.error);
      applyFragments(res.data.fragments);
      if (document.body.classList.contains("woocommerce-checkout")) {
        $(document.body).trigger("update_checkout");
        if (res.data.count === 0) {
          window.location.href = MareliCart.cartUrl || "/carrinho/";
        }
        return res.data;
      }
      if (document.querySelector(".Mareli-cart") && res.data.count === 0) {
        window.location.reload();
      } else if (document.querySelector(".Mareli-cart__item")) {
        const row = document.querySelector('[data-remove-cart="' + key + '"]');
        if (row) {
          const item = row.closest(".Mareli-cart__item");
          if (item) item.remove();
        }
        if (!document.querySelector(".Mareli-cart__item")) window.location.reload();
      }
      const accountItem = document.querySelector('[data-account-bag-item="' + key + '"]');
      if (accountItem) {
        accountItem.remove();
        if (!document.querySelector("[data-account-bag-item]")) {
          window.location.reload();
        }
      }
      return res.data;
    });
  }

  // Bag icon opens drawer
  $(document).on("click", "[data-open-cart]", function (e) {
    e.preventDefault();
    openDrawer();
  });

  $(document).on("click", "[data-cart-close]", function (e) {
    e.preventDefault();
    closeDrawer();
  });

  $(document).on("keydown", function (e) {
    if (e.key === "Escape") closeDrawer();
  });

  // Quick add from cards / AJAX buttons
  $(document).on("click", "[data-add-to-cart]", function (e) {
    e.preventDefault();
    const id = $(this).data("add-to-cart");
    if (!id) return;
    const $btn = $(this);
    $btn.addClass("is-loading").prop("disabled", true);
    addToCart(id)
      .catch(function (err) {
        window.alert(err.message || MareliCart.i18n.error);
      })
      .always(function () {
        $btn.removeClass("is-loading").prop("disabled", false);
      });
  });

  // Buy now
  $(document).on("click", "[data-buy-now]", function (e) {
    e.preventDefault();
    const id = $(this).data("buy-now");
    if (!id) return;
    addToCart(id, { buyNow: true }).catch(function (err) {
      window.alert(err.message || MareliCart.i18n.error);
    });
  });

  // Remove
  $(document).on("click", "[data-remove-cart]", function (e) {
    e.preventDefault();
    const key = $(this).data("remove-cart");
    if (!key) return;
    removeFromCart(key).catch(function (err) {
      window.alert(err.message || MareliCart.i18n.error);
    });
  });

  // Intercept single product form submit → AJAX + drawer
  $(document).on("submit", "form.cart", function (e) {
    const $form = $(this);
    const $btn = $form.find('[type="submit"]');
    const productId =
      $form.find("[name=add-to-cart]").val() ||
      $form.find("button[name=add-to-cart]").val() ||
      $form.data("product_id");
    if (!productId) return;
    e.preventDefault();
    const qty = parseInt($form.find("input.qty").val() || "1", 10) || 1;
    $btn.prop("disabled", true);
    addToCart(productId, { quantity: qty })
      .catch(function (err) {
        window.alert(err.message || MareliCart.i18n.error);
      })
      .always(function () {
        $btn.prop("disabled", false);
      });
  });

  // Coupon on cart page
  $(document).on("submit", "[data-cart-coupon]", function (e) {
    e.preventDefault();
    const $form = $(this);
    const code = $form.find("[name=coupon]").val();
    const $msg = $form.find("[data-coupon-msg]");
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
        applyFragments(res.data.fragments);
        if (typeof res.data.discount !== "undefined") {
          $("[data-cart-discount]").text("- R$ " + Number(res.data.discount).toFixed(2).replace(".", ","));
        }
        window.location.reload();
      })
      .fail(function (xhr) {
        const msg =
          (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) ||
          "Cupom inválido";
        $msg.text(msg).prop("hidden", false);
      });
  });

  // Woo native added_to_cart event also opens drawer
  $(document.body).on("added_to_cart", function () {
    openDrawer();
  });
})(jQuery);
