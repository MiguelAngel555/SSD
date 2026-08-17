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
          <!-- Icono dinámico o por defecto para los menús -->
          <component :is="getItemIcon(item.routeName)" :size="16" class="nav-icon" />
          {{ item.label }}
        </router-link>
      </div>

      <div class="nav-sec sidebar-footer">
        <div class="nav-lbl">Mi cuenta</div>
        
        <!-- Botón de Seguridad con Icono de Escudo -->
        <router-link
          :to="{ name: 'perfil-2fa' }"
          class="nav-a"
          :class="{ active: route.name === 'perfil-2fa' }"
        >
          <ShieldCheck :size="16" class="nav-icon shield-ic" />
          Seguridad (2FA)
        </router-link>

        <!-- Botón de Cerrar Sesión en Rojo Intenso con Icono de Puerta -->
        <button class="logout-btn-custom" @click="onLogout">
          <span class="ic logout-ic"><LogOut :size="16" /></span>
          Cerrar sesión
        </button>
      </div>
    </aside>

    <main class="content">
      <div class="flex jb ic mb4 topbar-greet">
        <span class="sz-sm" style="color: var(--text-dim, #9AA6B4)">
          Hola, <strong style="color: var(--text, #F4F6F9)">{{ auth.user?.nombre_completo }}</strong>
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
import { ShieldCheck, LogOut, LayoutDashboard, GraduationCap, Users, FileText, Settings } from 'lucide-vue-next'

const route = useRoute()
const auth = useAuthStore()
const menu = computed(() => menuFusionado(auth.roles))

// Helper opcional para asignar un icono según la ruta del menú si lo deseas
function getItemIcon(routeName) {
  if (routeName?.includes('academico')) return GraduationCap
  if (routeName?.includes('usuarios')) return Users
  if (routeName?.includes('reporte')) return FileText
  if (routeName?.includes('config')) return Settings
  return LayoutDashboard
}

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
  padding-bottom: 12px;
}

/* Enlaces del menú lateral adaptados al tema oscuro */
.nav-a {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 12px;
  color: var(--text-dim, #9AA6B4);
  text-decoration: none;
  font-size: 13.5px;
  font-weight: 500;
  border-radius: 10px;
  position: relative;
  transition: all 0.2s ease;
}

.nav-a:hover {
  background: var(--bg-soft, #171F2B);
  color: var(--text, #F4F6F9);
  transform: translateX(3px);
}

.nav-a.active {
  background: linear-gradient(90deg, rgba(34, 211, 168, 0.12) 0%, transparent 130%);
  color: var(--brand, #22D3A8);
  font-weight: 600;
}

.nav-icon {
  opacity: 0.75;
  transition: opacity 0.2s ease;
}

.nav-a:hover .nav-icon,
.nav-a.active .nav-icon {
  opacity: 1;
  color: var(--brand, #22D3A8);
}

.shield-ic {
  color: #38BDF8;
}

/* Botón de Cerrar Sesión personalizado en Rojo */
.logout-btn-custom {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 12px;
  margin-top: 4px;
  border-radius: 10px;
  border: 1px solid rgba(239, 68, 68, 0.25);
  background: rgba(239, 68, 68, 0.08);
  color: #FCA5A5;
  font-size: 13.5px;
  font-weight: 600;
  cursor: pointer;
  text-align: left;
  width: 100%;
  font-family: inherit;
  transition: all 0.2s ease;
}

.logout-btn-custom:hover {
  background: #EF4444;
  border-color: #EF4444;
  color: #FFFFFF;
  box-shadow: 0 6px 20px -6px rgba(239, 68, 68, 0.6);
  transform: translateY(-1px);
}

.logout-ic {
  color: #F87171;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: transform 0.2s ease;
}

.logout-btn-custom:hover .logout-ic {
  color: #FFFFFF;
  transform: translateX(2px);
}

/* Topbar y Avatar de Usuario */
.topbar-greet {
  animation: fadeIn var(--ts, 250ms) var(--ease, ease) both;
}

.greet-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12.5px;
  font-weight: 700;
  color: #04241C;
  background: linear-gradient(135deg, var(--brand, #22D3A8), #0FA989);
  box-shadow: 0 4px 14px rgba(34, 211, 168, 0.35);
  transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.greet-avatar:hover {
  transform: scale(1.08);
}
</style>