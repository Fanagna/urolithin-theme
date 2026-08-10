# MIGRATION — Shopify → WordPress + WooCommerce

> **Analyse d'architecture** — Aucun fichier du thème Shopify n'a été modifié.

---

## ⚠️ Note préliminaire importante

Le dossier **`shopify-original/` n'existe pas** dans ce projet. Le thème Shopify est en réalité **directement à la racine du dépôt** (`layout/`, `sections/`, `snippets/`, `templates/`, `assets/`, `config/`, `locales/`, `blocks/`). Toute l'analyse ci-dessous porte donc sur ces fichiers, qui constituent le thème Shopify d'origine à ne **jamais modifier**. Le thème est un **Dawn 15.1.0** (thème officiel Shopify) lourdement personnalisé avec des sections PDP maison et plusieurs page builders tiers.

---

## 1. Architecture du thème Shopify

### 1.1 Vue d'ensemble

| Dossier | Fichiers | Rôle |
|---|---|---|
| `layout/` | 10 | Structures HTML racine (theme, page builders, password, gift card) |
| `config/` | 2 | Réglages du thème (`settings_schema.json` + `settings_data.json`) |
| `sections/` | 115 (112 liquid + 3 json) | Blocs de page éditables (Dawn + sections custom PDP + sections marketing) |
| `snippets/` | 91 | Fragments Liquid réutilisables (cartes, médias, intégrations apps) |
| `templates/` | 71 (+ 7 dans `customers/`) | Gabarits de page (index, product.*, collection, page.*, blog, cart…) |
| `assets/` | 902 | CSS (141), JS (67), images (319 webp + 206 png + 46 jpg + 4 mp4 + 1 gif) et SVG (118) |
| `blocks/` | 145 | Blocs `ai_gen_block_*` générés par IA (EComposer/app tierce), réutilisés via le type `_blocks` |
| `locales/` | 58 | Traductions i18n (dont `en.default.json` et `fr.json`) |

### 1.2 Layouts (structures de page)

| Fichier | Usage |
|---|---|
| `layout/theme.liquid` | **Layout principal** — charge Dawn (base.css, composants, cart drawer, header/footer group, scripts globaux, routes cart, JSON-LD) + `ecom_header` / `ecom_footer` |
| `layout/ecom.liquid` | Layout dédié **EComposer** (pages construites avec EComposer) |
| `layout/theme.pagefly.liquid` | Layout dédié **PageFly** (page builder) |
| `layout/theme.pagefly.ai-sales-page.liquid` | Layout PageFly pour la page de vente IA (`page.pf-ai-sales-page-*`) |
| `layout/theme.gempages.header.liquid` / `.footer.liquid` / `.blank.liquid` | Layouts **GemPages** (builder GemPages) |
| `layout/theme.gem-layout-none.liquid` | Layout GemPages sans header/footer |
| `layout/ua-no-header.liquid` | Layout sans header (utilisé par certaines landing pages produit) |
| `layout/password.liquid` | Page de mot de passe (boutique en construction) |

### 1.3 Templates (pages)

**Templates produit personnalisés (le cœur du site, ~30+) :** chaque produit a un template dédié qui référence une section PDP custom ou une combinaison de sections :

| Template produit | Section PDP utilisée |
|---|---|
| `product.ua-stack.json` | `uas-pdp` (héro + buy card + abonnement 15/23/31 % + sticky ATC + comparaison VS + FAQ) |
| `product.ua-liposomal.json` | `ual-pdp` |
| `product.urolithin-pdp.json` | `ps-pdp-urolithin` (+ blocs ingredient/review) |
| `product.urolithin-longevity.json` | `ua-longevity-product-page` |
| `product.urolithin.json`, `product.akkermansia.json`… | `main-product` Dawn (classique) |
| `product.ndl-liposomal.json` | Sections `ndl-*` (héro, absorption, routines, experts, FAQ, sticky cart…) |
| `product.nmn-pdp.json` | `nmn-resveratrol-v2-pdp` |
| `product.shilajit.json` | `shj-pdp` |
| `product.akkermansia.json`, `product.nad-plus.json`, etc. | Mixtes Dawn |

