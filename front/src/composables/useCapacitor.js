import { ref, onMounted } from 'vue'
import capacitorService from '@/plugins/capacitor.js'

export function useCapacitor() {
  const isNative = ref(false)
  const deviceInfo = ref(null)
  const networkStatus = ref(null)

  onMounted(() => {
    isNative.value = capacitorService.isNativeApp()
    deviceInfo.value = capacitorService.getDeviceInfo()
    networkStatus.value = capacitorService.getNetworkStatus()
  })

  const hapticFeedback = (style) => {
    return capacitorService.hapticFeedback(style)
  }

  const isOnline = () => {
    return networkStatus.value?.connected ?? true
  }

  return {
    isNative,
    deviceInfo,
    networkStatus,
    hapticFeedback,
    isOnline
  }
}
