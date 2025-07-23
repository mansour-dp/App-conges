<template>
  <!-- Système de notifications toast professionnel -->
  <teleport to="body">
    <div class="toast-container">
      <transition-group name="toast" tag="div" class="toast-list">
        <div
          v-for="toast in visibleToasts"
          :key="toast.id"
          :class="['toast', `toast--${toast.type}`]"
          @mouseenter="pauseTimer(toast.id)"
          @mouseleave="resumeTimer(toast.id)"
        >
          <div class="toast__icon">
            <v-icon :icon="getToastIcon(toast.type)" size="20"></v-icon>
          </div>
          
          <div class="toast__content">
            <div v-if="toast.title" class="toast__title">{{ toast.title }}</div>
            <div class="toast__message">{{ toast.message }}</div>
          </div>
          
          <button class="toast__close" @click="removeToast(toast.id)">
            <v-icon icon="mdi-close" size="16"></v-icon>
          </button>
          
          <!-- Barre de progression -->
          <div 
            class="toast__progress"
            :style="{ 
              width: `${toast.progress || 0}%`,
              backgroundColor: getProgressColor(toast.type)
            }"
          ></div>
        </div>
      </transition-group>
    </div>
  </teleport>
</template>

<script>
import { useNotificationsStore } from '@/stores/notifications';
import { storeToRefs } from 'pinia';
import { ref, computed, watch, onMounted } from 'vue';

export default {
  name: 'NotificationSystem',
  setup() {
    const notificationsStore = useNotificationsStore();
    const { notifications } = storeToRefs(notificationsStore);
    
    const timers = ref(new Map());
    
    const visibleToasts = computed(() => {
      return notifications.value.filter(n => n.show !== false);
    });
    
    const getToastIcon = (type) => {
      const icons = {
        success: 'mdi-check-circle',
        error: 'mdi-alert-circle',
        warning: 'mdi-alert',
        info: 'mdi-information'
      };
      return icons[type] || 'mdi-information';
    };
    
    const getProgressColor = (type) => {
      const colors = {
        success: '#10b981',
        error: '#ef4444',
        warning: '#f59e0b',
        info: '#3b82f6'
      };
      return colors[type] || '#3b82f6';
    };
    
    const removeToast = (id) => {
      notificationsStore.removeToast(id);
      if (timers.value.has(id)) {
        clearInterval(timers.value.get(id));
        timers.value.delete(id);
      }
    };
    
    const pauseTimer = (id) => {
      if (timers.value.has(id)) {
        clearInterval(timers.value.get(id));
      }
    };
    
    const resumeTimer = (id) => {
      const toast = notifications.value.find(n => n.id === id);
      if (toast && !toast.persistent) {
        startTimer(toast);
      }
    };
    
    const startTimer = (toast) => {
      if (toast.persistent) return;
      
      const duration = toast.timeout || 5000;
      const interval = 50;
      let elapsed = 0;
      
      const timer = setInterval(() => {
        elapsed += interval;
        toast.progress = Math.max(0, 100 - (elapsed / duration) * 100);
        
        if (elapsed >= duration) {
          removeToast(toast.id);
        }
      }, interval);
      
      timers.value.set(toast.id, timer);
    };
    
    // Surveiller les nouvelles notifications
    watch(notifications, (newNotifications, oldNotifications) => {
      const oldIds = new Set((oldNotifications || []).map(n => n.id));
      
      newNotifications.forEach(notification => {
        if (!oldIds.has(notification.id)) {
          // Nouvelle notification
          notification.progress = 100;
          startTimer(notification);
        }
      });
    }, { deep: true });
    
    // Exposer la fonction globale
    onMounted(() => {
      window.showToast = (message, type = 'info', duration = 5000) => {
        notificationsStore.addToast({
          message,
          type,
          timeout: duration
        });
      };
    });
    
    return {
      visibleToasts,
      getToastIcon,
      getProgressColor,
      removeToast,
      pauseTimer,
      resumeTimer
    };
  }
};
</script>

<style scoped>
.toast-container {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 9999;
  pointer-events: none;
  max-width: 400px;
}

.toast-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.toast {
  position: relative;
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 16px;
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  box-shadow: 
    0 10px 25px rgba(0, 0, 0, 0.1),
    0 4px 12px rgba(0, 0, 0, 0.05);
  pointer-events: auto;
  transition: all 0.3s ease;
  overflow: hidden;
  min-width: 320px;
  max-width: 400px;
}

.toast:hover {
  background: rgba(255, 255, 255, 0.98);
  transform: translateY(-2px);
  box-shadow: 
    0 15px 35px rgba(0, 0, 0, 0.15),
    0 6px 16px rgba(0, 0, 0, 0.08);
}

.toast--success {
  border-left: 4px solid #10b981;
}

.toast--error {
  border-left: 4px solid #ef4444;
}

.toast--warning {
  border-left: 4px solid #f59e0b;
}

.toast--info {
  border-left: 4px solid #3b82f6;
}

.toast__icon {
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  margin-top: 2px;
}

.toast--success .toast__icon {
  color: #10b981;
  background: rgba(16, 185, 129, 0.1);
}

.toast--error .toast__icon {
  color: #ef4444;
  background: rgba(239, 68, 68, 0.1);
}

.toast--warning .toast__icon {
  color: #f59e0b;
  background: rgba(245, 158, 11, 0.1);
}

.toast--info .toast__icon {
  color: #3b82f6;
  background: rgba(59, 130, 246, 0.1);
}

.toast__content {
  flex: 1;
  min-width: 0;
}

.toast__title {
  font-weight: 600;
  font-size: 14px;
  color: #1f2937;
  margin-bottom: 4px;
  line-height: 1.3;
}

.toast__message {
  font-size: 13px;
  color: #6b7280;
  line-height: 1.4;
  word-wrap: break-word;
}

.toast__close {
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 24px;
  height: 24px;
  border: none;
  background: transparent;
  cursor: pointer;
  border-radius: 50%;
  color: #9ca3af;
  transition: all 0.2s ease;
  margin-top: 2px;
}

.toast__close:hover {
  background: rgba(156, 163, 175, 0.1);
  color: #6b7280;
}

.toast__progress {
  position: absolute;
  bottom: 0;
  left: 0;
  height: 3px;
  background: #10b981;
  transition: width 0.05s linear;
  border-radius: 0 0 12px 12px;
}

/* Animations */
.toast-enter-active {
  transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.toast-leave-active {
  transition: all 0.3s ease-in;
}

.toast-enter-from {
  opacity: 0;
  transform: translateX(100%) scale(0.8);
}

.toast-leave-to {
  opacity: 0;
  transform: translateX(100%) scale(0.9);
}

.toast-move {
  transition: transform 0.3s ease;
}

/* Responsive */
@media (max-width: 768px) {
  .toast-container {
    top: 10px;
    right: 10px;
    left: 10px;
    max-width: none;
  }
  
  .toast {
    min-width: auto;
    max-width: none;
  }
}

@media (max-width: 480px) {
  .toast {
    padding: 12px;
    gap: 10px;
  }
  
  .toast__title {
    font-size: 13px;
  }
  
  .toast__message {
    font-size: 12px;
  }
}
</style>
