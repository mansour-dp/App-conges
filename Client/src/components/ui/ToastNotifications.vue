<template>
  <teleport to="body">
    <div class="toast-container">
      <transition-group name="toast" tag="div">
        <div
          v-for="notification in notifications"
          :key="notification.id"
          :class="[
            'toast-notification',
            `toast-${notification.type}`,
            { 'toast-entering': notification.entering }
          ]"
        >
          <div class="toast-content">
            <div class="toast-icon">
              <v-icon 
                :icon="getIcon(notification.type)" 
                :color="getIconColor(notification.type)"
                size="20"
              ></v-icon>
            </div>
            <div class="toast-message">
              {{ notification.message }}
            </div>
            <div class="toast-close">
              <v-btn
                icon="mdi-close"
                variant="text"
                size="x-small"
                @click="removeNotification(notification.id)"
                class="close-btn"
              ></v-btn>
            </div>
          </div>
          <div 
            class="toast-progress" 
            :class="`progress-${notification.type}`"
            :style="{ 
              animationDuration: `${notification.duration}ms`,
              animationPlayState: notification.paused ? 'paused' : 'running'
            }"
          ></div>
        </div>
      </transition-group>
    </div>
  </teleport>
</template>

<script>
import { defineComponent, ref, onMounted, onUnmounted } from 'vue';

export default defineComponent({
  name: 'ToastNotifications',
  setup() {
    const notifications = ref([]);
    let notificationId = 0;

    const getIcon = (type) => {
      const icons = {
        success: 'mdi-check-circle',
        error: 'mdi-alert-circle',
        warning: 'mdi-alert',
        info: 'mdi-information'
      };
      return icons[type] || icons.info;
    };

    const getIconColor = (type) => {
      const colors = {
        success: '#10b981',
        error: '#ef4444',
        warning: '#f59e0b',
        info: '#3b82f6'
      };
      return colors[type] || colors.info;
    };

    const addNotification = (message, type = 'info', duration = 5000) => {
      const id = ++notificationId;
      const notification = {
        id,
        message,
        type,
        duration,
        entering: true,
        paused: false
      };

      notifications.value.push(notification);

      // Supprimer l'état "entering" après un court délai
      setTimeout(() => {
        const notif = notifications.value.find(n => n.id === id);
        if (notif) {
          notif.entering = false;
        }
      }, 100);

      // Auto-suppression après la durée spécifiée
      setTimeout(() => {
        removeNotification(id);
      }, duration);

      return id;
    };

    const removeNotification = (id) => {
      const index = notifications.value.findIndex(n => n.id === id);
      if (index > -1) {
        notifications.value.splice(index, 1);
      }
    };

    const pauseNotification = (id) => {
      const notification = notifications.value.find(n => n.id === id);
      if (notification) {
        notification.paused = true;
      }
    };

    const resumeNotification = (id) => {
      const notification = notifications.value.find(n => n.id === id);
      if (notification) {
        notification.paused = false;
      }
    };

    // Écouter les événements globaux pour les notifications
    const handleGlobalNotification = (event) => {
      const { message, type, duration } = event.detail;
      addNotification(message, type, duration);
    };

    onMounted(() => {
      window.addEventListener('show-toast', handleGlobalNotification);
    });

    onUnmounted(() => {
      window.removeEventListener('show-toast', handleGlobalNotification);
    });

    // Exposer les méthodes pour utilisation externe
    window.showToast = (message, type = 'info', duration = 5000) => {
      return addNotification(message, type, duration);
    };

    return {
      notifications,
      getIcon,
      getIconColor,
      addNotification,
      removeNotification,
      pauseNotification,
      resumeNotification
    };
  }
});
</script>

<style scoped>
.toast-container {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 9999;
  display: flex;
  flex-direction: column;
  gap: 12px;
  max-width: 400px;
  pointer-events: none;
}

.toast-notification {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(12px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 12px;
  box-shadow: 
    0 8px 32px rgba(0, 0, 0, 0.12),
    0 4px 16px rgba(0, 0, 0, 0.08);
  overflow: hidden;
  pointer-events: auto;
  position: relative;
  min-width: 320px;
  max-width: 400px;
  transform: translateX(0);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.toast-notification:hover {
  transform: translateX(-4px);
  box-shadow: 
    0 12px 40px rgba(0, 0, 0, 0.16),
    0 6px 20px rgba(0, 0, 0, 0.12);
}

.toast-content {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 16px;
}

.toast-icon {
  flex-shrink: 0;
  margin-top: 2px;
}

.toast-message {
  flex: 1;
  font-size: 14px;
  line-height: 1.5;
  color: #1f2937;
  font-weight: 500;
}

.toast-close {
  flex-shrink: 0;
  margin-top: -4px;
}

.close-btn {
  opacity: 0.6;
  transition: opacity 0.2s ease;
}

.close-btn:hover {
  opacity: 1;
}

.toast-progress {
  position: absolute;
  bottom: 0;
  left: 0;
  height: 3px;
  width: 0;
  animation: toast-progress linear;
  border-radius: 0 0 12px 12px;
}

@keyframes toast-progress {
  from { width: 0; }
  to { width: 100%; }
}

/* Styles par type */
.toast-success {
  border-left: 4px solid #10b981;
}

.progress-success {
  background: linear-gradient(90deg, #10b981, #059669);
}

.toast-error {
  border-left: 4px solid #ef4444;
}

.progress-error {
  background: linear-gradient(90deg, #ef4444, #dc2626);
}

.toast-warning {
  border-left: 4px solid #f59e0b;
}

.progress-warning {
  background: linear-gradient(90deg, #f59e0b, #d97706);
}

.toast-info {
  border-left: 4px solid #3b82f6;
}

.progress-info {
  background: linear-gradient(90deg, #3b82f6, #2563eb);
}

/* Animations d'entrée/sortie */
.toast-enter-active {
  transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

.toast-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 1, 1);
}

.toast-enter-from {
  transform: translateX(100%);
  opacity: 0;
}

.toast-leave-to {
  transform: translateX(100%);
  opacity: 0;
}

.toast-move {
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Animation d'apparition */
.toast-entering {
  animation: toast-bounce 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

@keyframes toast-bounce {
  0% { 
    transform: translateX(100%) scale(0.8);
    opacity: 0;
  }
  50% { 
    transform: translateX(-10px) scale(1.05);
  }
  100% { 
    transform: translateX(0) scale(1);
    opacity: 1;
  }
}

/* Responsive */
@media (max-width: 640px) {
  .toast-container {
    top: 10px;
    right: 10px;
    left: 10px;
    max-width: none;
  }
  
  .toast-notification {
    min-width: unset;
    max-width: none;
  }
  
  .toast-content {
    padding: 14px;
  }
  
  .toast-message {
    font-size: 13px;
  }
}
</style>