Autres templates : `index.json` (accueil), `collection.json` (+ variantes GemPages/EComposer), `cart.json`, `search.json`, `blog.json`, `article.json`, `list-collections.json`, `page.*.json` (faq, about-us, contact, quizz, social-proof, galery-certificat, vipmember, blog, new-blog, collabs, how-to-heal, judgeme_reviews, ndl-liposomal, ecom-shoe-rack-landing, pf-ai-sales-page…), `password.json`, `gift_card.liquid`, `customers/*` (login, register, account, addresses, order, activate, reset).

### 1.4 Sections custom importantes

**Sections PDP « maison »** (chacune avec son CSS dédié dans `assets/`) :

| Section | CSS associé | Caractéristiques |
|---|---|---|
| `uas-pdp.liquid` | `uas-pdp.css`, `uas-pdp-live.css` | Héro, buy card, plans d'abonnement, sticky ATC, accordéons, comparaison VS |
| `ual-pdp.liquid` | `ual-pdp.css` | Idem famille urolithin |
| `ua-longevity-product-page.liquid` | `ua-longevity-product.css` | Long format (héro, ingrédients, tableau comparatif, garantie, reviews, offre finale) |
| `ps-pdp-urolithin.liquid` | `ps-pdp-urolithin.css` | PDP urolithin (blocs ingredient/review) |
| `nad-pdp.liquid`, `nmn-pdp.liquid`, `nmn-resveratrol-v2-pdp.liquid`, `shj-pdp.liquid`, `akk-pdp.liquid`, `akv-pdp.liquid`, `brb-pdp.liquid`, `crt-pdp.liquid` | `*pdp.css` correspondants | PDP produits individuels |

**Familles de sections marketing** (landing/pages produit long format) :
- `psr-*` (14) : hero-buybox, trustbar, problem, previous-solutions, how-it-works, ingredients, comparison, timeline, testimonials, ugc-videos, press-marquee, faq, final-cta, sticky-cart — **famille « Panstellar »**
- `ndl-*` (9) : hero, absorption, color-settings, data, journey, routines, results, experts-faq, sticky-cart — **famille « NAD+ Liposomal »** (`ndl-liposomal.css/js`)
- `spf-*` (7) : rating-hero, avatar-row, logo-strip, review-cards, video-cards, photo-grid, experts — **famille « Social Proof »** (`spf.css/js`)
- `pnf-nmn-*` (8) : body, formula, results-proof, reviews, ugc, faq-stats, final-cta, + `pnf-nmn.liquid` — **famille NMN** (`section-pnf-nmn.css/js`)
- `sciexp-experts.liquid` — bandeau experts scientifiques (`sciexp-experts.css`)

**Sections tiers / builders :**
- `ecom-*` (6) : sections générées **EComposer** (landing shoe-rack, preview template product/page, quickview, filters, predictive-search)
- `pagefly-section.liquid`, `pf-ai-sales-page-*.liquid` : sections **PageFly**
- `gp-section-*.liquid` : section **GemPages**
- Sections Dawn natives conservées : `main-product`, `main-collection-product-grid`, `main-cart-items/footer`, `cart-drawer`, `header`, `footer`, `announcement-bar`, `featured-collection`, `slideshow`, `multicolumn`, `image-banner`, `rich-text`, `collapsible-content`, `related-products`, `featured-product`, `video`, `contact-form`, etc.

### 1.5 Snippets clés

- **Cartes** : `card-product`, `card-collection`, `article-card`, `price`, `unit-price`, `buy-buttons`, `quantity-input`, `product-variant-picker/options`, `swatch`
- **Médias produit** : `product-media`, `product-media-gallery`, `product-media-modal`, `product-thumbnail`
- **Custom produit** : `panstellar-product-schema` (JSON-LD produit maison), `ua-product-cta-form` (formulaire CTA multi-emplacements), `psr-editable-icon`
- **Apps/SEO/tracking** : `judgeme_core`, `pagefly-*`, `ecom_*` (10+), `avada-seo*` (4), `SEOAnt-SpeedUp`, `stape`/`google-tag-manager-stape`, `enhanced-conversions`, `ga4-add-to-cart`, `posthog-pixel`, `delay-posthog`, `microsoft-clarity`, `microsoft-ads-uet`, `adrol-pixel`, `icart-variables`
- **Header/Footer** : `header_file`, `footer_file`, `header-drawer`, `header-mega-menu`, `header-dropdown-menu`, `header-search`, `country-localization`, `language-localization`

