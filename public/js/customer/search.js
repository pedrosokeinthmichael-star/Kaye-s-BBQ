/**
 * search.js — Kaye's Barbeque
 * -----------------------------------------------------------------
 * Client-side search for the Menu, Order (tracking) and Order History
 * pages. Every page already has its full list in the DOM, so no
 * server call is needed: typing just hides the cards that don't match.
 *
 * Wire a search box up with data attributes on the <input>:
 *
 *   data-search-input            marks the box (required)
 *   data-search-scope="#id"      container that holds the items (required)
 *   data-search-item=".sel"      selector for each searchable card (required)
 *   data-search-group=".sel"     optional: wrapper to hide when none of its
 *                                items match (menu categories)
 *   data-search-fields=".a, .b"  optional: child selectors whose text is
 *                                searched. Without it, a menu card's
 *                                data-name + data-desc are used, then the
 *                                card's full text as a last resort.
 *   data-search-empty="#id"      optional: "no matches" element to reveal
 *   data-search-filters="#id"    optional: chip filter bar on the same list,
 *                                so the empty message stays correct when
 *                                the chip changes (customer.js)
 *
 * Search hides cards with the .search-hidden class, not inline display,
 * so it combines with the chip filters in customer.js instead of
 * fighting them. Multiple words must all match (any order), and a
 * leading "#" is ignored, so "#KB-1042", "kb-1042" and "1042" all work.
 */
(function () {
  function normalize(str) {
    return String(str == null ? '' : str).toLowerCase().replace(/#/g, '').replace(/\s+/g, ' ').trim();
  }

  function init(input) {
    var scope = document.querySelector(input.getAttribute('data-search-scope') || '');
    var itemSel = input.getAttribute('data-search-item');
    if (!scope || !itemSel) return;

    var groupSel = input.getAttribute('data-search-group');
    var fieldsSel = input.getAttribute('data-search-fields');
    var emptySel = input.getAttribute('data-search-empty');
    var filtersSel = input.getAttribute('data-search-filters');
    var emptyEl = emptySel ? document.querySelector(emptySel) : null;
    var filterBar = filtersSel ? document.querySelector(filtersSel) : null;
    var clearBtn = input.parentNode.querySelector('.search-clear');

    function textOf(el) {
      if (fieldsSel) {
        return normalize(Array.prototype.map.call(el.querySelectorAll(fieldsSel), function (n) {
          return n.textContent;
        }).join(' '));
      }
      var name = el.getAttribute('data-name');
      if (name != null) return normalize(name + ' ' + (el.getAttribute('data-desc') || ''));
      return normalize(el.textContent);
    }

    function apply() {
      var query = normalize(input.value);
      var terms = query ? query.split(' ') : [];
      var shown = 0;
      var eligible = 0; // cards not already hidden by a chip filter

      Array.prototype.forEach.call(scope.querySelectorAll(itemSel), function (el) {
        var text = terms.length ? textOf(el) : '';
        var match = terms.every(function (t) { return text.indexOf(t) !== -1; });
        el.classList.toggle('search-hidden', !match);
        if (el.style.display !== 'none') {
          eligible++;
          if (match) shown++;
        }
      });

      if (groupSel) {
        Array.prototype.forEach.call(scope.querySelectorAll(groupSel), function (g) {
          var hasMatch = !!g.querySelector(itemSel + ':not(.search-hidden)');
          g.classList.toggle('search-hidden', terms.length > 0 && !hasMatch);
        });
      }

      // Only say "no matches" when the search itself emptied the list, not
      // when the list was already empty (that has its own message).
      if (emptyEl) emptyEl.hidden = !(terms.length > 0 && eligible > 0 && shown === 0);
      if (clearBtn) clearBtn.hidden = input.value === '';
    }

    input.addEventListener('input', apply);
    input.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && input.value) {
        e.preventDefault();
        input.value = '';
        apply();
      }
    });
    if (clearBtn) {
      clearBtn.addEventListener('click', function () {
        input.value = '';
        apply();
        input.focus();
      });
    }

    // Re-check after a chip filter changes (customer.js handles the click first).
    if (filterBar) filterBar.addEventListener('click', function () { setTimeout(apply, 0); });

    // Re-check when cards are removed (e.g. an order gets cancelled).
    if (window.MutationObserver) {
      new MutationObserver(apply).observe(scope, { childList: true, subtree: true });
    }

    apply();
  }

  function boot() {
    Array.prototype.forEach.call(document.querySelectorAll('[data-search-input]'), init);
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
  else boot();
})();