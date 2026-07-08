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
   * @param {string} [options.formSelector] - Selector des Filter-<form>. Wird dieser angegeben,
   *   registriert die Klasse selbstständig alle Event-Listener (change, reset, popstate) und
   *   übernimmt den Filterzustand aus der URL beim Start.
   * @param {string} [options.resetButtonSelector] - Selector des Zurücksetzen-Buttons
   * @param {string} [options.categoryInputName='category'] - name-Attribut der Kategorie-Checkboxen
   * @param {string} [options.ingredientInputName='ingredient'] - name-Attribut der Zutaten-Checkboxen
   * @param {string} [options.urlCategoryParam='kategorie'] - Query-Parameter-Name für Kategorien
   * @param {string} [options.urlIngredientParam='zutat'] - Query-Parameter-Name für Zutaten
   * @param {string} [options.searchInputSelector='.filter-search'] - Selector der Such-Inputs je Fieldset
   * @param {string} [options.sheetSelector] - Selector des <dialog>-Elements (mobiles Bottom-Sheet)
   * @param {string} [options.openButtonSelector] - Selector des Buttons, der das Bottom-Sheet öffnet
   * @param {string} [options.closeButtonSelector] - Selector des Buttons, der das Bottom-Sheet schließt
   * @param {string} [options.chipsSelector] - Selector des Containers für aktive Filter-Chips
   */
  constructor({
                listSelector,
                itemSelector,
                categoryAttr = "categories",
                ingredientAttr = "ingredients",
                statusSelector = null,
                noResultsSelector = null,
                formSelector = null,
                resetButtonSelector = null,
                categoryInputName = "category",
                ingredientInputName = "ingredient",
                urlCategoryParam = "category",
                urlIngredientParam = "ingredient",
                statusText = "{count} of {total} recipes are shown",
                statusFiltered = "filtered",
                searchInputSelector = ".filter-search",
                sheetSelector = null,
                openButtonSelector = null,
                closeButtonSelector = null,
                chipsSelector = null,
              }) {
    this.listEl = document.querySelector(listSelector);
    if (!this.listEl) {
      throw new Error(`RecipeFilter: Container "${listSelector}" not found.`);
    }

    this.itemSelector = itemSelector;
    this.categoryAttr = categoryAttr;
    this.ingredientAttr = ingredientAttr;
    this.statusEl = statusSelector ? document.querySelector(statusSelector) : null;
    this.noResultsEl = noResultsSelector ? document.querySelector(noResultsSelector) : null;

    this.activeCategories = new Set();
    this.activeIngredients = new Set();

    this.items = Array.from(this.listEl.querySelectorAll(this.itemSelector));

    // --- Optionale Formular-Verkabelung inkl. URL-Sync ---
    this.formEl = formSelector ? document.querySelector(formSelector) : null;
    this.resetButtonEl = resetButtonSelector ? document.querySelector(resetButtonSelector) : null;
    this.categoryInputName = categoryInputName;
    this.ingredientInputName = ingredientInputName;
    this.urlCategoryParam = urlCategoryParam;
    this.urlIngredientParam = urlIngredientParam;
    this.statusText = statusText;
    this.statusFiltered = statusFiltered;

    if (this.formEl) {
      // Gebundene Referenzen merken, damit removeEventListener in destroy()
      // funktioniert (Arrow-Function-Literale in addEventListener ließen sich
      // sonst nicht wieder entfernen).
      this._onFormChange = this._handleFormChange.bind(this);
      this._onResetClick = this._handleResetClick.bind(this);
      this._onPopState = this._handlePopState.bind(this);

      this.formEl.addEventListener("change", this._onFormChange);
      if (this.resetButtonEl) {
        this.resetButtonEl.addEventListener("click", this._onResetClick);
      }
      window.addEventListener("popstate", this._onPopState);

      // --- Mobiles Bottom-Sheet (natives <dialog>, Öffnen/Schließen via showModal()/close()) ---
      this.sheetEl = sheetSelector ? document.querySelector(sheetSelector) : null;
      this.openButtonEl = openButtonSelector ? document.querySelector(openButtonSelector) : null;
      this.closeButtonEl = closeButtonSelector ? document.querySelector(closeButtonSelector) : null;
      if (this.sheetEl && this.openButtonEl) {
        this._onSheetOpen = () => this.sheetEl.showModal();
        this.openButtonEl.addEventListener("click", this._onSheetOpen);
      }
      if (this.sheetEl && this.closeButtonEl) {
        this._onSheetClose = () => this.sheetEl.close();
        this.closeButtonEl.addEventListener("click", this._onSheetClose);
      }

      // --- Aktive Filter als Chips (mobil sichtbar, auch wenn das Sheet zu ist) ---
      this.chipsEl = chipsSelector ? document.querySelector(chipsSelector) : null;
      if (this.chipsEl) {
        this._onChipClick = this._handleChipClick.bind(this);
        this.chipsEl.addEventListener("click", this._onChipClick);
      }

      // --- Suchfelder je Fieldset, blenden nicht passende checkbox-row aus ---
      this._searchInputs = Array.from(this.formEl.querySelectorAll(searchInputSelector));
      this._onSearchInput = (event) => this._filterRows(event.target);
      this._searchInputs.forEach((input) => input.addEventListener("input", this._onSearchInput));

      // Initialer Zustand: Filter aus der URL übernehmen (z. B. bei geteiltem
      // Link oder Lesezeichen), Checkboxen entsprechend vorbelegen.
      this._applyUrlToCheckboxes();
      this.setFilters({
        categories: this._readCheckedValues(this.categoryInputName),
        ingredients: this._readCheckedValues(this.ingredientInputName),
      });
    } else {
      this.apply();
    }
  }

  /** Entfernt alle registrierten Event-Listener (z. B. bevor die Komponente
   *  aus dem DOM entfernt wird, etwa in einer SPA). Nach destroy() ist die
   *  Instanz nicht mehr funktionsfähig. */
  destroy() {
    if (!this.formEl) return;
    this.formEl.removeEventListener("change", this._onFormChange);
    if (this.resetButtonEl) {
      this.resetButtonEl.removeEventListener("click", this._onResetClick);
    }
    window.removeEventListener("popstate", this._onPopState);
    if (this.openButtonEl) {
      this.openButtonEl.removeEventListener("click", this._onSheetOpen);
    }
    if (this.closeButtonEl) {
      this.closeButtonEl.removeEventListener("click", this._onSheetClose);
    }
    if (this.chipsEl) {
      this.chipsEl.removeEventListener("click", this._onChipClick);
    }
    this._searchInputs.forEach((input) => input.removeEventListener("input", this._onSearchInput));
  }

  /** Blendet innerhalb des Fieldsets eines Such-Inputs alle checkbox-row aus,
   *  deren Label nicht zum Suchbegriff passt */
  _filterRows(searchInput) {
    const query = searchInput.value.trim().toLowerCase();
    searchInput.closest("fieldset").querySelectorAll(".checkbox-row").forEach((row) => {
      const label = row.querySelector("label").textContent.toLowerCase();
      row.hidden = query !== "" && !label.includes(query);
    });
  }

  /** Handler: Klick auf einen Filter-Chip entfernt genau diesen Filter */
  _handleChipClick(event) {
    const chip = event.target.closest(".filter-chip");
    if (!chip) return;
    const input = this.formEl.querySelector(
      `input[name="${chip.dataset.filterName}"][value="${CSS.escape(chip.dataset.filterValue)}"]`
    );
    if (input) input.checked = false;
    this._handleFormChange();
  }

  /** Rendert die aktiven Kategorie-/Zutatenfilter als Chips (v. a. für mobil,
   *  damit aktive Filter sichtbar bleiben, während das Bottom-Sheet zu ist) */
  _renderChips() {
    if (!this.chipsEl) return;

    const chips = [
      ...[...this.activeCategories].map((value) => ({ name: this.categoryInputName, value })),
      ...[...this.activeIngredients].map((value) => ({ name: this.ingredientInputName, value })),
    ];

    this.chipsEl.innerHTML = "";
    chips.forEach(({ name, value }) => {
      const input = this.formEl.querySelector(`input[name="${name}"][value="${CSS.escape(value)}"]`);
      const label = input ? this.formEl.querySelector(`label[for="${input.id}"]`).textContent : value;

      const chip = document.createElement("button");
      chip.type = "button";
      chip.className = "filter-chip";
      chip.dataset.filterName = name;
      chip.dataset.filterValue = value;
      chip.setAttribute("aria-label", `${label} entfernen`);
      chip.textContent = label;
      this.chipsEl.appendChild(chip);
    });

    this.chipsEl.hidden = chips.length === 0;
  }

  /** Liest die aktuell angehakten Checkbox-Werte für ein gegebenes name-Attribut */
  _readCheckedValues(inputName) {
    return Array.from(
      this.formEl.querySelectorAll(`input[name="${inputName}"]:checked`)
    ).map((input) => input.value);
  }

  /** Setzt Checkboxen anhand einer Werteliste (z. B. aus der URL) */
  _setCheckedValues(inputName, values) {
    const valueSet = new Set(values);
    this.formEl.querySelectorAll(`input[name="${inputName}"]`).forEach((cb) => {
      cb.checked = valueSet.has(cb.value);
    });
  }

  /** Schreibt den aktuellen Filterzustand in die URL, ohne Reload und ohne
   *  Fokusverschiebung. replaceState statt pushState, damit nicht jede
   *  einzelne Checkbox einen eigenen Browser-History-Eintrag erzeugt. */
  _syncUrlFromFilters(categories, ingredients) {
    const params = new URLSearchParams(window.location.search);

    categories.length
      ? params.set(this.urlCategoryParam, categories.join(","))
      : params.delete(this.urlCategoryParam);
    ingredients.length
      ? params.set(this.urlIngredientParam, ingredients.join(","))
      : params.delete(this.urlIngredientParam);

    const queryString = params.toString();
    const newUrl = `${window.location.pathname}${queryString ? "?" + queryString : ""}${window.location.hash}`;
    history.replaceState(null, "", newUrl);
  }

  /** Liest kommagetrennte Werte für einen Query-Parameter aus der aktuellen URL */
  _readUrlParamValues(paramName) {
    const params = new URLSearchParams(window.location.search);
    const raw = params.get(paramName);
    return raw ? raw.split(",").map((v) => v.trim()).filter(Boolean) : [];
  }

  /** Übernimmt den Filterzustand aus der URL in die Checkboxen (ohne zu filtern) */
  _applyUrlToCheckboxes() {
    this._setCheckedValues(this.categoryInputName, this._readUrlParamValues(this.urlCategoryParam));
    this._setCheckedValues(this.ingredientInputName, this._readUrlParamValues(this.urlIngredientParam));
  }

  /** Handler: Checkbox-Zustand geändert (Maus, Touch oder Tastatur/Leertaste) */
  _handleFormChange() {
    const categories = this._readCheckedValues(this.categoryInputName);
    const ingredients = this._readCheckedValues(this.ingredientInputName);
    this.setFilters({ categories, ingredients });
    this._syncUrlFromFilters(categories, ingredients);
  }

  /** Handler: Zurücksetzen-Button geklickt. Fokus bleibt bewusst auf dem
   *  Button, damit Tastatur-Nutzende nicht die Orientierung verlieren. */
  _handleResetClick() {
    this.formEl.querySelectorAll('input[type="checkbox"]').forEach((cb) => (cb.checked = false));
    this.reset();
    this._syncUrlFromFilters([], []);
  }

  /** Handler: Browser-Vor-/Zurück-Navigation (z. B. geteilter Link oder
   *  pushState an anderer Stelle der App) */
  _handlePopState() {
    this._applyUrlToCheckboxes();
    this.setFilters({
      categories: this._readCheckedValues(this.categoryInputName),
      ingredients: this._readCheckedValues(this.ingredientInputName),
    });
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
    this._renderChips();
    this.apply();
  }

  /** Setzt alle Filter zurück */
  reset() {
    this.activeCategories.clear();
    this.activeIngredients.clear();
    this._renderChips();
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
        ? this.statusText.replaceAll('{count}', count.toString()).replaceAll('{total}', total.toString())
        : this.statusText.replaceAll('{count}', count.toString()).replaceAll('{total}', total.toString()) + ' (' + this.statusFiltered + ')';
  }
}