### 1.6 Assets CSS/JS

- **Noyau Dawn** : `base.css`, `global.js`, `cart-drawer.js`, `product-form.js`, `facets.js`, `main-search.js`, etc. (fichiers `*.aio.min.js` = versions minifiées).
- **CSS custom par PDP** : `uas-pdp.css`, `ual-pdp.css`, `nad-pdp.css`, `nmn-pdp.css`, `akk-pdp.css`, `shj-pdp.css`, `brb-pdp.css`, `crt-pdp.css`, `akv-pdp.css`, `ps-pdp-urolithin.css`, `ua-longevity-product.css`, `ndl-liposomal.css`, `section-pnf-nmn.css`, `spf.css`, `sciexp-experts.css`, `psr-panstellar-v11.css`, `gp-global.css`, `pagefly-animation.css`.
- **JS custom** : `ndl-liposomal.js`, `psr-panstellar-v11.js`, `section-pnf-nmn.js`, `spf.js`, `theme-editor.js`, `jquery-3.7.1.min.js`.
- **Images** : ~580 visuels produit/marketing (webp/png/jpg) + 118 SVG d'icônes + 4 vidéos mp4 (UGC produits).

### 1.7 Intégrations tierces détectées

| Intégration | Type | Où |
|---|---|---|
| **PageFly** | Page builder | `layout/theme.pagefly*`, sections `pf-*`, snippets `pagefly-*` |
| **GemPages** | Page builder | `layout/theme.gempages*`, `index.gem-*`, `collection.gem-*` |
| **EComposer** | Page builder | `layout/ecom.liquid`, sections `ecom-*`, snippets `ecom_*`, `blocks/ai_gen_block_*` |
| **Judge.me** | Avis produits | `snippets/judgeme_core`, metafields `judgeme.badge/widget` |
| **Abonnements Shopify natifs** | Subscriptions | `selling_plan` dans `uas-pdp`, `ual-pdp`, `akv-pdp`, `main-cart-items`, `main-order` |
| **Stape GTM / GA4** | Tracking | `google-tag-manager-stape`, `enhanced-conversions`, `ga4-add-to-cart` |
| **PostHog (pixiehog)** | Analytics produit | `posthog-pixel`, `delay-posthog` |
| **Microsoft Clarity + Ads UET** | Analytics | `microsoft-clarity`, `microsoft-ads-uet` |
| **Avada SEO** | SEO | `avada-seo*` |
| **SEOAnt SpeedUp** | Performance | `SEOAnt-SpeedUp`, `avada-defer-css` |
| **Omnisend** | Email/SMS | App block dans `settings_data.json` |
| **Simprosys** | Google Shopping Feed | App block |
| **Shopify Inbox** | Chat | App block (désactivé) |
| **iCart** | Cart drawer | `icart-variables`, `cart.icart.liquid`, `collection.icart.liquid` |
| **Multi-plateformes d'avis (métadonnées)** | Reviews | Loox, Opinew, Ryviu, Stamped, SCM, Air Reviews, TrustReviews, Shopify Product Reviews |

---

## 2. Correspondance Shopify → WordPress

> ✅ **DÉCISION ACTÉE** : reconstruction en **thème WordPress classique en PHP** (structure `header.php` / `footer.php` / `template-parts/`), **sans page builder**. Voir §2.4 pour la convention de fichiers de référence.

### 2.1 Concepts génériques

