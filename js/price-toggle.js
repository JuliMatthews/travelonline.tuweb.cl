// Toggle CLP/USD/EUR en la página de detalle de paquete — mismo cálculo que
// inc/currency.php (duplicado en JS para que el cambio de moneda sea
// instantáneo sin ida y vuelta al servidor; el precio nunca se manda al
// backend desde acá, es solo visualización).
(function () {
  var CLP_PER_UNIT = { CLP: 1, USD: 950, EUR: 1020 };
  var SYMBOL = { CLP: "$", USD: "US$", EUR: "€" };

  function formatPrice(clp, currency) {
    var amount = Math.round(clp / CLP_PER_UNIT[currency]);
    return SYMBOL[currency] + amount.toLocaleString("es-CL");
  }

  var block = document.getElementById("price-block");
  if (!block) return;
  var hasPrice = block.dataset.hasPrice === "1";
  var priceClp = parseInt(block.dataset.priceClp, 10);
  var valueEl = document.getElementById("price-value");
  var buttons = block.querySelectorAll(".price-currency-btn");

  if (!hasPrice) return;

  buttons.forEach(function (btn) {
    btn.addEventListener("click", function () {
      var currency = btn.dataset.currency;
      buttons.forEach(function (b) {
        b.classList.toggle("bg-brand", b === btn);
        b.classList.toggle("text-white", b === btn);
        b.classList.toggle("text-brand-dark", b !== btn);
      });
      valueEl.textContent = "Desde " + formatPrice(priceClp, currency);
    });
  });
})();
