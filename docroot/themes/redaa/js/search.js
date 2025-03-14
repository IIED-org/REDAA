/**
* DO NOT EDIT THIS FILE.
* See the following change record for more information,
* https://www.drupal.org/node/2815083
* @preserve
**/
(function (Drupal) {
  var searchWideButtonSelector = '[data-drupal-selector="block-search-wide-button"]';
  var searchWideButton = document.querySelector(searchWideButtonSelector);
  var searchWideWrapperSelector = '[data-drupal-selector="block-search-wide-wrapper"]';
  var searchWideWrapper = document.querySelector(searchWideWrapperSelector);
  function searchIsVisible() {
    return searchWideWrapper.classList.contains('is-active');
  }
  Drupal.redaa.searchIsVisible = searchIsVisible;
  function watchForClickOut(e) {
    var clickInSearchArea = e.target.matches("\n      ".concat(searchWideWrapperSelector, ",\n      ").concat(searchWideWrapperSelector, " *,\n      ").concat(searchWideButtonSelector, ",\n      ").concat(searchWideButtonSelector, " *\n    "));
    if (!clickInSearchArea && searchIsVisible()) {
      toggleSearchVisibility(false);
    }
  }
  function watchForFocusOut(e) {
    if (e.relatedTarget) {
      var inSearchBar = e.relatedTarget.matches("".concat(searchWideWrapperSelector, ", ").concat(searchWideWrapperSelector, " *"));
      var inSearchButton = e.relatedTarget.matches("".concat(searchWideButtonSelector, ", ").concat(searchWideButtonSelector, " *"));
      if (!inSearchBar && !inSearchButton) {
        toggleSearchVisibility(false);
      }
    }
  }
  function watchForEscapeOut(e) {
    if (e.key === 'Escape' || e.key === 'Esc') {
      toggleSearchVisibility(false);
    }
  }
  function handleFocus() {
    if (searchIsVisible()) {
      searchWideWrapper.querySelector('input[type="search"]').focus();
    } else if (searchWideWrapper.contains(document.activeElement)) {
      searchWideButton.focus();
    }
  }
  function toggleSearchVisibility(visibility) {
    searchWideButton.setAttribute('aria-expanded', visibility === true);
    searchWideWrapper.addEventListener('transitionend', handleFocus, {
      once: true
    });
    if (visibility === true) {
      Drupal.redaa.closeAllSubNav();
      searchWideWrapper.classList.add('is-active');
      document.addEventListener('click', watchForClickOut, {
        capture: true
      });
      document.addEventListener('focusout', watchForFocusOut, {
        capture: true
      });
      document.addEventListener('keyup', watchForEscapeOut, {
        capture: true
      });
    } else {
      searchWideWrapper.classList.remove('is-active');
      document.removeEventListener('click', watchForClickOut, {
        capture: true
      });
      document.removeEventListener('focusout', watchForFocusOut, {
        capture: true
      });
      document.removeEventListener('keyup', watchForEscapeOut, {
        capture: true
      });
    }
  }
  Drupal.redaa.toggleSearchVisibility = toggleSearchVisibility;
  Drupal.behaviors.searchWide = {
    attach: function attach(context) {
      var searchWideButtonEl = once('search-wide', searchWideButtonSelector, context).shift();
      if (searchWideButtonEl) {
        searchWideButtonEl.setAttribute('aria-expanded', searchIsVisible());
        searchWideButtonEl.addEventListener('click', function () {
          toggleSearchVisibility(!searchIsVisible());
        });
      }
    }
  };
})(Drupal);