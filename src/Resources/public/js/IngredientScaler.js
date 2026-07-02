/**
 * RecipeIngredientScaler
 * ------------------------------------------------------------------
 * Skaliert eine Zutatenliste dynamisch anhand einer eingegebenen
 * Portionszahl. Erwartet folgendes HTML-Grundgerüst (siehe demo.html):
 *
 * <div data-recipe-scaler>
 *   <div class="portion-control">
 *     <label for="portion-input">Portionen</label>
 *     <button type="button" data-action="decrease" aria-label="Portionen verringern">−</button>
 *     <input type="number" id="portion-input" data-portion-input
 *            data-base-portions="4" value="4" min="1" max="99" step="1">
 *     <button type="button" data-action="increase" aria-label="Portionen erhöhen">+</button>
 *     <button type="button" data-action="reset">Zurücksetzen</button>
 *   </div>
 *   <p data-scaler-status class="visually-hidden" aria-live="polite" aria-atomic="true"></p>
 *   <ul data-ingredient-list>
 *     <li data-base-amount="200" data-unit="g">
 *       <span data-amount>200</span> g Mehl
 *     </li>
 *     ...
 *   </ul>
 * </div>
 *
 * Anforderungen an das Markup:
 * - Jedes Zutat-Element trägt data-base-amount (Zahl, Basismenge bei
 *   der Ursprungsportionszahl). data-unit ist optional und rein
 *   informativ (wird nicht angefasst).
 * - Innerhalb jedes Zutat-Elements markiert [data-amount] den
 *   Text-Knoten, der die Menge enthält. Fehlt er, wird das gesamte
 *   Element als Textziel verwendet (nicht empfohlen, da Einheit/Name
 *   dann überschrieben würden).
 * - Zutaten ohne data-base-amount (z. B. "Salz nach Geschmack")
 *   werden ignoriert und bleiben unverändert.
 *
 * Barrierefreiheit (WCAG 2.1 AA):
 * - Alle Interaktionen laufen über <button> und <input type="number">,
 *   damit native Tastaturbedienbarkeit (Tab, Enter, Leertaste,
 *   Pfeiltasten im number-Feld) erhalten bleibt.
 * - Buttons erhalten aussagekräftige aria-label, falls nur Symbole
 *   ("−" / "+") verwendet werden.
 * - Ein separater, visuell versteckter Status-Bereich mit
 *   aria-live="polite" kündigt Änderungen für Screenreader an, ohne
 *   dass die komplette (potenziell lange) Zutatenliste bei jeder
 *   Änderung vorgelesen wird.
 * - Deaktivierte Buttons (disabled) bei Erreichen von Min/Max/Basiswert
 *   verhindern ungültige Eingaben, statt sie nur zu verstecken.
 * - Fokus-Styles sind bewusst NICHT Teil dieser Klasse (Trennung von
 *   Logik und Darstellung) – siehe demo.html für :focus-visible.
 */
class RecipeIngredientScaler {
  /**
   * @param {HTMLElement|string} container Root-Element oder CSS-Selektor
   * @param {Object} [options]
   * @param {number} [options.minPortions=1] Untere Grenze für die Portionszahl
   * @param {number} [options.maxPortions=99] Obere Grenze für die Portionszahl
   * @param {number} [options.step=1] Schrittweite für +/- Buttons
   * @param {number} [options.decimalPlaces=1] Max. Nachkommastellen bei der Anzeige
   */
  constructor(container, options = {}) {
    this.container =
      typeof container === 'string' ? document.querySelector(container) : container;

    if (!this.container) {
      throw new Error('RecipeIngredientScaler: Container element not found.');
    }

    this.list = this.container.querySelector('[data-ingredient-list]');
    this.input = this.container.querySelector('[data-portion-input]');
    this.decreaseBtn = this.container.querySelector('[data-action="decrease"]');
    this.increaseBtn = this.container.querySelector('[data-action="increase"]');
    this.resetBtn = this.container.querySelector('[data-action="reset"]');
    this.status = this.container.querySelector('[data-scaler-status]');

    if (!this.input) {
      throw new Error('RecipeIngredientScaler: [data-portion-input] fehlt im Markup.');
    }

    const basePortionsAttr = this.input.dataset.basePortions;
    this.basePortions = parseFloat(basePortionsAttr ?? this.input.value ?? 4);
    if (!this.basePortions || this.basePortions <= 0) {
      throw new Error(
        'RecipeIngredientScaler: Ungültige oder fehlende data-base-portions / value am Eingabefeld.'
      );
    }

    this.currentPortions = this.basePortions;
    this.minPortions = options.minPortions ?? parseFloat(this.input.min) ?? 1;
    this.maxPortions = options.maxPortions ?? parseFloat(this.input.max) ?? 99;
    this.step = options.step ?? parseFloat(this.input.step) ?? 1;
    this.decimalPlaces = options.decimalPlaces ?? 1;

    this.ingredients = this._collectIngredients();

    this._bindEvents();
    this._render();
  }

