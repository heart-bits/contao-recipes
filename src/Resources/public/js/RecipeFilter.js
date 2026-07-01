/**
 * RecipeFilter
 * -------------------------------------------------------------------------
 * Filtert eine Liste von Rezept-Elementen anhand von data-categories und
 * data-ingredients Attributen am Container.
 *
 * Logik:
 *  - INNERHALB einer Filtergruppe (Kategorien bzw. Zutaten) wird mit ODER
 *    verknüpft: z. B. "vegan" ODER "glutenfrei" zeigt Rezepte, die mind.
 *    eine der beiden Kategorien erfüllen.
 *  - ZWISCHEN den Gruppen wird mit UND verknüpft: gewählte Kategorie UND
 *    gewählte Zutat müssen beide zutreffen.
 *  Das ist das gängige Verhalten von Facetten-Filtern (z. B. Online-Shops)
 *  und meist intuitiver als reines UND/ODER über alles. Falls eine andere
 *  Logik gewünscht ist, lässt sich das in matches() leicht anpassen.
 *
 * Accessibility-Hinweise sind inline kommentiert.
 */
class RecipeFilter {
  /**
   * @param {Object} options
   * @param {string} options.listSelector - Selector des Containers, der die Rezept-Items enthält
   * @param {string} options.itemSelector - Selector der einzelnen Rezept-Items innerhalb des Containers
   * @param {string} [options.categoryAttr='categories'] - data-Attribut-Name (ohne "data-") für Kategorien
   * @param {string} [options.ingredientAttr='ingredients'] - data-Attribut-Name (ohne "data-") für Zutaten
   * @param {string} [options.statusSelector] - Optionaler Selector für eine aria-live Statusmeldung
   * @param {string} [options.noResultsSelector] - Optionaler Selector für eine "keine Ergebnisse"-Meldung
   */
  constructor({
                listSelector,
                itemSelector,
                categoryAttr = "categories",
                ingredientAttr = "ingredients",
                statusSelector = null,
                noResultsSelector = null,
              }) {
    this.listEl = document.querySelector(listSelector);
    if (!this.listEl) {
      throw new Error(`RecipeFilter: Container "${listSelector}" nicht gefunden.`);
    }

    this.itemSelector = itemSelector;
    this.categoryAttr = categoryAttr;
    this.ingredientAttr = ingredientAttr;
    this.statusEl = statusSelector ? document.querySelector(statusSelector) : null;
    this.noResultsEl = noResultsSelector ? document.querySelector(noResultsSelector) : null;

    this.activeCategories = new Set();
    this.activeIngredients = new Set();

    this.items = Array.from(this.listEl.querySelectorAll(this.itemSelector));
  }

  /** Liest ein kommagetrenntes data-Attribut als Set von kleingeschriebenen, getrimmten Werten */
  _readAttrSet(el, attrName) {
    const raw = el.dataset[attrName] || "";
    return new Set(
      raw
        .split(",")
        .map((v) => v.trim().toLowerCase())
        .filter(Boolean)
    );
  }

  /** Prüft, ob ein einzelnes Rezept-Element den aktuellen Filtern entspricht */
  matches(el) {
    const itemCategories = this._readAttrSet(el, this.categoryAttr);
    const itemIngredients = this._readAttrSet(el, this.ingredientAttr);

    const categoryOk =
      this.activeCategories.size === 0 ||
      [...this.activeCategories].some((c) => itemCategories.has(c));

    const ingredientOk =
      this.activeIngredients.size === 0 ||
      [...this.activeIngredients].some((i) => itemIngredients.has(i));

    return categoryOk && ingredientOk;
  }

  /** Setzt aktive Kategorie- und Zutatenfilter und wendet sie an */
  setFilters({ categories = [], ingredients = [] } = {}) {
    this.activeCategories = new Set(categories.map((c) => c.toLowerCase()));
    this.activeIngredients = new Set(ingredients.map((i) => i.toLowerCase()));
    this.apply();
  }

  /** Setzt alle Filter zurück */
  reset() {
    this.activeCategories.clear();
    this.activeIngredients.clear();
    this.apply();
  }

  /** Wendet die aktuellen Filter auf die DOM-Items an */
  apply() {
    let visibleCount = 0;

    this.items.forEach((el) => {
      const isMatch = this.matches(el);
      // "hidden"-Attribut statt nur CSS-display verwenden:
      // Das nimmt das Element zuverlässig auch aus dem Accessibility-Baum,
      // sodass Screenreader gefilterte Rezepte nicht mehr ansagen.
      el.hidden = !isMatch;
      if (isMatch) visibleCount++;
    });

    if (this.noResultsEl) {
      this.noResultsEl.hidden = visibleCount !== 0;
    }

    this._announce(visibleCount);
    return visibleCount;
  }

  /** Aktualisiert die aria-live Statusmeldung, damit Screenreader Änderungen mitbekommen */
  _announce(count) {
    if (!this.statusEl) return;
    const total = this.items.length;
    this.statusEl.textContent =
      count === total
        ? `${count} von ${total} Rezepten werden angezeigt`
        : `${count} von ${total} Rezepten werden angezeigt (gefiltert)`;
  }
}
