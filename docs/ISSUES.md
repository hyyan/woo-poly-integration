# Issue tracker

Updated: 2026-10-06. Status is evidence based; triaged does not mean fixed.

| ID | Finding | Status | Acceptance |
| --- | --- | --- | --- |
| SEC-001 | Closure notice CVE-2026-101926: raw variation ID SQL concatenation | Fixed in local commit 01bfb80; Chrome and 11 integration checks pass | No SQL delay/extraction; legitimate linked lookup preserved |
| SEC-002 | Variation removal missing nonce/capability checks | Fixed in local commit 01bfb80; Chrome and 11 integration checks pass | Subscriber/customer denied; authorized multilingual deletion passes |
| SEC-003 | Featured-product AJAX mutates translations before WooCommerce checks | Fixed and verified; 10 regression cases and Chrome role, nonce, normal toggle and disabled setting acceptance pass; local commit 0ec30b1 | Unauthorized/invalid nonce denied; valid toggle syncs translations |
| ENV-001 | Latest WordPress/PHP/WooCommerce/Polylang baseline | In progress | Exact versions, healthy store/admin, Chrome proof |
| CHECK-001 | Full Plugin Check/coding standards/security audit | Pending | 368 baseline findings individually inventoried in docs/CHECKS.md; disposition and current report pending |
| [GH-571](https://github.com/hyyan/woo-poly-integration/issues/571) | After updating the Hyyan WooCommerce Polylang Integration doesn't work on pages with options, Fatal error: Uncaught TypeError: | Fixed in local commit f49cb58; PHP 8.5 unit checks and bilingual Chrome cart workflow pass | Empty dimensions do not crash; numbers and admin values preserved |
| [GH-569](https://github.com/hyyan/woo-poly-integration/issues/569) | The plan for a WooCommerce custom order table | Historical HPOS notice, current storage compatibility audit required | Read discussion; reproduce; classify bug/configuration/feature request; regression evidence |
| [GH-566](https://github.com/hyyan/woo-poly-integration/issues/566) | broken language switcher links on search page | Fixed in local commit b035464: search links preserve query and switch languages; normal shop links preserve sorting; 3 regressions and Chrome acceptance pass | Read discussion; reproduce; classify bug/configuration/feature request; regression evidence |
| [GH-565](https://github.com/hyyan/woo-poly-integration/issues/565) | Cannot translate cart checkout myaccount | Reported page or theme string translation problem, page links observed, full reproduction pending | Read discussion; reproduce; classify bug/configuration/feature request; regression evidence |
| [GH-564](https://github.com/hyyan/woo-poly-integration/issues/564) | This Plugin and Elementor | Third party compatibility report, requires Elementor Pro reproduction, not yet tested | Read discussion; reproduce; classify bug/configuration/feature request; regression evidence |
| [GH-561](https://github.com/hyyan/woo-poly-integration/issues/561) | Price synchronization | Reported price regression with incomplete isolated reproduction, Chrome synchronization check pending | Read discussion; reproduce; classify bug/configuration/feature request; regression evidence |
| [GH-559](https://github.com/hyyan/woo-poly-integration/issues/559) | No redirect to Thank you page after checkout for some languages | Reported checkout redirect regression in Ukrainian and Russian, checkout matrix pending | Read discussion; reproduce; classify bug/configuration/feature request; regression evidence |
| [GH-558](https://github.com/hyyan/woo-poly-integration/issues/558) | Custom Attribute after translate changed in all languages version. | Reported disabled custom attribute synchronization regression, settings reproduction pending | Read discussion; reproduce; classify bug/configuration/feature request; regression evidence |
| [GH-557](https://github.com/hyyan/woo-poly-integration/issues/557) | WC 5.4 has added a product attributes lookup table | Historical attributes lookup notice, current filtering compatibility audit required | Read discussion; reproduce; classify bug/configuration/feature request; regression evidence |
| [GH-556](https://github.com/hyyan/woo-poly-integration/issues/556) | Emails without order not translated correctly | Fixed and verified: 7 integration cases and bilingual Chrome reset mail capture pass; native placeholders and order path preserved | Read discussion; reproduce; classify bug/configuration/feature request; regression evidence |
| [GH-552](https://github.com/hyyan/woo-poly-integration/issues/552) | 404 error with Paypal express button api and language slugs | Reported YITH PayPal URL integration problem, isolated integration reproduction pending | Read discussion; reproduce; classify bug/configuration/feature request; regression evidence |
| [GH-543](https://github.com/hyyan/woo-poly-integration/issues/543) | Category entries | Reported category hierarchy regression, category fixture reproduction pending | Read discussion; reproduce; classify bug/configuration/feature request; regression evidence |
| [GH-537](https://github.com/hyyan/woo-poly-integration/issues/537) | Cart handling requirements / ideas for discussion | Behavior design request, preserve existing cart translation contract, compatibility tests pending | Read discussion; reproduce; classify bug/configuration/feature request; regression evidence |
| [GH-514](https://github.com/hyyan/woo-poly-integration/issues/514) | REST API support for limiting returned language versions | REST language filter feature request, existing behavior preservation audit pending | Read discussion; reproduce; classify bug/configuration/feature request; regression evidence |
| [GH-464](https://github.com/hyyan/woo-poly-integration/issues/464) | Lots of database queries | Reported gateway query overhead, singleton already used, benchmark reproduction pending | Read discussion; reproduce; classify bug/configuration/feature request; regression evidence |
| [GH-449](https://github.com/hyyan/woo-poly-integration/issues/449) | WooCommerce status report additions | Status report feature request, no demonstrated regression | Read discussion; reproduce; classify bug/configuration/feature request; regression evidence |
| [GH-410](https://github.com/hyyan/woo-poly-integration/issues/410) | Looking for maintainers! | Administrative maintainer discussion, no code defect | Read discussion; reproduce; classify bug/configuration/feature request; regression evidence |
| [GH-348](https://github.com/hyyan/woo-poly-integration/issues/348) | variations should have language attributes | Reported variation language query problem, newer source assigns target language, baseline reproduction pending | Read discussion; reproduce; classify bug/configuration/feature request; regression evidence |
| [GH-208](https://github.com/hyyan/woo-poly-integration/issues/208) | WooCommerce 3.1 CSV Import/Export | Import and export support request, import hook exists, current synchronization verification pending | Read discussion; reproduce; classify bug/configuration/feature request; regression evidence |
| [GH-120](https://github.com/hyyan/woo-poly-integration/issues/120) | Show Product Ratings and Reviews in all languages. | Combined ratings feature request, changes original review semantics, no defect reproduction | Read discussion; reproduce; classify bug/configuration/feature request; regression evidence |

External sources to inventory: all WordPress.org support pages, Wordfence, Patchstack and CVE references; closed GitHub security/compatibility history. Source failures are recorded rather than treated as no issues.

Feature contract: README.md/readme.txt and wiki; existing variable-product default-language and content-based URL limitations remain explicit. New feature requests are assessed separately from regressions.

| SEC-004 | [Public CVE-2026-24585](https://www.wordfence.com/threat-intel/vulnerabilities/wordpress-plugins/woo-poly-integration/hyyan-woocommerce-polylang-integration-150-missing-authorization) | Primary advisories checked again: no operation named; contributor variation/featured/settings denial verified; exact mapping unresolved | Identify exact operation; reproduce contributor denial and legitimate workflow |

| COMPAT-001 | Product and category shortcodes use reversed implode arguments | Fixed in local commit a88c467 on PHP 8.5: both language shortcode rendering and 5 regression cases pass | Original ID filtering works in both languages without changing shortcode contract |

| COMPAT-002 | Shipping collector creates unsaved zone instead of rest of world zone 0 | Fixed in local commit 93f6226: 2 regressions and Chrome translated shipping labels/rates pass | No null key warning; rest of world and configured methods retain instance keys |

| COMPAT-003 | Currency formatter crashes when Polylang has no current locale during checkout email rendering | Fixed in local commit 7077cf8: WordPress locale fallback, regression and Chrome checkout 200/order received pass | Normal language formatting preserved; emails render without ValueError |

| COMPAT-004 | Block checkout order language missing with HPOS and German thank you redirect goes to English | Fixed for normal cookie enabled checkout: 5 order regressions and 5 Chrome purchases pass across block/classic and HPOS/legacy paths; broader client/cookie configuration acceptance pending | Classic and block orders retain language, localized redirect and email behavior in both storage modes |

| FORUM-020 | Unknown locale assignment on false current Polylang language | Fixed and tested: 4 integration cases plus Chrome exact callback before/after proof | Missing language does not crash, valid language and existing object fallback preserved |

Individual forum dispositions are in docs/evidence/wordpress-support-capture.json. FORUM-001 through FORUM-020 were read with captured replies. The remaining 403 reports need triage. Mapping a report to a tested fix does not close broader feature acceptance.

| COMPAT-005 / FORUM-022 | Cart fragments declare jquery-cookie while consuming Cookies | Fixed and verified: native graph regression, Chrome isolated before/after and bilingual mini cart flow | Existing cookie API is loaded before fragments, quantities and translated contents preserved |
| COMPAT-006 | English product page shows German related product | Resolved as malformed lab data: German simple product had English category. Native category correction and bilingual Chrome checks pass. No production change | Correct fixture categories, preserve existing product behavior |

Forum topics FORUM-021 through FORUM-040 have individual dispositions in the forum inventory. Forty topics are triaged and 383 remain pending.

| SEC-005 | Security maintenance review and expanded regression coverage | Reported removal paths and native client inspected, 72 tests and 189 assertions pass, current Chrome rejection and native deletion pass | Full checker dispositions and actual release artifact review remain pending, see SECURITY-REVIEW.md |

| SEC-006 | Stored category metadata and image values can break out of inline JavaScript | Fixed, 3 regressions and native Chrome marker and normal thumbnail copying pass | Category display type and thumbnail ID, image and Remove button retained |

| SEC-007 | Translation availability and download disable certificate verification | Fixed and verified, 3 regressions and Chrome real availability and download pass | Both original requests verify certificates, French pack installed successfully |