| Shopify | WordPress / WooCommerce |
|---|---|
| Theme Liquid | Thème WordPress classique (PHP) |
| `layout/*.liquid` | `header.php` / `footer.php` / `functions.php` + templates de page |
| `sections/*.liquid` | `template-parts/` PHP (appelés via `get_template_part()`) |
| `snippets/*.liquid` | `template-parts/` PHP ou `get_template_part()` |
| `templates/*.json` | Page templates WordPress + modèles de page WooCommerce (`single-product.php`, `archive-product.php`, `page-*.php`) |
| `assets/` | `wp-content/themes/<theme>/assets/` ou Media Library |
| `locales/*.json` | **WPML / Polylang** ou fichiers `.mo/.po` |
| `config/settings_data.json` | Customizer WordPress + Réglages du thème (theme mods) |
| `product` + `variants` | Produit WooCommerce + **variations** (attributs) |
| `collection` | **Catégorie de produits** WooCommerce (`product_cat`) |
| `blog` / `article` | Articles WordPress (post type `post`) |
| `page` | Pages WordPress (`page`) |
| `cart` | Panier WooCommerce (AJAX via Storefront API/Fragments) |
| `customer` (compte) | Comptes WooCommerce (`my-account`) |
| `selling_plan` (abonnement) | **WooCommerce Subscriptions** (ou `woocommerce-subscriptions`) |
| `metafields` | **ACF (Advanced Custom Fields)** / Metaboxes / `wp_postmeta` |
| `shop.metafields` | Options WP (`wp_options`) / ACF Options |
| `content_for_header` (apps) | Hooks `wp_head()` / `wp_footer()` |
| `{% render 'x' %}` / `{% include %}` | `get_template_part('x')` / `wc_get_template_part()` |
| `{{ 'file.css' | asset_url \| stylesheet_tag }}` | `wp_enqueue_style()` / `wp_enqueue_script()` |
| `{{ product | image_url }}` | `wp_get_attachment_image()` / `woocommerce_get_product_image()` |
| `{% schema %}` (éditeur visuel) | Champs ACF (groupes réutilisables) + Customizer |
| `theme editor` (online store 2.0) | Customizer WordPress |

### 2.2 Correspondance template par template

| Template Shopify | Cible WordPress |
|---|---|
| `layout/theme.liquid` | `header.php` + `footer.php` (thème) |
| `templates/index.json` | `front-page.php` |
| `templates/product.urolithin.json`, `product.akkermansia.json`, etc. (Dawn classiques) | `single-product.php` (variante par catégorie/produit) |
| `templates/product.ua-stack.json` (uas-pdp) | Template produit dédié `single-product-ua-stack.php` (via `template-parts/`) |
| `templates/product.ndl-liposomal.json` (ndl-*) | Template produit dédié (boucle de `template-parts/ndl-*`) |
| `templates/collection.json` | `archive-product.php` + hooks WooCommerce |
| `templates/cart.json` | `cart.php` WooCommerce |
| `templates/blog.json` / `article.json` | `home.php` / `single.php` |
| `templates/page.about-us.json`, `page.faq.json`… | Pages WP + `page-*.php` (template-parts) |
| `templates/page.pf-ai-sales-page-*.json` | Page + modèle « Landing Page » (`template-landing.php`) |
| `templates/search.json` | `search.php` |
| `templates/password.json` | Plugin « Maintenance Mode » ou `wp_maintenance_mode` |
| `templates/customers/*` | Pages **Mon compte** WooCommerce (`my-account`) |

### 2.3 Correspondance des familles de sections

| Famille Shopify | Équivalent WordPress |
|---|---|
| `uas-pdp`, `ual-pdp`, `ps-pdp-urolithin`, `*pdp` | **1 template `single-product` custom** composé de `template-parts/` (héro, buy-card, sticky-cta…) |
| `psr-*` (14 sections) | Template long-format « Panstellar » (`template-parts/psr-*`) |
| `ndl-*` (9) | Template long-format « NAD+ Liposomal » (`template-parts/ndl-*`) |
| `spf-*` (7) | `template-parts/spf-*` (avis, presse, experts, vidéos) |
| `pnf-nmn-*` (8) | Template long-format « NMN » (`template-parts/pnf-*`) |
| `sciexp-experts` | Section « Experts » (ACF repeater) |
| `ecom-*` / `pagefly-*` / `gp-*` | Pages reconstruites en PHP (contenu statique extrait des templates JSON) |
| Sections Dawn (main-product, slideshow, multicolumn…) | `template-parts/` du thème ou hooks/native WooCommerce |

### 2.4 ⭐ Convention de fichiers adoptée (thème PHP classique)

Mapping de référence **validé** — correspondance fichier → fichier (noms réels du dépôt Shopify) :

