# 🧪 Urolithin A — Single-Active Liposomal Product Page

Template Shopify personnalisé pour une page produit **Urolithin A Liposomal**, inspirée de [panstellarshop.com](https://panstellarshop.com/products/urolithin-a-single-active-liposomal).

> ✅ **Prêt à l'emploi** — Sections, sticky cart, accordéon, comparateur, avis, FAQ, responsive.

---

## 📦 Contenu du thème

```
urolithin-theme/
├── assets/
│   ├── theme-sticky.css              # Styles complets de la page
│   ├── theme-sticky-accordion.js     # Accordéon (Supports / Ingrédients / Certificats)
│   ├── theme-sticky-variant.js       # Sélecteur de variantes + AJAX add to cart
│   ├── theme-sticky-quantity.js      # Gestion de la quantité
│   ├── theme-sticky-cart.js          # Barre sticky cart + intégration Dawn
│   ├── acc-support-leaf.svg          # Icône feuille (accordéon Supports)
│   ├── acc-title-flask.svg           # Icône flacon (accordéon Ingrédients)
│   ├── acc-title-shield.svg          # Icône bouclier (accordéon Certificats)
│   ├── pm-hero-bg.svg                # Fond hero (démo)
│   ├── pm-hero-product.svg           # Image produit flottante (démo)
│   ├── pm-hero-product-thumb.svg     # Miniature sticky cart (démo)
│   ├── pm-benefit-icon.svg           # Icône générique bénéfices
│   ├── pm-shipping-icon.svg          # Icône livraison
│   ├── pm-support-icon.svg           # Icône support
│   ├── pm-ingredient-icon.svg        # Icône ingrédients
│   ├── pm-cert-icon.svg              # Icône certificats
│   ├── pm-compare-product.svg        # Image comparaison (gauche)
│   ├── pm-compare-con.svg            # Image comparaison (droite)
│   ├── pm-lab-results.svg            # Image résultats labo
│   └── pm-coa-certificate.svg        # Certificat COA
├── sections/
│   └── product-main.liquid           # Section personnalisée (tout le contenu)
├── templates/
│   └── product.custom.json           # Template JSON du produit
└── README.md                         # Ce fichier
```

---

## 🚀 ÉTAPE 1 : Uploader les fichiers sur Shopify

### Option A — Via Shopify CLI (recommandé)

```bash
# 1. Installer Shopify CLI
npm install -g @shopify/cli @shopify/theme

# 2. Se connecter à votre boutique
shopify auth login

# 3. Uploader le thème
shopify theme push --store VOTRE-BOUTIQUE.myshopify.com --theme "Urolithin Theme"

# 4. (Optionnel) Développement en direct
shopify theme dev --store VOTRE-BOUTIQUE.myshopify.com
```

### Option B — Via GitHub Integration

```bash
# 1. Initialiser Git
git init
echo "node_modules/
.DS_Store
*.log
config/settings_data.json" > .gitignore

# 2. Ajouter et commiter les fichiers
git add .
git commit -m "Initial theme - Urolithin product page"

# 3. Pousser sur GitHub
git remote add origin https://github.com/VOTRE_COMPTE/urolithin-theme.git
git branch -M main
git push -u origin main
```

Puis dans Shopify : **Boutique en ligne → Thèmes → Ajouter un thème → Connecter depuis GitHub**

### Option C — Manuellement dans l'admin Shopify

1. **Boutique en ligne → Thèmes → Modifier le code**
2. Cliquez **« Ajouter un nouvel asset »** pour chaque fichier SVG dans `assets/`
3. Ouvrez `theme.css` du thème Dawn et **collez le contenu de `theme-sticky.css` à la fin**
4. Ouvrez `theme.js` du thème Dawn et **collez le contenu des 4 fichiers JS à la fin** (dans l'ordre : accordion → variant → quantity → cart)
5. Cliquez **« Ajouter une nouvelle section »** → nommez-la `product-main` → collez `sections/product-main.liquid`
6. Cliquez **« Ajouter un nouveau template »** → type `product` → nommez `product.custom` → collez `templates/product.custom.json`

---

## 🛒 ÉTAPE 2 : Créer le produit

1. **Admin Shopify → Produits → Ajouter un produit**

| Champ | Valeur |
|---|---|
| Titre | `Urolithin A — Single-Active Liposomal` |
| Description | `Liposomal Urolithin A, derived from pomegranate. One molecule. Nothing else.` |
| Prix | `129.00` |
| Comparer à | (laisser vide) |
| SKU | `UA-LIPO-2000` |

2. **Images** : Téléchargez vos photos produit (bouteille/flacon)
3. **Template** : En bas de la page, sélectionnez **`product.custom`** dans le menu déroulant

### Variantes (One-time / Subscribe)

Si vous voulez deux options d'achat, ajoutez ces variantes :

| Option | Titre | Prix |
|---|---|---|
| One-time purchase | One-time purchase | $129.00 |
| Subscribe & Save | Subscribe & Save 15% | $109.65 |

Pour les créer :
1. Dans la fiche produit, section **Variantes** → **Ajouter des variantes**
2. Créez l'option `Purchase type` avec les valeurs `One-time` et `Subscribe`
3. Assignez les prix respectifs

---

## 🎨 ÉTAPE 3 : Personnaliser la page dans l'éditeur visuel

1. **Boutique en ligne → Thèmes → Personnaliser**
2. Ouvrez votre produit Urolithin A
3. Si le template `product.custom` n'est pas actif, cliquez en haut et sélectionnez-le

### Configurer les blocs

La section **Product Main** contient déjà un preset complet. Vous pouvez personnaliser chaque bloc dans l'éditeur visuel :

| Bloc | Quantité | Description |
|---|---|---|
| Hero Icon | 4 | Icônes de confiance (Third-Party Tested, cGMP, COA, Non-GMO) |
| Benefit | 5 | 45 Servings, Single-Active, Liposomal, No Blend, 90-Day Guarantee |
| Support Item | 4 | Cellular energy, Mitochondrial health, Healthy aging, COA-verified |
| Ingredient Item | 6 | Urolithin A, Liposomal, Derived from pomegranate, 2000 mg, etc. |
| Certificate Item | 4 | Third-Party Tested, COA by batch, cGMP, USA |
| Strip Item | 4 | Cellular energy support, Mitochondrial health support, etc. |
| Compare Pro | 6 | Single-Active, Liposomal Delivery, Clinically Dosed, etc. |
| Compare Con | 6 | Multi-Ingredient Blends, No Liposomal Delivery, Underdosed, etc. |
| Ingredient Detail | 5 | Urolithin A (2000mg), Phosphatidylcholine, Sunflower Lecithin, etc. |
| Quality Item | 4 | COA by Batch, cGMP, Purity Tested, Made in USA |
| Subscribe Bullet | 4 | Subscribe & save, Ships every 90 days, Cancel anytime, COA access |
| Plan | 3 | 3 Months ($109.65/mo), 6 Months ($99.00/mo), 12 Months ($89.00/mo) |
| Review | 3 | ★★★★★ (avis clients) |
| FAQ | 4 | Why single-active? Is it pure? Is it tested? What is the guarantee? |

### Remplacer les images SVG par vos visuels

Depuis l'éditeur, chaque paramètre d'image (`image_picker`) peut être remplacé par vos vraies images :

1. Hero background → votre photo lifestyle
2. Hero product image → votre photo produit (bouteille)
3. Accordion icons → vos icônes personnalisées
4. COA certificate → votre vrai certificat
5. Comparaison → vos visuels produits
6. Lab results → votre image de laboratoire

---

## 🔧 ÉTAPE 4 : Fonctionnalités incluses

### ✅ Sticky Cart (barre d'achat fixe)
- Apparaît au scroll après le hero
- Affiche : image produit, titre, prix, sélecteur de quantité, bouton Add to Cart
- Prix total mis à jour en temps réel
- S'intègre avec le cart drawer natif de Dawn

### ✅ Accordéon (3 sections)
- **Supports** : Cellular energy, Mitochondrial health, Healthy aging, COA-verified
- **Ingredients** : Urolithin A, Liposomal, Derived from pomegranate, 2000 mg, etc.
- **Certificates** : Third-Party Tested, COA by batch, cGMP, USA

### ✅ AJAX Add to Cart
- Ajout au panier sans rechargement de page
- Ouverture automatique du cart drawer Dawn
- Gestion des erreurs (stock, réseau)

### ✅ Sélecteur de quantité
- Boutons − / + avec input numérique
- Prix total mis à jour (quantité × prix)
- Synchronisé entre la carte d'achat et le sticky cart

### ✅ Responsive design
- Optimisé mobile (≤767px) et tablette (≤1180px)
- Images SVG vectorielles (aucune perte de qualité)

---

## 🧪 ÉTAPE 5 : Test

### Tests fonctionnels
- [ ] Cliquer "Add to Cart" → le drawer panier s'ouvre
- [ ] Scroller → la barre sticky apparaît en bas
- [ ] Cliquer le bouton sticky → ajoute au panier
- [ ] Changer la quantité → le prix total se met à jour
- [ ] Le checkout fonctionne (panier → paiement)

### Tests visuels
- [ ] La page s'affiche correctement sur mobile (≤767px)
- [ ] La page s'affiche correctement sur tablette (768-1180px)
- [ ] La page s'affiche correctement sur desktop (>1180px)
- [ ] L'accordéon s'ouvre/ferme correctement
- [ ] Les icônes SVG s'affichent
- [ ] Aucune erreur dans la console navigateur

---

## 📸 Ressources visuelles

Pour un rendu professionnel, remplacez les SVG de démonstration par :

| Image | Suggestion |
|---|---|
| Hero background | Photo lifestyle haute résolution (1920×560 px) |
| Hero product | Photo produit sur fond transparent (400×600 px) |
| Comparison product | Photo produit (300×224 px) |
| Comparison "typical" | Photo d'illustration (460×260 px) |
| Lab results | Graphique ou photo de laboratoire (400×240 px) |
| COA certificate | Scan de certificat (300×224 px) |
| Ingredient images | Photos des ingrédients (136×86 px) |

---

## ❓ FAQ

**Q : Puis-je utiliser ce template sans produit Shopify configuré ?**
R : Oui. Le template affiche automatiquement des données de démonstration (titre, description, prix) quand aucun produit n'est trouvé.

**Q : Comment changer les couleurs ?**
R : Modifiez les variables CSS dans `theme-sticky.css`, section `:root` :
```css
:root {
  --pm-cream: #f7efe6;
  --pm-gold: #b87316;
  /* etc. */
}
```

**Q : Le sticky cart fonctionne-t-il avec d'autres thèmes que Dawn ?**
R : Il est optimisé pour Dawn mais peut s'adapter. Le principal risque est le sélecteur du cart drawer (ligne `window.openCartDrawer`).

**Q : Comment remplacer "Add to Cart" par du texte en français ?**
R : Dans l'éditeur Shopify, paramètre de section "Button text" → changez en "Ajouter au panier".

---

## 📄 Licence

Template libre — utilisation personnelle et commerciale autorisée.
