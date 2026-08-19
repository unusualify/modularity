# Vuetify v4 upgrade — remaining review

Kaynak: `get_v4_breaking_changes` + [Vuetify 4 upgrade guide](https://next.vuetifyjs.com/en/getting-started/upgrade-guide/).

Bu dosya **madde 6 ve 7’nin tam uygulanması** ile **madde 8’in tekrar incelenmesi** içindir. Satırlar `packages/modularous/` köküne göredir.

- [x] Madde 10 — docs (bu turda yapıldı)
- [x] Typography plumbing — `$typography` `_abstract` → `_settings` / `base.scss` → Vuetify; b2pressV2 B2P Roboto overlay
- [ ] Madde 6 — display / breakpoint
- [ ] Madde 7 — CSS layers vs unlayered `display:`
- [ ] Madde 8 — typography class kullanımı (yeniden incele; ölçek artık temadan geliyor)

---

## 6. Display / breakpoint küçülmesi

Vuetify 4 varsayılan eşikler küçüldü. Pakette **override yok**.

| Breakpoint | V3 | V4 |
|---|---|---|
| xs | 0 | 0 |
| sm | 600 | 600 |
| md | 960 | **840** |
| lg | 1280 | **1145** |
| xl | 1920 | **1545** |
| xxl | 2560 | **2138** |

`VContainer` max-width da küçülür (md 900→700, lg 1200→1000, xl 1800→1400, xxl 2400→2000).

### Karar

V3 layout’u korumak mı, yoksa yeni eşiklerle yeniden ölçmek mi? İkisini birden denemeden uygulama.

### V3’ü geri almak (seçilirse)

- [ ] `vue/src/js/plugins/vuetify.js` — `createVuetify` içine:

```js
display: {
  thresholds: { md: 960, lg: 1280, xl: 1920, xxl: 2560 },
},
```

- [ ] `vue/src/sass/core/_settings.scss` — `@use 'vuetify/settings' with (` bloğuna:

```scss
$grid-breakpoints: (
  'md': 960px,
  'lg': 1280px,
  'xl': 1920px,
  'xxl': 2560px,
),
```

- [ ] App teması (`resources/vendor/modularous/themes/{name}/sass/_settings.scss` / `_abstract.scss`) aynı `$grid-breakpoints` değerini set ediyor mu kontrol et. Şu an `b2pressV2` override etmiyor.

### Etkilenen kod (yeniden doğrula)

- [ ] `vue/src/js/hooks/useTable.js` **236** — `display.thresholds.value[bp]`; md/lg kayması tablo mobil kırılımını erken tetikler.
- [ ] `cols` / `md` / `lg` / `xl` kullanan tüm `v-col` / widget col tanımları (ör. `src/View/Widgets/BoardInformationWidget.php` `widgetCol`).
- [ ] `useDisplay()` (`xsOnly`, `smAndDown`, `mdAndUp`, …) tüketen Vue dosyaları.
- [ ] Auth form genişliği: `vue/src/js/components/shared/Auth.vue` `formSheetStyle` breakpoint anahtarları.

### Doğrulama

- [ ] Tablo aksiyonları / mobile breakpoint (dar ve orta viewport).
- [ ] Stepper / dashboard grid (`md`, `lg`).
- [ ] Auth kart genişliği breakpoint geçişleri.

---

## 7. CSS layers — unlayered `display:` utility’leri ezer

Vuetify 4 stilleri **zorunlu cascade layer** içinde. Layer’sız CSS (SFC `<style>` dahil) her Vuetify utility’sinin üstündedir — `.d-none` bile kaybeder. Filepond’daki aynı sınıf hata.

Layer sırası: `vuetify-core` → `vuetify-components` → `vuetify-overrides` → `vuetify-utilities` → `vuetify-final`.

### Bilinen noktalar

- [ ] `vue/src/js/components/inputs/RadioGroup.vue` **90** — `.v-input__control { display: block }`
- [ ] `vue/src/js/components/inputs/FormTabs.vue` **446** — aynı
- [ ] `vue/src/js/components/inputs/ChecklistGroup.vue` **91** — aynı
- [ ] `vue/src/js/components/inputs/Image.vue` **742–896** — birçok `display:` / `display: block!important` (özellikle **742**, **745**, **749**, **754**, **767**, **819**, **829**, **839**, **863**, **868**, **874**, **878**, **896**)

### Tarama (tekrar çalıştır)

```bash
rg -n '^\s*display:\s*(block|flex|none|grid)|display:\s*block\s*!important' \
  vue/src modules --glob '*.{vue,scss,sass,css}' --glob '!vue/src/sass/core/abstract/vuetify/**'
```

App override’ları da bak: `resources/vendor/modularous/` (host app).

### Uygulama kuralı

- Hide/show için Vuetify utility (`d-none`, `d-flex`, …) kullanılıyorsa, unlayered `display:` o utility’yi ezer — `display` kuralını kaldır veya `@layer vuetify-overrides { … }` içine al.
- Bileşenin kendi layout’u (ör. `.v-input__control { display: block }`) utility ile çakışmıyorsa layer’a almak yine de doğru; ileride `.d-flex` eklendiğinde aynı bug tekrar etmesin.
- `!important` ile utility ezmek yerine layer sırası.

### Doğrulama

- [ ] Filepond avatar / browse + `d-none` (önceki regresyon).
- [ ] RadioGroup / FormTabs / ChecklistGroup kontrol alanı görünürlüğü.
- [ ] Image input hover actions / crop / editor (`d-none` veya `v-show` varsa).

---

## 8. Typography — tekrar incele

MD2 sınıf **adları** duruyor; V4’te **punto / ağırlık / line-height** MD3. Toplu rename vs Sass revert ayrı karar.

### MD2 → MD3 eşleme

| MD2 class | MD3 class |
|---|---|
| `text-h1` … `text-h3` | `text-display-large` … `text-display-small` |
| `text-h4` … `text-h6` | `text-headline-large` … `text-headline-small` |
| `text-subtitle-1`, `text-body-1` | `text-body-large` |
| `text-body-2` | `text-body-medium` |
| `text-caption` | `text-body-small` |
| `text-subtitle-2` | `text-label-large` |
| `text-overline` | `text-label-small` |

Toplu araç: [vuetify-codemods](https://github.com/vuetifyjs/vuetify). Geçici: upgrade guide’daki Sass revert snippet.

### Yeniden taranacak yerler

PHP / Blade / config (satır net):

- [ ] `src/View/Widgets/BoardInformationWidget.php` **32**, **34** — `text-subtitle-2`, `text-h4`
- [ ] `config/defers/widgets.php` **34**, **36** — aynı
- [ ] `config/defers/auth_component.php` **28** — `text-h4`
- [ ] `src/Helpers/input.php` **719** — `text-body-1`
- [ ] `src/Http/Controllers/Traits/Form/FormSchema.php` **581** — `text-body-1`
- [ ] `src/Helpers/component.php` **127** — `text-subtitle-1` + `grey--text` (V2 kalıntısı)
- [ ] `modules/SystemNotification/Config/config.php` **324** — `text-body-1`
- [ ] `modules/ErrorPage/Resources/views/error_page/{403,404,500}.blade.php` **10–12**
- [ ] `modules/Cms/Http/Controllers/LayoutBuilderHtmlPreviewController.php` **23**
- [ ] `modules/Cms/Http/Controllers/LayoutBuilderShellDraftPreviewController.php` **116**
- [ ] `modules/Cms/Resources/assets/Pages/Sitemap/Index.vue` **128**, **132**, **182**
- [ ] `vue/src/js/components/shared/StepperForm.vue` **164** — `grey--text`

Vue (~63 dosya, ~179 eşleşme). Yoğun:

- [ ] `vue/src/js/components/shared/SystemConsole.vue`
- [ ] `vue/src/js/Pages/BulkSheet.vue`
- [ ] `vue/src/js/components/inputs/PaymentService.vue`
- [ ] `vue/src/js/components/inputs/JsonField.vue`
- [ ] `vue/src/js/components/inputs/Process.vue`
- [ ] `vue/src/js/Pages/ArtisanRunner.vue` + `vue/src/js/components/shared/ArtisanRunner.vue`
- [ ] `vue/src/js/components/inputs/LayoutBlades.vue`

### Bu turda görülen ekstra hatalar (8 ile birlikte düzelt)

- [ ] `vue/src/js/components/shared/ListSection.vue` **311**, **315** — geçersiz sınıf `text-body-medium` (Türkçe ı). Olması gereken: `text-body-medium`. Docs örnekleri de aynı yazımı kopyalamıştı; docs tarafı `text-body-medium` olarak güncellendi.
- [ ] `vue/src/js/components/shared/BoardInformationPlus.vue` **107** — Vue fallback `elevation: 10` (V4 yalnızca 0–5). PHP widget `elevation: 2`. Docs widget değerini anlatıyor.
- [ ] `vue/src/js/components/shared/Title.vue` / `ue-title` `type="h4"` vs MD3 class eşlemesi.

### Karar listesi

- [ ] Codemod ile class rename mi, Sass ile MD2 ölçülerini geri almak mı?
- [ ] `ue-title` `type` prop’u (`h1`–`caption`) V4 class’larına nasıl bağlanacak?
- [ ] Host app (`b2press-app` Blade / custom Vue) aynı taramadan geçecek mi?

### Doğrulama

- [ ] Dashboard glance kartları, error sayfaları, auth banner başlığı, metric dense/normal, list-section satır yazısı.

---

## Referans — bu turda yapılmayan maddeler

1. Select `item.raw` (Phone, Tagger, ArtisanRunner)
2. Grid `dense` / `align` / `justify` / `order`
3. Elevation 8 (`RetainableNotifications.vue`)
4. Date range emit (`Metrics.vue`)
5. VBtn uppercase
9. Ölü Sass `vue/src/sass/core/abstract/vuetify/`
10. Docs — **yapıldı** (aşağıdaki dosyalar)

Docs güncellemeleri:

- `docs/src/pages/guide/custom-auth-pages/custom-auth-component.md`
- `docs/src/pages/guide/components/shared/success.md`
- `docs/src/pages/guide/components/shared/board-information-plus.md`
- `docs/src/pages/guide/components/shared/auth.md`
- `docs/src/pages/guide/components/shared/list-section.md`