| Shopify (fichier réel) | WordPress (fichier cible) | Rôle |
|---|---|---|
| `layout/theme.liquid` | `header.php` + `footer.php` | Structure globale de page |
| `sections/header.liquid` | `template-parts/header.php` | Header : logo, menu, recherche, panier |
| `sections/footer.liquid` | `template-parts/footer.php` | Footer : liens, newsletter, paiements, légal |
| `templates/product.json` | `single-product.php` | Page produit par défaut (WooCommerce) |
| `snippets/card-product.liquid` | `template-parts/product-card.php` | Carte produit (grilles, upsell, panier) |
| `templates/collection.json` | `archive-product.php` | Grille de collection / catégorie |
| `snippets/*.liquid` | `template-parts/*.php` | Fragments réutilisables |
| `sections/main-product.liquid` | `template-parts/single-product/*.php` | Détail produit : galerie, prix, variantes, ATC |
| `sections/cart-drawer.liquid` | `template-parts/cart-drawer.php` | Drawer panier AJAX |
| `sections/main-cart-items.liquid` / `main-cart-footer.liquid` | `template-parts/cart/*.php` | Contenu panier |
| `sections/main-collection-product-grid.liquid` | `template-parts/archive-product/*.php` | Grille + filtres collection |
| `sections/main-blog.liquid` / `main-article.liquid` | `template-parts/blog/*.php` | Blog |
| `sections/main-account.liquid` + `templates/customers/*` | `myaccount/*.php` (WooCommerce) | Compte client |
| `sections/announcement-bar.liquid` | `template-parts/announcement-bar.php` | Bandeau annonce |
| `sections/featured-collection.liquid`, `slideshow.liquid`, `multicolumn.liquid`, `image-banner.liquid`, `rich-text.liquid`, `collapsible-content.liquid`, `video.liquid`, `newsletter.liquid`… | `template-parts/home/*.php` | Sections de la page d'accueil |
| `sections/related-products.liquid` | `template-parts/single-product/related-products.php` | Produits recommandés |
| `sections/contact-form.liquid` | `template-parts/contact-form.php` | Formulaire de contact |
| `sections/main-search.liquid` | `search.php` | Résultats de recherche |

---

## 3. Fichiers qui peuvent être RÉUTILISÉS

> Ces fichiers sont **indépendants de Liquid** (ou quasi) : copie directe possible, au minimum après un simple renommage d'URL.

### 3.1 Images et médias (recommandé : import Media Library)

- **Toutes les images produit/packshots** : `assets/*.webp|png|jpg` (~580 fichiers) → à importer dans la Media Library WordPress puis assigner aux produits.
  - Famille urolithin : `ua-*`, `ual-*`, `uas-*`, `ps-pdp-*`, `ua-hero-*`, `ua-comparison-*`, `ua-ingredient-*`, `ua-step-*`, `ua-avatar-*`
  - Autres PDP : `nad-*`, `nmn-*`, `akk-*`, `akv-*`, `shj-*`, `brb-*`, `crt-*`, `ndl-*`, `nrv-*`, `unm-*`
  - Marketing : `spf-*`, `sciexp-*`, `psr-v10-*`
- **Icônes SVG** : `assets/*.svg` (118) → dossier `/wp-content/themes/<theme>/assets/icons/` ou Sprite SVG.
- **Vidéos mp4** : `akv-crea-*.mp4`, `akv-ugc-*.mp4` (+ posters webp).
- **GIF/animations** : `sparkle.gif`, `loading-spinner.svg`.

### 3.2 CSS

- `base.css` (noyau Dawn) → base du thème enfant (adapté à la grille WooCommerce).
- **CSS custom PDP/marketing** (quasiment 100 % réutilisables, ce sont des feuilles de style « figma ») :
  `uas-pdp.css`, `uas-pdp-live.css`, `ual-pdp.css`, `ua-longevity-product.css`, `ps-pdp-urolithin.css`, `nad-pdp.css`, `nmn-pdp.css`, `nmn-resveratrol-v2-pdp.css`, `akk-pdp.css`, `akv-pdp.css`, `shj-pdp.css`, `brb-pdp.css`, `crt-pdp.css`, `ndl-liposomal.css`, `section-pnf-nmn.css`, `spf.css`, `sciexp-experts.css`, `psr-panstellar-v11.css`, `gp-global.css`, `mask-blobs.css`, `collage.css`.
- `component-*.css` Dawn → utiles pour la base (boutons, cartes, modales).

