<template>
  <div class="wrap">
    <aside class="sidebar">
      <div class="sb-brand">
        <div class="logo-cube">UTH</div>
        <div>
          <h1>Planeaciones</h1>
          <p>Didácticas UTH</p>
        </div>
      </div>

      <!-- Un bloque de navegación por cada rol que tenga el usuario -->
      <div v-for="grupo in menu" :key="grupo.rol" class="nav-sec">
        <div class="nav-lbl">{{ grupo.rol }}</div>
        <router-link
          v-for="item in grupo.items"
          :key="item.routeName"
          :to="{ name: item.routeName }"
          class="nav-a"
          :class="{ active: route.name === item.routeName }"
        >
          {{ item.label }}
        </router-link>
      </div>

      <div class="nav-sec sidebar-footer">
        <div class="nav-lbl">Mi cuenta</div>
        <router-link
          :to="{ name: 'perfil-2fa' }"
          class="nav-a"
          :class="{ active: route.name === 'perfil-2fa' }"
        >
          Seguridad (2FA)
        </router-link>
        <button class="nav-a nav-a-btn" @click="onLogout">Cerrar sesión</button>
      </div>
    </aside>

    <main class="content">
      <div class="flex jb ic mb4 topbar-greet">
        <span class="sz-sm" style="color: var(--text-300)">
          Hola, <strong style="color: var(--text-700)">{{ auth.user?.nombre_completo }}</strong>
        </span>
        <div class="greet-avatar" :title="auth.user?.nombre_completo">{{ iniciales }}</div>
      </div>
      <slot />
    </main>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { menuFusionado } from '@/config/menus'
import router from '@/router'

const route = useRoute()
const auth = useAuthStore()
const menu = computed(() => menuFusionado(auth.roles))

const iniciales = computed(() => {
  const nombre = auth.user?.nombre_completo || ''
  return (
    nombre
      .split(' ')
      .filter(Boolean)
      .slice(0, 2)
      .map((p) => p[0]?.toUpperCase())
      .join('') || '?'
  )
})

async function onLogout() {
  await auth.logout()
  router.push({ name: 'login' })
}
</script>

<style scoped>
.sidebar {
  display: flex;
  flex-direction: column;
}
.sidebar-footer {
  margin-top: auto;
}
.nav-a-btn {
  width: 100%;
  text-align: left;
  border: none;
  background: none;
  cursor: pointer;
  font-family: gotham, 'Roboto', sans-serif;
}
.topbar-greet {
  animation: fadeIn var(--ts) var(--ease) both;
}
.greet-avatar {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: 700;
  color: white;
  background: var(--grad-brand);
  box-shadow: var(--sh-verde);
  transition: transform var(--ts) var(--ease-spring);
}
.greet-avatar:hover {
  transform: scale(1.08);
}
</style>
