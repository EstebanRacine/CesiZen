# Guide de développement mobile CesiZen avec Capacitor

## 🚀 Configuration terminée !

Votre application Vue.js est maintenant configurée pour être une application mobile avec Capacitor.

### 📱 Plateformes supportées

- **Android** - Prêt pour le développement
- **iOS** - Prêt pour le développement (nécessite macOS pour la compilation)

### 🛠️ Commandes disponibles

#### Développement
```bash
npm run dev                 # Développement web classique
npm run mobile:sync         # Synchroniser les changements avec les plateformes mobiles
npm run mobile:build        # Construire et synchroniser
```

#### Ouverture des IDE natifs
```bash
npm run mobile:android      # Ouvrir Android Studio
npm run mobile:ios          # Ouvrir Xcode (macOS uniquement)
```

#### Exécution sur appareil/émulateur
```bash
npm run mobile:run:android  # Construire et lancer sur Android
npm run mobile:run:ios      # Construire et lancer sur iOS
```

### 🔧 Fonctionnalités ajoutées

1. **Service Capacitor** (`src/plugins/capacitor.js`)
   - Initialisation automatique
   - Gestion de la barre de statut
   - Retour haptique
   - Gestion du réseau
   - Bouton retour Android

2. **Composable Vue** (`src/composables/useCapacitor.js`)
   - Hook réutilisable pour les fonctionnalités mobiles
   - Réactif avec les refs Vue

3. **Composant DeviceInfo** (`src/components/mobile/DeviceInfo.vue`)
   - Affichage des informations de l'appareil
   - Statut réseau en temps réel
   - Test des vibrations

### 📋 Prérequis pour le développement

#### Android
- Android Studio
- SDK Android (API 22+)
- JDK 11 ou supérieur

#### iOS (macOS uniquement)
- Xcode 14 ou supérieur
- iOS SDK
- CocoaPods

### 🔄 Workflow de développement

1. **Développement web** : `npm run dev`
2. **Test des changements** : `npm run mobile:build`
3. **Test sur émulateur** : `npm run mobile:run:android` ou `npm run mobile:run:ios`
4. **Debug natif** : `npm run mobile:android` ou `npm run mobile:ios`

### 📦 Plugins installés

- `@capacitor/splash-screen` - Écran de démarrage
- `@capacitor/status-bar` - Barre de statut
- `@capacitor/app` - Cycle de vie de l'app
- `@capacitor/haptics` - Vibrations/retour haptique
- `@capacitor/device` - Informations appareil
- `@capacitor/network` - Statut réseau
- `@capacitor/keyboard` - Gestion clavier

### 🎨 Personnalisation

#### Configuration Capacitor
Le fichier `capacitor.config.ts` contient la configuration principale :
- App ID : `com.cesizen.app`
- Nom : `CesiZen`
- Configuration splash screen
- Schéma Android

#### Ajout de plugins
```bash
npm install @capacitor/[plugin-name]
npx cap sync
```

### 🚀 Déploiement

#### Android
1. `npm run mobile:android`
2. Build > Build Bundle(s) / APK(s) > Build APK(s)
3. L'APK sera dans `android/app/build/outputs/apk/`

#### iOS
1. `npm run mobile:ios`
2. Product > Archive
3. Distribuer via App Store Connect

### 💡 Conseils

- Toujours exécuter `npm run mobile:sync` après installation de nouveaux plugins
- Tester régulièrement sur de vrais appareils
- Utiliser les Chrome DevTools pour debug la WebView
- Consulter la documentation Capacitor : https://capacitorjs.com/docs

### 🐛 Dépannage

- **Erreur de build** : Vérifiez que les SDK sont installés
- **Plugin non trouvé** : Exécutez `npx cap sync`
- **App ne se lance pas** : Vérifiez les logs dans l'IDE natif