  /** Liest alle Zutat-Elemente mit Basismenge aus dem DOM ein. */
  _collectIngredients() {
    if (!this.list) return [];
    return Array.from(this.list.querySelectorAll('[data-base-amount]'))
      .map((el) => {
        const baseAmount = parseFloat(el.dataset.baseAmount);
        if (isNaN(baseAmount)) return null;
        return {
          element: el,
          amountEl: el.querySelector('[data-amount]') || el,
          baseAmount,
        };
      })
      .filter(Boolean);
  }

  _bindEvents() {
    this.decreaseBtn?.addEventListener('click', () => this.decrease());
    this.increaseBtn?.addEventListener('click', () => this.increase());
    this.resetBtn?.addEventListener('click', () => this.reset());

    // Direkte Eingabe: erst bei Bestätigung (change/Enter) übernehmen,
    // damit Zwischenzustände beim Tippen nicht sofort skalieren.
    this.input.addEventListener('change', (e) => {
      this.setPortions(parseFloat(e.target.value));
    });
    this.input.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        this.setPortions(parseFloat(e.target.value));
      }
    });
  }

  /** Erhöht die Portionszahl um `step`. */
  increase() {
    this.setPortions(this.currentPortions + this.step);
  }

  /** Verringert die Portionszahl um `step`. */
  decrease() {
    this.setPortions(this.currentPortions - this.step);
  }

  /** Setzt die Portionszahl auf den Ursprungswert zurück. */
  reset() {
    this.setPortions(this.basePortions);
  }

  /**
   * Setzt die Portionszahl explizit (z. B. nach manueller Eingabe),
   * begrenzt sie auf [minPortions, maxPortions] und skaliert die
   * Zutatenliste entsprechend neu.
   * @param {number} value
   */
  setPortions(value) {
    if (isNaN(value)) {
      value = this.currentPortions;
    }
    value = Math.min(this.maxPortions, Math.max(this.minPortions, value));

    this.currentPortions = value;
    this._render();
  }

  /** Aktualisiert Eingabefeld, Zutatenmengen, Button-Status und Live-Region. */
  _render() {
    this.input.value = this._formatAmount(this.currentPortions);
    this._scaleIngredients();
    this._updateButtonStates();
    this._announce();
  }

  _scaleIngredients() {
    const factor = this.currentPortions / this.basePortions;
    this.ingredients.forEach((ingredient) => {
      const scaled = ingredient.baseAmount * factor;
      ingredient.amountEl.textContent = this._formatAmount(scaled);
    });
  }

  /** Formatiert eine Zahl mit deutschem Dezimaltrennzeichen, ohne unnötige Nachkommastellen. */
  _formatAmount(value) {
    const factor = Math.pow(10, this.decimalPlaces);
    const rounded = Math.round(value * factor) / factor;
    return rounded.toLocaleString('de-DE', { maximumFractionDigits: this.decimalPlaces });
  }

  _updateButtonStates() {
    if (this.decreaseBtn) {
      this.decreaseBtn.disabled = this.currentPortions <= this.minPortions;
    }
    if (this.increaseBtn) {
      this.increaseBtn.disabled = this.currentPortions >= this.maxPortions;
    }
    if (this.resetBtn) {
      this.resetBtn.disabled = this.currentPortions === this.basePortions;
    }
  }

  /** Kündigt die neue Portionszahl für Screenreader-Nutzer:innen an. */
  _announce() {
    if (!this.status) return;
    const formatted = this._formatAmount(this.currentPortions);
    const einheit = this.currentPortions === 1 ? 'Portion' : 'Portionen';
    this.status.textContent = `Zutaten für ${formatted} ${einheit} berechnet.`;
  }
}

// Für den Einsatz als ES-Modul:
// export default RecipeIngredientScaler;
