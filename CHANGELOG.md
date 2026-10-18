# Changelog

## 1.6.0

* fix: initialize CI fixtures and guard deferred product saves ([fcecb7a](https://github.com/hyyan/woo-poly-integration/commit/fcecb7a5dc9c9c039b61bb9218677a0afb427194))
* fix: secure variation removal and synchronize parent prices ([e809f03](https://github.com/hyyan/woo-poly-integration/commit/e809f03b762067fd778590883cd00ce7836cf699))
* fix: complete secure plugin maintenance ([65cc44d](https://github.com/hyyan/woo-poly-integration/commit/65cc44df124ee5c7f7d864e915f335b2dd33152e))
* fix: preserve variation relationships during stock updates ([40116a6](https://github.com/hyyan/woo-poly-integration/commit/40116a690bf311cd20f9f8f80a0c5be777db0bf3))
* fix: reject integer overflow before variation operations ([c2487d4](https://github.com/hyyan/woo-poly-integration/commit/c2487d4e3d13260bfb72a72c02fc7ea1541a2de7))
* fix: restrict variation stock and coupons to variations ([bad6b9c](https://github.com/hyyan/woo-poly-integration/commit/bad6b9c8d371face7af68fa9ddc95d0d11843bc7))
* fix: verify custom report requests before translation ([df79acb](https://github.com/hyyan/woo-poly-integration/commit/df79acbf6d2d6482bae4d3a5bfc403d90f2c9bd8))
* fix: centralize native report language selection ([5fcd63c](https://github.com/hyyan/woo-poly-integration/commit/5fcd63c2a101a5043681b92189ae6789e4055d95))
* fix: centralize attribute language request fallback ([9611ab1](https://github.com/hyyan/woo-poly-integration/commit/9611ab1e693f71a9d04c6ba5ef892ccd9badf738))
* fix: verify translation language and metadata setup ([78d6218](https://github.com/hyyan/woo-poly-integration/commit/78d6218840eff46f6383031b1a7dbf2b5be29955))
* fix: verify product editor translation requests ([bed0aee](https://github.com/hyyan/woo-poly-integration/commit/bed0aee39802159be53456f46c4bb10346d936cd))
* fix: preserve catalogs with native version aware loading ([7f8d2a8](https://github.com/hyyan/woo-poly-integration/commit/7f8d2a8d1c487d3fd86967db333911c4799b4e9a))
* fix: use native settings context for endpoint notices ([b4e1e63](https://github.com/hyyan/woo-poly-integration/commit/b4e1e636fcba2a8ee5a749ab0dd378c972f4483d))
* fix: own message domains and retain WooCommerce translations ([3567186](https://github.com/hyyan/woo-poly-integration/commit/35671868dc5904d84dcb6bd9c5d31ce44d8e305a))
* fix: number translation formats and preserve legacy catalogs ([9790467](https://github.com/hyyan/woo-poly-integration/commit/9790467ae606f94656b2f9afcdd2541428dce86d))
* fix: resolve coupon screens and verify translation warning bypass ([4ac655a](https://github.com/hyyan/woo-poly-integration/commit/4ac655a4fb1ac42bbb804ba5aec3fa83edea2e8a))
* fix: verify creation callbacks and resolve attribute screens ([24325be](https://github.com/hyyan/woo-poly-integration/commit/24325be9498d1e36d9fc935e4fb671449f8d8924))
* fix: expose verified product creation requests to security checks ([22ace96](https://github.com/hyyan/woo-poly-integration/commit/22ace96a19aa2ddc92894dc62c04d4aed70a93dd))
* fix: identify plugin hook constants with their owning prefix ([3175ae8](https://github.com/hyyan/woo-poly-integration/commit/3175ae8d45d131fb805671af78d48fc891f937bf))
* fix: translate stored checkout text through Polylang ([dacbd61](https://github.com/hyyan/woo-poly-integration/commit/dacbd6162d7d31f4e291cf9ca003f072b7cdc6a2))
* fix: preserve cart stock checks on WooCommerce 3.0 ([37a7fbe](https://github.com/hyyan/woo-poly-integration/commit/37a7fbebed4e6e69a70c13175c9e05d247ff092e))
* fix: invalidate cached product lookups on native changes ([dd816e0](https://github.com/hyyan/woo-poly-integration/commit/dd816e0eef1bfa7922dd147bd46988d82c1dd349))
* fix: normalize request IDs through validated sanitizers ([ac630ad](https://github.com/hyyan/woo-poly-integration/commit/ac630ad0351734d045498ec0e5683ae1d3838e87))
* fix: retain published WooCommerce minimum requirement ([60ce0d0](https://github.com/hyyan/woo-poly-integration/commit/60ce0d0a482a41116c8fc22a1eadc838e138a609))
* fix: use compliant plugin display name in release headers ([f659792](https://github.com/hyyan/woo-poly-integration/commit/f659792933c313cd019d6d3a83927f5589762d5d))
* fix: reuse exact variation relationships across lookup paths ([0e23d68](https://github.com/hyyan/woo-poly-integration/commit/0e23d680865e3a5328ba333b3382b14cb73f8eff))
* fix: match normalized variation relationships exactly ([02c2b76](https://github.com/hyyan/woo-poly-integration/commit/02c2b76d9c575e1a8bc66e61e6be2d8033e219e7))
* fix: verify early product translation creation requests ([344363e](https://github.com/hyyan/woo-poly-integration/commit/344363ec04bd699d3c06c1f67ddf3ccd54accf7e))
* fix: validate registered languages across request filters ([4e89b8e](https://github.com/hyyan/woo-poly-integration/commit/4e89b8eb616b2872e995921f66d6107b5d2a3026))
* fix: defer product type metadata backfill until save ([4564602](https://github.com/hyyan/woo-poly-integration/commit/4564602e2e975fe5ae70fa40bca767fbb8500869))
* fix: remove obsolete product type request callbacks ([166bab9](https://github.com/hyyan/woo-poly-integration/commit/166bab95b4e8b760392657ea6c781b76b67555ea))
* fix: prefix plugin callbacks and sharing variables ([df5c6fb](https://github.com/hyyan/woo-poly-integration/commit/df5c6fbcc0bb053e8d5b58610e0795e949846d67))
* fix: verify stock edit requests and use native diagnostics ([b7b33b0](https://github.com/hyyan/woo-poly-integration/commit/b7b33b0d52d6bc761da9862be5d00497dc764911))
* fix: verify category requests and normalize request identifiers ([cb91bae](https://github.com/hyyan/woo-poly-integration/commit/cb91bae8d10f4caef5c01f490ea6d7194aa7e541))
* fix: sanitize admin notices and translation error output ([4b4094f](https://github.com/hyyan/woo-poly-integration/commit/4b4094f1ec678816a71368ef4ad9e5bc196c939b))
* fix: preserve language filtered reports under HPOS ([df3ece1](https://github.com/hyyan/woo-poly-integration/commit/df3ece13b8d6b70dc8b3654653338ab116214f8c))
* fix: translate configured block and receipt payment titles ([0f31724](https://github.com/hyyan/woo-poly-integration/commit/0f317249dcde8c624331647f520b99a3c81c49c4))
* fix: translate configured native tax rate labels ([5c0a0ed](https://github.com/hyyan/woo-poly-integration/commit/5c0a0eda316f95ed4037c4746d83732991f20309))
* fix: preserve native translated store notice controls ([d28e504](https://github.com/hyyan/woo-poly-integration/commit/d28e504d5347a9134ae36ad864edaf11230df0b2))
* fix: preserve variation copy and import synchronization ([28bbc81](https://github.com/hyyan/woo-poly-integration/commit/28bbc81eca58c6935222e440163872d2916d7c03))
* fix: preserve independent product attributes during copies ([6dd38b2](https://github.com/hyyan/woo-poly-integration/commit/6dd38b2b7a0209bb9b2686f4834fd33a38f1bdbd))
* fix: restore stock synchronization callbacks after errors ([d4aa1ee](https://github.com/hyyan/woo-poly-integration/commit/d4aa1eee0af801cd8ccc636d3cfa5c9da692a1b2))
* fix: resolve validated translated variation deletion sources ([3a83aa6](https://github.com/hyyan/woo-poly-integration/commit/3a83aa6c64e1e3a3e092d4df07c597f131f40b2d))
* fix: replace only complete translated shop path segments ([8d6f920](https://github.com/hyyan/woo-poly-integration/commit/8d6f9209987db84985a64ce485155c0103a8655f))
* fix: retain product defaults and reload locale catalogs ([6b0d805](https://github.com/hyyan/woo-poly-integration/commit/6b0d8052223bfae83e8a25268f257e5271b473b9))
* fix: restore caller language after translated emails ([ae7bbea](https://github.com/hyyan/woo-poly-integration/commit/ae7bbea1b03a6310b1901ece2a0f7de51135b579))
* fix: preserve translated cart state and validation ([c66e470](https://github.com/hyyan/woo-poly-integration/commit/c66e470eb6d58ecd79217f6f8e58a04b1ab916a8))
* fix: preserve translated checkout receipt endpoints ([67271c0](https://github.com/hyyan/woo-poly-integration/commit/67271c0af26a5c3a2c47b1befc64580fa2de8368))
* fix: preserve translated account endpoints when switching languages ([b2c5117](https://github.com/hyyan/woo-poly-integration/commit/b2c51176a836284a54506ca04836d2d2fbda3972))
* fix: preserve native block store page translations ([791192b](https://github.com/hyyan/woo-poly-integration/commit/791192bbe2563bd2c2367ad4c707ee2acb6dd82e))
* fix: exclude development files from release packages ([9798ed6](https://github.com/hyyan/woo-poly-integration/commit/9798ed693eca6a15c7aa61de19411e6304a18f9c))
* fix: remove automatic third-party admin resources ([7ed435c](https://github.com/hyyan/woo-poly-integration/commit/7ed435c8499115002f13f6860d8bb351766e31ee))
* fix: preserve native URLs and restore variation timeouts ([2114f83](https://github.com/hyyan/woo-poly-integration/commit/2114f838419cb585902a2840faf4b0069d3251d9))
* fix: protect stock synchronization across request contexts ([d3cf009](https://github.com/hyyan/woo-poly-integration/commit/d3cf009c7980680a6ec74a6620d303d02e32dca3))
* fix: correct translation calls and preserve catalog contracts ([e0cf70d](https://github.com/hyyan/woo-poly-integration/commit/e0cf70df6641ffedb50b9f96820bb675f364283a))
* fix: preserve literal SKU checks and secure coupon diagnostics ([7df3bd5](https://github.com/hyyan/woo-poly-integration/commit/7df3bd5aa6e220500e3b186b467be9708531368a))
* fix: secure settings output and restrict page maintenance ([390162c](https://github.com/hyyan/woo-poly-integration/commit/390162c909f3188326f5caac723de0078f4884d0))
* fix: secure inline script data and preserve editor restrictions ([02851f2](https://github.com/hyyan/woo-poly-integration/commit/02851f2fca3d1429a9606a9f9bcb4662e8e0e4d6))
* fix: validate navigation filters and current URL inputs ([efd2096](https://github.com/hyyan/woo-poly-integration/commit/efd209614b2e645cff55e50e079370800e31b18b))
* fix: validate product synchronization request inputs ([a3a675f](https://github.com/hyyan/woo-poly-integration/commit/a3a675f27e0b1d24a81ea192130a20026819d956))
* fix: preserve Polylang post fields during product import ([12aecf9](https://github.com/hyyan/woo-poly-integration/commit/12aecf95d0420d397a6dc582780e4705a8ec5c5e))
* fix: validate language and translation request boundaries ([30f9688](https://github.com/hyyan/woo-poly-integration/commit/30f9688713e092286e260912d9f2deedf72f26f2))
* fix: escape translated template and gateway output ([f0f5c99](https://github.com/hyyan/woo-poly-integration/commit/f0f5c992c69192ac44a2b37b00760f7454696c49))
* fix: guard executable files outside WordPress ([9d093a2](https://github.com/hyyan/woo-poly-integration/commit/9d093a25578c2f780ff5895bc8a352f24afe4eb9))
* fix: escape status report locale and path output ([ecd05e6](https://github.com/hyyan/woo-poly-integration/commit/ecd05e681b479e8080a4305b5b9691bb6f671a2b))
* fix: verify translation download certificates ([26fbe5f](https://github.com/hyyan/woo-poly-integration/commit/26fbe5f49bf012246cc5394c2ce0488c9a9686d8))
* fix: encode category translation script values ([4b02c13](https://github.com/hyyan/woo-poly-integration/commit/4b02c135a3d5b494a980f7b9b433cadf7499bc5a))
* fix: load the cookie API required by cart fragments ([132e4a2](https://github.com/hyyan/woo-poly-integration/commit/132e4a20639ced1067e3cfc0868fa79fbbf905e9))
* fix: preserve absent Polylang language for unknown locales ([76e3f53](https://github.com/hyyan/woo-poly-integration/commit/76e3f53dd5ebed64fa955bfcf6d6be1bdeec80a8))
* fix: translate account email templates ([c1afb22](https://github.com/hyyan/woo-poly-integration/commit/c1afb22c7b5f92f599836f4ce1b9bedf1fa5c284))
* fix: preserve block checkout order language ([9b6780b](https://github.com/hyyan/woo-poly-integration/commit/9b6780b9622418f35a34c53c142663a819ec3e28))
* fix: use WordPress locale when checkout language is unavailable ([7077cf8](https://github.com/hyyan/woo-poly-integration/commit/7077cf8650aba18f274e9abf99921be6bbd0270c))
* fix: load rest of world shipping methods ([93f6226](https://github.com/hyyan/woo-poly-integration/commit/93f6226c850280e3c87fa604c0aa02d9ba5d8ea6))
* fix: restore translated shortcodes on PHP 8 ([a88c467](https://github.com/hyyan/woo-poly-integration/commit/a88c4675c59867f3eb846ff9afaae4f333517b7e))
* fix: preserve product search language links ([b035464](https://github.com/hyyan/woo-poly-integration/commit/b035464eb50048ce053c178754852fb1dbb11511))
* fix: authorize translated featured product toggles ([0ec30b1](https://github.com/hyyan/woo-poly-integration/commit/0ec30b1b511f1093936c67f0cfaf6669fa60069d))
* fix: preserve empty values during decimal localization ([f49cb58](https://github.com/hyyan/woo-poly-integration/commit/f49cb58575c1dc4defddd065e25726feca708997))
* fix: validate and authorize linked variation removal ([01bfb80](https://github.com/hyyan/woo-poly-integration/commit/01bfb80e73ccd962031729ff935f194082f780a9))
* fixes #568 ([b5f7495](https://github.com/hyyan/woo-poly-integration/commit/b5f7495180d37020204a6f725307eb6ecdc29cf0))
###  1.5.1
* Secure variation removal with complete integer validation, prepared database queries, capability checks and native AJAX nonce verification.
* Preserve translated cart quantities, prices, stock, checkout validation and configured payment text in classic and block flows.
* Restore caller language after email operations and retain current and older WooCommerce catalog loading.
* Preserve independent product metadata and synchronize native variation imports.
* Correct combined and language filtered reports for legacy and HPOS order storage and declare native HPOS compatibility.
* Preserve native store notice controls and translate configured tax labels.
* Verify native settings upgrades and separate network site settings.
* Tested with WordPress 7.1.3, WooCommerce 11.1.2 and PHP 8.5.11. Existing minimum requirements remain unchanged.
* [fixes #545 keep fields unlocked if products does not exist in default language props mrleemon](https://github.com/hyyan/woo-poly-integration/issues/545)
* [fixes #549 Quick edit Product synchronisation issues](https://github.com/hyyan/woo-poly-integration/issues/549)
* [fixes #548 incorrect save hook caused inconsistent synchronisation behaviour, especially changing product type and visibility](https://github.com/hyyan/woo-poly-integration/issues/548)
* [fixes #542 shop page breadcrumb in secondary language](https://github.com/hyyan/woo-poly-integration/issues/542) 


### 1.5.0

* correction to fix #527
* fixes #536 avoid over-setting product type on edit
* re- fixes #534 new variation not visible until save
* re- fixes #527 variation form default values
* fixes #535 variation stock issues

### 1.4.5

* [re-fixes #430, #524 variation duplications](https://github.com/hyyan/woo-poly-integration/issues/476)
* [fixes #526 product type selector issues - variable product appears as simple](https://github.com/hyyan/woo-poly-integration/issues/526)
* [fixes #522, #529 and other JQuery deprecation issues props @mrleemon](https://github.com/hyyan/woo-poly-integration/issues/522)
* [fixes #475 variation stock issues](https://github.com/hyyan/woo-poly-integration/issues/475)
* [fixes #527 default form value for variation lost when creating new translation](https://github.com/hyyan/woo-poly-integration/issues/527)
* [fixes #534 new variable translation, variations not visible until save](https://github.com/hyyan/woo-poly-integration/issues/534)

### 1.4.4

* [implements #476 adding Additional Content to translated email fields](https://github.com/hyyan/woo-poly-integration/issues/476)
* [Addresses multiple email issues causing some fields and default values not to be picked up or translated correctly.](https://github.com/hyyan/woo-poly-integration/wiki/Email-Languages)
* [fixes #502 Cart issues by removing addToCartHandlerVariable()](https://github.com/hyyan/woo-poly-integration/issues/502)
* [fixes #503 Deprecated: get_woocommerce_term_meta](https://github.com/hyyan/woo-poly-integration/issues/503)
* [fixes #466 undefined index in Cart.php on line 188 props nicmare sep 2019](https://github.com/hyyan/woo-poly-integration/issues/466)
* [addresses #449 by adding info to woo status report props jon007 sep 2019](https://github.com/hyyan/woo-poly-integration/issues/449)
* [fixes #446 by allow strings translation for customiser checkout text props jon007 jul 2019](https://github.com/hyyan/woo-poly-integration/issues/446)
* [implements #441 allow taxonomy slugs to be the same in different languages props jon007 jul 2019](https://github.com/hyyan/woo-poly-integration/issues/441)
* [fixes #442 incorrect defaults used where no saved settings exist  props jon007 jul 2019](https://github.com/hyyan/woo-poly-integration/issues/442)

### 1.4.3

* [fixes #434 page checks duplicating pages and other language switching issues](https://github.com/hyyan/woo-poly-integration/commit/8c54fb23afad1bec1f98b2077d6f552646f253b8)
* [fixes #435 fix get_current_screen errors when this plugin called in unanticipated circumstances props danieleftodi YITH Gift Cards](https://github.com/hyyan/woo-poly-integration/commit/7d428208ff3b3a2472ce42f5a2259604ce2128c8)
* [fixes #436 Illegal string offset and Invalid argument in FlashMessages](https://github.com/hyyan/woo-poly-integration/commit/feedc68431bcd26c8d9bd2b4afacf4d7a86a62a1)

### 1.4.2

* [fixes #432 default variation attributes were not synchronised correctly when using more than one attribute](https://github.com/hyyan/woo-poly-integration/commit/7101bcf0786e21515885d5796188420972b01831)
* [fixes #431 multiple email translation issues in different circumstances](https://github.com/hyyan/woo-poly-integration/issues/431)
* [fixes #430 bugfix and enhancement to allow variations to be disabled](https://github.com/hyyan/woo-poly-integration/issues/430)
* [stock synchronisation - avoid errors being logged where product is not translated](https://github.com/hyyan/woo-poly-integration/commit/08c9913105e6ad8183f7bbf35930f25b983d4c50)


### 1.4.1

* [fixes #427 by reversal of #390, #346 Cart: retain price when switching languages due to incompatibility with currency switchers, instead a filter woo-poly.Cart.switchedItem allows customisation of behaviour where necessary](https://github.com/hyyan/woo-poly-integration/issues/427)

### 1.4.0 preview

* [fixes #408 WooCommerce 3.6.x: by ensuring product lookup data is recalculated for translations and stale product data cache is cleared](https://github.com/hyyan/woo-poly-integration/issues/408)
* [fixes #408 WooCommerce 3.6.x: cart fragment script updates for 3.6](https://github.com/hyyan/woo-poly-integration/commit/89ef0451dd8c7775fe48e20d71dc7836e8e49a96)
* [fixes #411, #299, #391, #361 stock synchronisation issues](https://github.com/hyyan/woo-poly-integration/commit/17e29b9927eb2fea3afbc3947d48e2b68e3d2185)
* [added WooCommerce pages checker: missing WooCommerce pages and translations are added, warning added for pages in incorrect status, addresses common support and setup usability issues such as #379](https://github.com/hyyan/woo-poly-integration/issues/379)
* [fixes #390, #346 Cart: retain price when switching languages props mweimerskirch](https://github.com/hyyan/woo-poly-integration/commit/ec272c0d921e39ebfd497fb2303454d7793bf60d)
* [fixes #364 initial setup: apply initial settings automatically](https://github.com/hyyan/woo-poly-integration/commit/217e33d2843fb82775a92a9d04cd2f7f73e436f6)
* [fixes #234 product admin: click on featured star in products list is now synchronised to translations](https://github.com/hyyan/woo-poly-integration/commit/3bbc1bf30ec90402a8796b16c6681037bd9e5125)
* [fixes #400 shop page url translation: fixes where shop slug is contained in url](https://github.com/hyyan/woo-poly-integration/issues/400)
* [fixes #416 emails: some missing custom string translations added](https://github.com/hyyan/woo-poly-integration/commit/16ad4c51edc8a9610d9039bc2e4953c801892b5e)
* [fixes #414 emails: v1.3.0 bug for translating email strings which are customized in admin](https://github.com/hyyan/woo-poly-integration/issues/414)
* [fixes #409 shortcodes: allow product shortcode to translate in same way as products shortcode](https://github.com/hyyan/woo-poly-integration/commit/2bf61574cf5b9ebb24ccf232de0ba92af879b31b)
* [fixes #419 variations: allow default variation based on custom product specific attribute](https://github.com/hyyan/woo-poly-integration/commit/a71263855dfa35915993ee7f8da8bf3232c46d53)


### 1.3.0

* [fixes #352 by allowing translation of 'invisible' variations](https://github.com/hyyan/woo-poly-integration/commit/cd19fdd01cafcf065fbf21dfaa80dd167ce60e63)
* [fixes #353 by checking language of duplicate skis](https://github.com/hyyan/woo-poly-integration/commit/2a8f35955016485f07764e2fe7f6d33b6b1ceaae)
* [improves #353 fix](https://github.com/hyyan/woo-poly-integration/commit/1d83ef23e96f35c2bb008b5fa37e5157bfc388e4)
* [fixes #348 by assigning language to variations](https://github.com/hyyan/woo-poly-integration/commit/51898f96f2eaa24c322850564cb65923bafd7ddd)
* [fixes #343 by reducing frequency of coupon load](https://github.com/hyyan/woo-poly-integration/commit/edf0993e0a4e7961879e5a95c825da61e0e8b5a7)
* [fixes #354 by including updates from woocommerce](https://github.com/hyyan/woo-poly-integration/commit/42db06e024e794a6e971a515a5c86ce04df081ef)
* [fixes #356](https://github.com/hyyan/woo-poly-integration/commit/29f4895eb2e86c492904a6dcb9b600b850199f40)
* [Fall back to $polylang->pref_lang for AJAX endpoint URL when $polylang->curlang is not available.](https://github.com/hyyan/woo-poly-integration/commit/5a7fc8d704c9666675162d8eba8a53efe5540484)
* [fixed empty attribute for variations](https://github.com/hyyan/woo-poly-integration/commit/3db2c1091082c38bda20ca5c762ad97120bc25ca)
* [Add files via upload](https://github.com/hyyan/woo-poly-integration/commit/f89aec1e7e79987da8fcaec1b63295b3fa123fbc)
* [fixed #370](https://github.com/hyyan/woo-poly-integration/commit/08fe1432fb0981e727409c362c1bbb2892dcb601)
* [fixes #384](https://github.com/hyyan/woo-poly-integration/commit/772acc09640c90aa43b3106dd483ed40e84271c4)
* [Extend the email hooks](https://github.com/hyyan/woo-poly-integration/commit/df276b3184324bb905cdd7e3812bae220c799d02)
* [fixes #401 email subject and title translations](https://github.com/hyyan/woo-poly-integration/commit/89ca15df970ee410513200954f0993fcf0ab93f9)
* [fixes #399](https://github.com/hyyan/woo-poly-integration/commit/90a9e625ead7d6a4429fb4db51174367014df701)


### 1.2.0 

* [Implements #337 by enabling privacy strings translation](https://github.com/hyyan/woo-poly-integration/commit/d9592822d4041ce299c04576e0a5d2bb1de4d646)
* [Implements #339 by translating store notice](https://github.com/hyyan/woo-poly-integration/commit/244aacfb4a6135beca73876cc856d1868834a9aa)

### 1.1.0

* [add wc version to comment headers](https://github.com/hyyan/woo-poly-integration/commit/756a380a0ef7ee425a55230f429462c471a62328)
* [fixes #283 by handling shortcode with no ids set](https://github.com/hyyan/woo-poly-integration/commit/19dd4b3e0afb12c9c1f73b45e45d2d1e95d1958d)
* [Add Spanish Translate](https://github.com/hyyan/woo-poly-integration/commit/730462cf01c91f7b61259105cce4e9c9bc2ba9b5)
* [fix issue #320](https://github.com/hyyan/woo-poly-integration/commit/5b0fae23200cf3c007ee712eeba43ee43059fdad)
* [Fix #325 Coupon optimization](https://github.com/hyyan/woo-poly-integration/commit/74f8620d55c006fa0650f63d0188a3d7f69c67e3)
* [fix issue #320 (re-commit)](https://github.com/hyyan/woo-poly-integration/commit/2b3c8972f0630ff89bc55d3d3e7caca69e09f006)
* [Merge pull request #322 from nunhes/master](https://github.com/hyyan/woo-poly-integration/commit/1cabc3b86a13861410e2ad4c4200e561e2e10b3f)
* [Update readme](https://github.com/hyyan/woo-poly-integration/commit/8113f35caf90c821204b14891e0b91448ac9bbfc)
* [Merge pull request #326 from masato-igarashi/master](https://github.com/hyyan/woo-poly-integration/commit/e3a8db6225ebc4bc82a78a3d55cffcb76ce47f88)
* [Fix #317](https://github.com/hyyan/woo-poly-integration/commit/c7eb1f786d9d6b0cdfaa224b0d4221b0f1666c05)
* [Fix Ajax endpoint URL again, fixes #319](https://github.com/hyyan/woo-poly-integration/commit/888df716c43c0191c32e2b8bfc11d7ea0a9ed055)
* [Identify woocommerce and polylang if installed as mu-plugins](https://github.com/hyyan/woo-poly-integration/commit/9e6dd41acccd7ec4a6329e937d1b39acb834c7af)
* [Change the way of checking polylang and woocommerce versions](https://github.com/hyyan/woo-poly-integration/commit/7772cff41d362c59610df1c3521f4b8be91713ff)
* [Remove Unneeded code since we use class name to check if plugin is activated or not](https://github.com/hyyan/woo-poly-integration/commit/be1417a2e346f8edfc690d845c451d7c78c42839)
* [Merge pull request #329 from marian-kadanka/fix-319-ajax-endpoint-url-again](https://github.com/hyyan/woo-poly-integration/commit/f9e7209a79ac818bf95d9816f72ccc82eefb532f)
* [Merge pull request #335 from ahmedofali/master](https://github.com/hyyan/woo-poly-integration/commit/70ce81c65e843e3050c282e4b661b04f74c93ac4)

### 1.0.4

* [Fix #257 , Fix #247](https://github.com/hyyan/woo-poly-integration/commit/9eaf0cabdf25425221c230d4459d26ea82c84605)
* [Fix #248 , Fix #266 upgrader_process_complete is not ideal](https://github.com/hyyan/woo-poly-integration/commit/01bc5b2d3df1c08fa4465c585721b7fbf28ed32e)
* [Merge pull request #253 from marian-kadanka/fix-is-front-page-conditional-tag](https://github.com/hyyan/woo-poly-integration/commit/13409a5ea2c1ec7eef252670879d8048a9207ff7)
* [Update tested wp version](https://github.com/hyyan/woo-poly-integration/commit/2837e83a97c9c68f96ce06ea3f23e459bdf6ea82)
* [Fix #260 - add the "Requires PHP" tag in the readme.txt](https://github.com/hyyan/woo-poly-integration/commit/6c73f8a1c4e4b94ffeec2e35e05a57446d4706b8)
* [fixes #268 setup coupon translations only when needed](https://github.com/hyyan/woo-poly-integration/commit/d192347e20d1f4370372276ab55ee77020ad35a0)
* [Merge pull request #263 from szepeviktor/patch-1](https://github.com/hyyan/woo-poly-integration/commit/4fd73ee17ecbbf0710247a98731a096d3c9d8db6)
* [Fix is_front_page() not working on WC shop page set as site's static front page](https://github.com/hyyan/woo-poly-integration/commit/2dc44a136bbef665cad4cd000894b3192e4b5332)
* [Fix Ajax endpoint URL](https://github.com/hyyan/woo-poly-integration/commit/9f9b7581260c87f84ce9f35100ecd872b974e58d)
* [Fix #247 - Woocommerce [products] shortcode and Polylang](https://github.com/hyyan/woo-poly-integration/commit/c18a2735173d62f631d6cdd3679fc68d0786a682)

### 1.0.3

* Fix PHP Fatal error: Class 'NumberFormatter' not found

### 1.0.2
* Fixes #200 Polylang version check fails to detect Polylang PRO
* Cart.js Updated in line with WooCommerce 3.1 cart-fragments.js
* Fixes #215 add string translations for Coupons (includes WooCommerce Extended Coupon Features if installed)
* addresses #168 with a utility function get_translated_variation to help get translated products or variations
* Fixes #217 BACS bank_details() update for woocommerce3
* partially implements #208 WooCommerce 3.1 CSV Import/Export by adding support for synchronising Product Meta and Product Attributes to translated products
* Fixes #213 copy children for Grouped Product
* Fixes #207 suppresses login customization to allow "Pay for Order" links to work when customer is not logged in 
(after login continue to payment page instead of my account home)
* Fixes #212 update deleteRelatedVariation for woocommerce3 warnings
* Fixes #209 cart filling up error logs with variations message

* Fixes #195 Locale number formatting for prices and built-in attributes
* Fixes #190 Products Quick Edit now synchronizes translations
* Fixes #187 Wordpress 4.8 breaks translation for new Variation Products
* Fixes #184 Stock update incorrect if customer switches language while checking out 
* Fixes #186 Shipping Method translation regression from 1.0.1
* Fixes #188 When translating Variations, code tries to create copies of untranslated terms
* Fixes #182 email translation extension hooks thanks to @vendidero/WooCommerce Germanized
* Fixes #181 additional filters in Meta synchronization thanks to @vendidero/WooCommerce Germanized

### 1.0.1

* Fixes #170 when WooCommerce 3.0.8+ active, product variation titles corrected in cart and orders
* Added Documentation links to new wiki Documentation pages
* Added minified javascript (enable SCRIPT_DEBUG to use unminified versions)
* Fixes #174 Error in autofill of missing translations of parent category
* Fixes #175 WooCommerce doesn't pass loop name for some shortcodes
* Fixes #10  Initial setup issues if Polylang is not yet configured

### 1.0.0

**Thanks for @jon007 and @decarvalhoaa for the amazing work in order to release this new version**

This release fixes a number of issues around handling of attributes and translations.
In particular:

1. New translations can now use auto-copy of source language, to help save time translating.
   In future a machine translation will be added.
	 Copy option covers Product Title, Short Description and Long Description.
   Also when creating a new product, any missing Product Categories, Tags and Attributes are copied,
   to avoid unexpected problems which occur if a translation is saved with missing term translations.

2. it is now possible to set up the system to allow different types of product attributes
to be synchronised, translated, or independent in each language. The default options will be:
 - Translation and Synchronization Enabled for Product Attributes
 - Synchronization off for Custom Product Attributes

In this case choose how to set up your product attributes as follows:
 - Translated Attribute?  Add in Products\Attributes and turn on Translation in Polylang at:
        Languages\Settings\Custom Taxonomies 
 - Synchronised Attribute? [eg same value in all languages, eg product code, numeric properties] 
				Add in Products\Attributes and leave Translation turned off in Polyang.
 - Different value in each language? add directly to Product as a Custom Product Attribute

* Enh: synchronisation for Custom Product Attributes and Global Product Attributes can now be 
       turned on and off independently in 
			 Settings\WooPoly, Metas List, Attributes Metas, Custom Product Attributes.
       The fields locker is unlocked for the attribute types which are not synchronized.
* Fix: Global Product Attributes can now be individually configured in Polylang:  
			 When Settings\WooPoly Translation attributes is checked then attributes appear in Polylang:
       Languages\Settings\Custom Taxonomies lists the individual taxonomies 
			 Previously all attributes translation were forced on: now they can be selectively turned
       on and off.  This means that there is no longer any need to create dummy translations for 
       untranslateable values such as reference codes and numeric fields.  Fixes #127.
CHANGE: new Product Attributes are no longer automatically enabled for translation,
       After creating new Attribute, enable Translation in Polylang if needed by checking:
			 Languages\Settings\Custom Taxonomies 
* Enh: Missing Term Translations are now added by default. Fixes #72
			 Applies to Products\Categories, Products\Tags, Products\Attributes 
       Previously missing term translations caused
* Fixes: #148 WooCommerce3 product_visibility is now a taxonomy not a meta item  
* Fixes: #153 Fields locker doesn't correctly lock Product Attributes of type Select 
* Fixes: #147 When adding new variations, tool should also add the new variation to other languages
* Fixes #149 Enable duplication of variable products
* Fix #137 #131 #130 #110 #117, #97, #94, #84, #83, #82 adaptations for wooCommerce 3.0
* Fix #136 Variable product stock sync issue where stock managed at parent level
* Enh #132 Add settings Page link to plugins page
* Fix #128 Allow variation description to be editable in translations
* Fix #129 #138 Account page only shows orders in current language
* Fix #112 Shipping Class are not sync for Product Variations
* Fix #140, #142, #143, #89, #70 Email Translation issues
* Fix #145 correct link from Polylang to Attributes Strings translations
* Fix #95 WooCommmerce product shortcodes not filtering by language
* Fix #104 Tax by allowing translation of Price Display Suffix


### 0.29.1

* Improve Multisite compatibility 
* Fix variation description couldnt be translated
* Fix PHP Notices when translating variable products with variations
* Fix wc translation download

###0.29

* Fix PHP notice in Reports when products dont have translations
* Fixed PHP notice due to Polylang deprecated functions
* Fixed Wordpress database error in reports page
* Fixed missing argument warning in order detailspage and emails
* Fixed strpos() empty needle warning for empty endpoints
* Fixed removing email instructions from 3rd party payment gateways
* Fixed not detecting whether polylang pro is active
* Fixed unable to unselect a complete settings section [Issue #51](https://github.com/hyyan/woo-poly-integration/issues/51)
* Fixed Fields Locker is not working in variation tab [Issue #76](https://github.com/hyyan/woo-poly-integration/issues/76)
* Tested and confirmed working on WordPress 4.6.1, Polylang 2.0.7 and WooCommerce 2.6.7

###0.28

* [Fixed order emails translation](https://github.com/hyyan/woo-poly-integration/pull/49)
* [Fixed shipment methods translation and added support for WooCommerce 2.6.](https://github.com/hyyan/woo-poly-integration/pull/50)
* [Fixed payment gateways translation](https://github.com/hyyan/woo-poly-integration/pull/52)
* [WC2.6 cart page ajax support](https://github.com/hyyan/woo-poly-integration/pull/53)
* [Fixed backend html orders screen](https://github.com/hyyan/woo-poly-integration/pull/55)
* [Fixed product type dropdown selection](https://github.com/hyyan/woo-poly-integration/pull/56)
* [Fixed translation of products variations created before plugin activation](https://github.com/hyyan/woo-poly-integration/pull/60)
* [Fixed variable products default attributes sync](https://github.com/hyyan/woo-poly-integration/pull/61)
* [Fixed variable products (non-taxonomies) attributes sync](https://github.com/hyyan/woo-poly-integration/pull/62)
* [Fixed product shipping class for websites running WooCommerce 2.6 or higher](https://github.com/hyyan/woo-poly-integration/pull/63)
* [Fixed cart translation](https://github.com/hyyan/woo-poly-integration/pull/64)
* [Fixed coupons with multiple products](https://github.com/hyyan/woo-poly-integration/pull/65)
* [Fixed coupon with multiple products](https://github.com/hyyan/woo-poly-integration/pull/66)
* Tested and confirmed working on WordPress 4.6.1 and Polylang 2.0.4

###0.27

* Updated [TranslationsDownloader](https://github.com/hyyan/woo-poly-integration/pull/32) to fetch languages files from woocommerce translation project
* Fixed Issue [#12 : Wrong Return URL after Payment](https://github.com/hyyan/woo-poly-integration/issues/12)
* Fixed Issue [#46 : PLugin is not activated when wordpress multisite is enabled ](https://github.com/hyyan/woo-poly-integration/issues/46)
* Fixed Issue [#26 : variation product and stock sync with language ](https://github.com/hyyan/woo-poly-integration/issues/26)
* Fixed Issue [#35 : Error Message: The plugin can not function correctly](https://github.com/hyyan/woo-poly-integration/issues/35)
* Fixed Issue [#16 : Catchable fatal error: Order List on Dashboard getProductTranslationByObject() is being given a Boolean instead of an WC_Product Object](https://github.com/hyyan/woo-poly-integration/issues/16)
* Fixed Issue [#42 : pll_get_post not defined error](https://github.com/hyyan/woo-poly-integration/issues/42)
* Fixed Issue [#43 : Call to undefined function Hyyan\WPI\pll_default_language()](https://github.com/hyyan/woo-poly-integration/issues/43)
* Fixed Issue [#44 : PLL()->model->get_translations is deprecated](https://github.com/hyyan/woo-poly-integration/issues/44)
* Fixed Issue [#45 : Fatal error: Call to undefined function Hyyan\WPI\pll_get_post_language()](https://github.com/hyyan/woo-poly-integration/issues/45)


###0.26 (Not Released In Wordpress.org)

* Fixed product duplication in shop page when default language is changed
* Fixed total sales is syned even if product is not managing stock
* Fixed duplicator class PHP notice when product is being edited in quick mode
* Fixed random behaviour for product type sync 
* Fixed tax class are not synced

###0.25

* Add the ability to handle the locale code of Paypal checkout
* Fixed locale for emails that are triggered by a Paypal IPN message
* Fixed fields locker is not working in Firefox browser

###0.24

* Added support for Layered Nav Widget
* Added support for endpoints translation
* Fixed products are duplicated when shop page is set as front page
* Fixed [Unable to open order details after 0.20 upgrade](https://wordpress.org/support/topic/unable-to-open-order-details-after-20-upgrade)
* Fixed translations links are not hidden in the order page
* Fixed email is not translated when complete button is used in orders table
* General code improvements

###0.23

* Added support for Woocommerce search widget @see [Duplicated search result](https://wordpress.org/support/topic/duplicated-search-result)
* Fixed translation downloader tries to download woo translations for en_US locale
* Fixed wrong product duplicate behavior 

###0.22

* Added Translation Downloader to auto download woocommerce translation files when a new polylang language is add
* Added Arabic translation
* Fixed translation links are hidden in posts page
* General code improvements

###0.21

* Added admin interface to allow user to control plugin features
* Added link for every attribute to search for its translation in the polylang strings table
* Added generic fields locker
* Added POT file for translation
* Fixed product_type is not synced in 0.20 version
* General code improvements

###0.20

* Added the ability to sync total_sales when stock value is changed
* Added the ability to combine product report with its translation
* Added the ability to combine category report with its translation
* Fixed database error in sales_be_category reports
* Fixed Orders Interface to use the current user language instead of the order language

###0.19

* Added the ability to set the write permalinks that can work with polylang if the default woocomerce permalinks are used

###0.18

* Added basic support for reports (filter by language)
* General fixes

###0.17.2

* Fixed issue#2 (https://github.com/hyyan/woo-poly-integration/issues/2)

###0.17.1

* Removed wrong php used statement

###0.17

* Fixed (Polylang language switcher is disabled even if there is no variable products)
* Added the ability to sync product category custom fields

###0.16

* Added support for product gallery translation

###0.15

* Extended meta list to include _visibility

###0.14

* Made ready to release to wordpress

###0.13

* Coupons support variation product

###0.12

* Added support for emails

###0.11.2

* Added forgotten return statement

###0.11.1

* Corrected query used to get the orders list in my account page

###0.11

* Improved support for product variations 
* Imporved support for orders

###0.10

* Added the ability to handle shop page translation

###0.9

* Refactoring Code 
* Added support for variation products


###0.8

* Refactoring Code 
* General Improvements

###0.7

* Added support for attributes translation

###0.6

* Added support for order translation

###0.5

* Added support for coupon translation

###0.4

* General improvements for product meta sync

###0.3

* added porduct restoreStockQuantity function

###0.2

* Added product meta and stock sync support

###0.1

* Initial commit
