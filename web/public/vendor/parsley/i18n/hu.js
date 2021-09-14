// ParsleyConfig definition if not already set
window.ParsleyConfig = window.ParsleyConfig || {};
window.ParsleyConfig.i18n = window.ParsleyConfig.i18n || {};

// Define then the messages
window.ParsleyConfig.i18n.en = $.extend(window.ParsleyConfig.i18n.en || {}, {
  defaultMessage: "A megadott érték hibásnak tűnik.",
  type: {
    email:        "A megadott értéknek érvényes email címet kell tartalmaznia.",
    url:          "A megadott értéknek érvényes url-t kell tartalmaznia.",
    number:       "A megadott értéknek számot kell tartalmaznia.",
    integer:      "A megadott értéknek egész számot kell tartalmaznia.",
    digits:       "A megadott érték csak számjegyeket tartalmazhat.",
    alphanum:     "A megadott érték csak alfanumerikus karaktereket tartalmazhat."
  },
  notblank:       "Ez az érték nem lehet üres.",
  required:       "Ennek az értéknek a megadása kötelező.",
  pattern:        "A megadott érték hibásnak tűnik.",
  min:            "A megadott értéknek nagyobb vagy egyenlőnek kell lennie, mint %s.",
  max:            "A megadott értéknek kisebb vagy egyenlőnek kell lennie, mint %s.",
  range:          "A megadott értéknek %s és %s közé kell esnie.",
  minlength:      "A megadott érték túl rövid. Legalább %s vagy több karaktert kell tartalmaznia.",
  maxlength:      "A megadott érték túl hosszú. Nem lehet hosszabb %s karakternél.",
  length:         "A megadott érték hossza hibás. %s és %s karakter közé kell esnie.",
  mincheck:       "Legalább %s elem kiválasztása kötelező.",
  maxcheck:       "%s elem, vagy kevesebb választható.",
  check:          "A kiválasztott elemek számának %s és %s közé kell esnie.",
  equalto:        "A megadott értéknek meg kell egyeznie."
});

// If file is loaded after Parsley main file, auto-load locale
if ('undefined' !== typeof window.ParsleyValidator)
  window.ParsleyValidator.addCatalog('en', window.ParsleyConfig.i18n.en, true);
