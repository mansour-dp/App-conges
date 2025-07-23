import { defineStore } from 'pinia'

export const useNotificationsStore = defineStore('notifications', {
  state: () => ({
    notifications: [],
    maxNotifications: 5,
    globalNotifications: [
      // Notifications pour la toolbar/sidebar
      {
        id: 1,
        type: 'info',
        title: 'Nouvelle demande',
        message: 'Une nouvelle demande de congé a été soumise par Fatou Sall',
        read: false,
        timestamp: new Date().toISOString(),
        category: 'demande'
      },
      {
        id: 2,
        type: 'success',
        title: 'Demande validée',
        message: 'Votre demande de congé du 15/08 au 25/08 a été approuvée',
        read: false,
        timestamp: new Date(Date.now() - 3600000).toISOString(), // 1h ago
        category: 'validation'
      },
      {
        id: 3,
        type: 'warning',
        title: 'Rappel planification',
        message: 'N\'oubliez pas de planifier vos congés annuels avant fin décembre',
        read: true,
        timestamp: new Date(Date.now() - 86400000).toISOString(), // 1 day ago
        category: 'rappel'
      }
    ]
  }),

  getters: {
    unreadCount: (state) => state.globalNotifications.filter(n => !n.read).length,
    unreadNotifications: (state) => state.globalNotifications.filter(n => !n.read),
    allNotifications: (state) => state.globalNotifications.sort((a, b) => new Date(b.timestamp) - new Date(a.timestamp))
  },

  actions: {
    // Pour les notifications Toast (temporaires)
    addToast(notification) {
      const newNotification = {
        id: Date.now(),
        type: 'info', // success, error, warning, info
        title: '',
        message: '',
        timeout: 5000,
        persistent: false,
        show: true,
        ...notification
      }
      
      this.notifications.unshift(newNotification)
      
      // Limiter le nombre de notifications
      if (this.notifications.length > this.maxNotifications) {
        this.notifications = this.notifications.slice(0, this.maxNotifications)
      }
      
      // Auto-remove si pas persistant
      if (!newNotification.persistent) {
        setTimeout(() => {
          this.removeToast(newNotification.id)
        }, newNotification.timeout)
      }

      return newNotification.id
    },

    removeToast(id) {
      const index = this.notifications.findIndex(n => n.id === id)
      if (index > -1) {
        this.notifications.splice(index, 1)
      }
    },

    markAsShown(id) {
      const notification = this.notifications.find(n => n.id === id)
      if (notification) {
        notification.show = false
      }
    },

    clearAllToasts() {
      this.notifications = []
    },

    // Pour les notifications globales (toolbar/persistantes)
    addGlobalNotification(notification) {
      const newNotification = {
        id: Date.now(),
        type: 'info',
        title: '',
        message: '',
        read: false,
        timestamp: new Date().toISOString(),
        category: 'general',
        ...notification
      }
      
      this.globalNotifications.unshift(newNotification)
    },

    markAsRead(id) {
      const notification = this.globalNotifications.find(n => n.id === id)
      if (notification) {
        notification.read = true
      }
    },

    markAllAsRead() {
      this.globalNotifications.forEach(n => n.read = true)
    },

    removeGlobalNotification(id) {
      const index = this.globalNotifications.findIndex(n => n.id === id)
      if (index > -1) {
        this.globalNotifications.splice(index, 1)
      }
    },

    // Méthodes utilitaires pour différents types d'actions
    notifySuccess(message, title = 'Succès') {
      this.addToast({
        type: 'success',
        title,
        message,
        timeout: 3000
      })
    },

    notifyError(message, title = 'Erreur') {
      this.addToast({
        type: 'error',
        title,
        message,
        timeout: 5000,
        persistent: false
      })
    },

    notifyWarning(message, title = 'Attention') {
      this.addToast({
        type: 'warning',
        title,
        message,
        timeout: 4000
      })
    },

    notifyInfo(message, title = 'Information') {
      this.addToast({
        type: 'info',
        title,
        message,
        timeout: 4000
      })
    },

    // Notifications spécifiques aux actions métier
    notifyDemandeSubmitted(demandeur) {
      this.addGlobalNotification({
        type: 'info',
        title: 'Nouvelle demande',
        message: `${demandeur} a soumis une nouvelle demande de congé`,
        category: 'demande'
      })
      
      this.notifySuccess('Demande soumise avec succès!')
    },

    notifyDemandeValidated(action, demandeur) {
      const message = action === 'approve' 
        ? `Demande de ${demandeur} approuvée avec succès`
        : `Demande de ${demandeur} rejetée`
      
      this.addGlobalNotification({
        type: action === 'approve' ? 'success' : 'warning',
        title: action === 'approve' ? 'Demande approuvée' : 'Demande rejetée',
        message,
        category: 'validation'
      })
      
      if (action === 'approve') {
        this.notifySuccess(message)
      } else {
        this.notifyWarning(message)
      }
    },

    notifyDemandeCancelled(demandeur) {
      this.addGlobalNotification({
        type: 'info',
        title: 'Demande annulée',
        message: `${demandeur} a annulé sa demande de congé`,
        category: 'demande'
      })
      
      this.notifyInfo('Demande annulée avec succès')
    }
  }
})