### 3.3 JS

- `jquery-3.7.1.min.js` → enqueue tel quel (si le thème WP l'utilise).
- Les JS custom sont **indépendants du contexte Shopify** en grande partie : `ndl-liposomal.js`, `psr-panstellar-v11.js`, `section-pnf-nmn.js`, `spf.js` (à relier aux nouvelles URLs).
- **Attention** : tous les JS qui appellent `/cart/add.js`, `/cart/change.js`, `Shopify.designMode`, `window.routes` (routes Shopify) **doivent être réécrits** pour l'API WooCommerce (`wc_add_to_cart_params`, fragments AJAX).

---

## 4. Fichiers qui doivent être CONVERTISIS

### 4.1 Layouts (100 % à réécrire)

| Shopify | Conversion |
|---|---|
| `layout/theme.liquid` | `header.php`, `footer.php`, `functions.php` (enqueue CSS/JS, hooks) |
| `layout/ecom.liquid`, `theme.pagefly*`, `theme.gempages*`, `gem-layout-none`, `ua-no-header` | Abandonnés → contenu récupéré en PHP statique (templates + template-parts) |
| `layout/password.liquid` | Plugin maintenance ou page template |

### 4.2 Sections (Liquid → PHP / template-parts / ACF)

**Stratégie actée : templates PHP + `template-parts/`** (contrôle total, pas de builder), avec ACF pour les contenus éditables. Chaque section Liquid custom devient une partie PHP (convention §2.4).

Familles prioritaires à convertir (les plus grosses + les plus différenciantes) :

| Famille | Fichiers | Complexité |
|---|---|---|
| PDP urolithin | `uas-pdp`, `ual-pdp`, `ua-longevity-product-page`, `ps-pdp-urolithin` | ⭐⭐⭐⭐⭐ (héro, buy card, abonnement, sticky, comparaison, accordéons) |
| PDP produits | `akk-pdp`, `akv-pdp`, `brb-pdp`, `crt-pdp`, `nad-pdp`, `nmn-pdp`, `nmn-resveratrol-v2-pdp`, `shj-pdp` | ⭐⭐⭐⭐ |
| Famille Panstellar | `psr-*` (14) | ⭐⭐⭐⭐ |
| Famille NDL | `ndl-*` (9) | ⭐⭐⭐⭐ |
| Famille SPF | `spf-*` (7) | ⭐⭐⭐ |
| Famille PNF-NMN | `pnf-nmn-*` (8) | ⭐⭐⭐ |
| Sticky cart | `psr-sticky-cart`, `ndl-sticky-cart` + sticky ATC dans `uas-pdp` | ⭐⭐⭐ |
| Sections Dawn natives | `main-product`, `main-cart-items/footer`, `cart-drawer`, `header`, `footer`, `featured-collection`, `slideshow`, `multicolumn`, `collapsible-content`, `related-products`, `image-banner`, `video`, `newsletter`, `contact-form`, `email-signup-banner`… | ⭐⭐ (équivalents WooCommerce natifs ou widgets) |

### 4.3 Snippets (Liquid → template-parts PHP)

- **Tous** les `snippets/` à convertir : `card-product`, `card-collection`, `price`, `buy-buttons`, `quantity-input`, `product-variant-picker/options`, `swatch`, `product-media*`, `panstellar-product-schema` (→ JSON-LD + `woocommerce_structured_data`), `ua-product-cta-form` (→ `woocommerce_single_product_add_to_cart`), `cart-drawer` (→ drawer WooCommerce), `pagination`, `facets`, `progress-bar`.
- Snippets d'**apps** (`pagefly-*`, `ecom_*`, `avada-*`, `SEOAnt-*`, `stape`, `posthog*`, `microsoft-*`, `adrol-pixel`, `judgeme_core`, `icart-*`) → **plugins WordPress équivalents** (voir §5), pas de conversion manuelle.

### 4.4 Blocs `blocks/ai_gen_block_*` (145)

Générés par l'app **AI de génération de sections** (utilisés dans les templates `_blocks`). Ils n'ont pas d'équivalent direct → chaque usage (dans les templates `page.*.json`, `product.*.json`) doit être **repris en contenu statique** (templates PHP / champs ACF / contenu de page) selon l'usage réel. **Ne pas convertir 1 pour 1** — extraire d'abord le contenu utile (textes, images, produits liés) depuis les fichiers JSON des templates.

### 4.5 Assets JS liés à l'écosystème Shopify

| Fichier | Action |
|---|---|
| `global.js`, `cart-drawer.js`, `cart-notification.js`, `product-form.js`, `facets.js`, `main-search.js`, `predictive-search.js`, `quick-add*.js`, `quantity-popover.js`, `media-gallery.js`, `product-modal.js`, `magnify.js`, `price-per-item.js`, `recipient-form.js` | Remplacer par le JS WooCommerce natif ou adapter à l'API REST (`/wp-json/wc/...`) |
| `constants.js`, `pubsub.js`, `theme-editor.js` | Supprimer / remplacer |
| `ecom-*.js`, `ecom-preview-*.js`, `pagefly-*`, `ndl-liposomal.js` (parties `/cart/*.js`) | Adapter les appels AJAX à WooCommerce |

---

## 5. Fonctionnalités Shopify nécessitant WooCommerce (plugins)

| Fonctionnalité Shopify | Besoin WooCommerce |
|---|---|
| **Abonnements / Subscribe & Save (15/23/31 %)** — `selling_plan` | **WooCommerce Subscriptions** (ou WooCommerce Payments récurrent) + logique de plans dans le template produit |
| **Variantes de produit** | Attributs + variations WooCommerce (natif) |
| **Panier drawer AJAX** | Plugin **WooCommerce Cart Drawer** (ou fragment custom via `wc_cart_fragments`) |
| **Sticky Add to Cart / sticky cart** | Plugin « Sticky Add to Cart for WooCommerce » ou custom JS |
| **Avis clients (Judge.me)** | **Judge.me for WooCommerce** (plugin officiel) — migre les avis depuis Shopify |
| **Page builders (PageFly, GemPages, EComposer)** | **Aucun** — reconstruction en PHP (`template-parts/`) selon la convention §2.4 |
| **Champs produits personnalisés** (dosages, ingrédients, certificats, métadonnées produits) | **ACF + ACF for WooCommerce** (product fields) |
| **Collections personnalisées** | Catégories + attributs WooCommerce (natif) + évent. **Product Filters** (tableau de bord filtre) |
| **Blog multilingue + boutique multilingue** (58 locales) | **WPML** ou **Polylang Pro** (+ WooCommerce Multilingual) |
| **Recherche prédictive** | **Fibosearch** ou Recherche AJAX (natif WooCommerce) |
| **GTM / GA4 / Enhanced Conversions / Google Shopping Feed** | **GTM4WP** + **Site Kit by Google** + plugin **Google Listings & Ads** |
| **PostHog** (analytics produit) | Plugin officiel PostHog ou snippet dans `wp_head()` |
| **Microsoft Clarity + UET** | Snippets dans `wp_head()` / plugin officiel Clarity |
| **Email/SMS marketing (Omnisend)** | **Omnisend for WooCommerce** (plugin officiel) |
| **SEO (Avada, SEOAnt)** | **Yoast SEO** ou **RankMath** |
| **Formulaires (contact, quizz, configurateur)** | **Fluent Forms** / **WS Form** / **Contact Form 7** |
| **Page de mot de passe** | Plugin **Maintenance Mode** / **Coming Soon** |
| **Cartes cadeaux** (`gift_card.liquid`) | **WooCommerce Gift Cards** (WooCommerce Payments ou YITH) |
| **Commandes en gros / quick order list** | **WooCommerce Wholesale** ou custom |
| **Chat (Shopify Inbox)** | **Tawk.to** / **Crisp** / plugin officiel |
| **JSON-LD produit personnalisé** | Filtre `woocommerce_structured_data` (remplace `panstellar-product-schema`) |
| **Devise/pays multilingues** | **WooCommerce Currency Switcher** (si multi-devises) |
| **Avis importés multi-plateformes** (Loox, Stamped, Opinew…) | Import CSV dans **WooCommerce Reviews** ou via plugin de migration d'avis |

---

## 6. Ordre recommandé de migration

> **Principe** : reconstruire d'abord le socle, puis le produit « héro » (Urolithin A), puis dupliquer le pattern pour les autres PDP, puis les pages marketing, puis les optimisations.

### Phase 0 — Préparation (fondations)
1. Installer WordPress + WooCommerce + **thème custom PHP** (structure `header.php` / `footer.php` / `template-parts/`) + ACF + WPML/Polylang.
2. Importer tous les médias Shopify (`assets/`) dans la Media Library.
3. Créer la structure de catégories produits (équivalent des collections).
4. Importer les produits : titres, descriptions, prix, comparer à, images, **variantes**, **plans d'abonnement** (via un plugin de migration comme **Cart2Cart** ou export/import CSV/XML WooCommerce).

### Phase 1 — Socle du thème
5. Construire `header.php` / `footer.php` (reprendre le header Dawn : logo, menu, recherche, panier, drawer).
6. `functions.php` : enqueue CSS/JS réutilisés (§3), hooks WooCommerce, JSON-LD.
7. Réimplémenter le **panier drawer AJAX** + le **compteur panier**.
8. Mettre en place le tracking (GTM, GA4, Clarity, PostHog, Omnisend).

### Phase 2 — Produit héro (Urolithin A) : le plus gros chantier
9. Convertir **`uas-pdp`** (ou `ual-pdp`) en template produit dédié :
   - héro + buy card, bascule One-time / Subscribe,
   - plans d'abonnement (Subscriptions),
   - sticky ATC, accordéons (Supports/Ingrédients/Certificats),
   - comparaison VS, qualité, garantie, FAQ, avis Judge.me.
10. Convertir les CSS/JS associés (`uas-pdp.css`, JS AJAX → API WooCommerce).
11. Appliquer le même pattern aux autres PDP de la famille urolithin (`ual-pdp`, `ua-longevity-product-page`, `ps-pdp-urolithin`).

### Phase 3 — Autres PDP produits
12. Convertir les PDP secondaires (`nad`, `nmn`, `shj`, `akk`, `akv`, `brb`, `crt`) en réutilisant le pattern établi (champs ACF par produit → rendu du template).

### Phase 4 — Pages marketing / long format
13. Convertir les familles `ndl-*`, `psr-*`, `pnf-nmn-*`, `spf-*` en templates long-format PHP (`template-parts/`).
14. Reconstruire les pages statiques (faq, about-us, contact, social-proof, galery-certificat, quizz…) en `page-*.php` + template-parts.
15. Reconstruire les landing pages des builders abandonnés (EComposer/GemPages/PageFly) — prioriser celles encore **actives** dans le site.

### Phase 5 — E-commerce transversal
16. Collection/catégorie : grille produits, filtres/facets, tri, pagination.
17. Panier + checkout (thème WooCommerce, logos de paiement, notes de commande).
18. Mon compte, commandes (y compris récurrentes Subscriptions), bons de réduction.
19. Recherche prédictive, blog (import des articles), newsletter, page de mot de passe, gift cards.

### Phase 6 — Finitions & recette
20. SEO (Yoast/RankMath, redirections 301 Shopify → WordPress, sitemap).
21. Performance (compression images, cache, lazy-load), accessibilité, responsive test mobile/tablette/desktop.
22. Migration des avis (Judge.me) et recette fonctionnelle complète : ajout panier, abonnement, checkout, comptes, filtres.

---

## Synthèse chiffrée

| Élément | Shopify | WordPress |
|---|---|---|
| Layouts | 10 | 3-4 (header/footer/functions + templates) |
| Sections | 115 | ~30 template-parts PHP |
| Snippets | 91 | ~25 template-parts + ~20 plugins |
| Templates | 78 | ~12 templates + pages WP |
| Assets réutilisables | 902 | ~600 médias + ~25 CSS + ~5 JS |
| Blocs AI | 145 | Reconstruits en contenu (selon usage) |
| Locales | 58 | WPML/Polylang |

**Estimation effort** (indicative) : base socle ≈ 15-20 %, produit héro Urolithin ≈ 30 %, autres PDP ≈ 20 %, pages marketing ≈ 20 %, finitions ≈ 10-15 %.

---

*Document d'analyse — généré sans modification du thème Shopify. Convention de fichiers actée : **thème PHP classique** (section 2.4). En attente des instructions pour la suite (phase de départ, priorisation).*
