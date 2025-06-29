import { App } from '@capacitor/app'
import { SplashScreen } from '@capacitor/splash-screen'
import { StatusBar, Style } from '@capacitor/status-bar'
import { Haptics, ImpactStyle } from '@capacitor/haptics'
import { Device } from '@capacitor/device'
import { Network } from '@capacitor/network'
import { Keyboard } from '@capacitor/keyboard'

class CapacitorService {
  constructor() {
    this.isNative = this.checkIfNative()
    this.device = null
    this.networkStatus = null
  }

  checkIfNative() {
    return window.Capacitor?.isNativePlatform() || false
  }

  async initialize() {
    if (!this.isNative) return

    try {
      // Configuration de la barre de statut
      await StatusBar.setStyle({ style: Style.Light })
      await StatusBar.setBackgroundColor({ color: '#1f2937' })

      // Récupération des informations de l'appareil
      this.device = await Device.getInfo()
      
      // Vérification du statut réseau
      this.networkStatus = await Network.getStatus()
      
      // Écoute des changements de réseau
      Network.addListener('networkStatusChange', status => {
        this.networkStatus = status
        this.onNetworkChange(status)
      })

      // Gestion du clavier
      if (this.device.platform === 'ios') {
        Keyboard.setAccessoryBarVisible({ isVisible: true })
      }

      // Masquer le splash screen après initialisation
      setTimeout(async () => {
        await SplashScreen.hide()
      }, 2000)

      console.log('📱 Capacitor initialized successfully')
    } catch (error) {
      console.error('Error initializing Capacitor:', error)
    }
  }

  // Retour haptique
  async hapticFeedback(style = ImpactStyle.Light) {
    if (!this.isNative) return
    try {
      await Haptics.impact({ style })
    } catch (error) {
      console.error('Haptic feedback error:', error)
    }
  }

  // Gestionnaire de changement de réseau
  onNetworkChange(status) {
    if (!status.connected) {
      // Afficher un message d'erreur réseau
      console.warn('📶 Network disconnected')
    } else {
      console.log('📶 Network connected:', status.connectionType)
    }
  }

  // Gestion du bouton retour Android
  setupBackButtonHandler(router) {
    if (!this.isNative) return

    App.addListener('backButton', ({ canGoBack }) => {
      if (canGoBack) {
        router.back()
      } else {
        // Si on est sur la page d'accueil, minimiser l'app
        App.minimizeApp()
      }
    })
  }

  // Gestion de la pause/reprise de l'application
  setupAppStateHandlers() {
    if (!this.isNative) return

    App.addListener('appStateChange', ({ isActive }) => {
      if (isActive) {
        console.log('📱 App resumed')
      } else {
        console.log('📱 App paused')
      }
    })
  }

  // Obtenir les informations de l'appareil
  getDeviceInfo() {
    return this.device
  }

  // Obtenir le statut du réseau
  getNetworkStatus() {
    return this.networkStatus
  }

  // Vérifier si l'application est en mode natif
  isNativeApp() {
    return this.isNative
  }
}

export const capacitorService = new CapacitorService()
export default capacitorService
