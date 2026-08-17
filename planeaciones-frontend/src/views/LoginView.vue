<template>
  <div class="auth-wrap">
    <div class="auth-shell">
      <!-- Panel de marca (Lado Izquierdo) -->
      <div class="auth-brand-panel">
        <div class="auth-brand-blob auth-brand-blob-1"></div>
        <div class="auth-brand-blob auth-brand-blob-2"></div>
        <div class="auth-brand-content">
          <div class="auth-logo">UTH</div>
          <h1 class="auth-brand-title">Planeaciones<br /><em>Didácticas</em></h1>
          <p class="auth-brand-sub">Universidad Tecnológica de Huejotzingo</p>
          <div class="hero-pills auth-brand-pills">
            <span class="hero-pill">Secuencias didácticas</span>
            <span class="hero-pill">Validación académica</span>
            <span class="hero-pill">Gestión de usuarios</span>
          </div>
        </div>
      </div>

      <!-- Panel de formulario (Lado Derecho) -->
      <div class="auth-form-panel">
        <div class="auth-form-inner">
          <h2 class="auth-title">Iniciar sesión</h2>
          <p class="auth-subtitle">Ingresa con tu correo institucional para continuar.</p>

          <div v-if="errorMsg" class="alert a-danger">{{ errorMsg }}</div>

          <form @submit.prevent="onSubmit" novalidate>
            <div class="field">
              <label class="fl">Correo institucional<span class="req">*</span></label>
              <input
                v-model.trim="email"
                type="email"
                class="input"
                :class="{ 'input-err': errorMsg }"
                placeholder="usuario@uth.edu.mx"
                autocomplete="username"
                required
              />
            </div>

            <div class="field">
              <label class="fl">Contraseña<span class="req">*</span></label>
              <input
                v-model="password"
                type="password"
                class="input"
                :class="{ 'input-err': errorMsg }"
                placeholder="••••••••"
                autocomplete="current-password"
                required
              />
            </div>

            <button type="submit" class="btn btn-primary auth-submit" :disabled="loading">
              {{ loading ? 'Ingresando…' : 'Iniciar sesión' }}
            </button>
          </form>

          <div class="auth-links">
            <router-link :to="{ name: 'forgot-password' }">¿Olvidaste tu contraseña?</router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { roleHomeName } from '@/config/menus'
import router from '@/router'

const auth = useAuthStore()
const email = ref('')
const password = ref('')
const loading = ref(false)
const errorMsg = ref('')

async function onSubmit() {
  loading.value = true
  errorMsg.value = ''
  try {
    const data = await auth.login(email.value, password.value)
    if (data.requires_2fa) {
      router.push({ name: 'verificar-2fa' })
      return
    }
    router.push({ name: roleHomeName(data.roles) })
  } catch (e) {
    errorMsg.value = e.response?.data?.message || 'No se pudo iniciar sesión. Intenta de nuevo.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
/* ── Contenedor Principal ── */
.auth-wrap {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bg);
  padding: 24px;
}

.auth-shell {
  width: 100%;
  max-width: 960px;
  min-height: 580px;
  display: grid;
  grid-template-columns: 1.1fr 1fr;
  background: var(--bg-elev);
  border-radius: var(--radius-xl);
  overflow: hidden;
  box-shadow: var(--shadow);
  border: 1px solid var(--border-soft);
  animation: scaleIn var(--tsl) var(--ease-spring) both;
}

/* ── Panel de marca (Izquierda) ── */
.auth-brand-panel {
  position: relative;
  background:
    radial-gradient(120% 140% at 15% 20%, rgba(139,92,246,0.35), transparent 55%),
    radial-gradient(90% 120% at 85% 80%, rgba(34,211,168,0.22), transparent 55%),
    linear-gradient(135deg, #171233 0%, #101A33 55%, #0B1B2E 100%);
  overflow: hidden;
  display: flex;
  align-items: center;
  padding: 48px;
  border-right: 1px solid var(--border-soft);
}

/* Luces/Glows flotantes adaptadas al modo oscuro */
.auth-brand-blob {
  position: absolute;
  border-radius: 50%;
}
.auth-brand-blob-1 {
  width: 350px;
  height: 350px;
  top: -120px;
  right: -120px;
  background: radial-gradient(circle, rgba(34, 211, 168, 0.15) 0%, transparent 70%);
  animation: floatSlow 9s var(--ease) infinite;
}
.auth-brand-blob-2 {
  width: 280px;
  height: 280px;
  bottom: -80px;
  left: -40px;
  background: radial-gradient(circle, rgba(139, 92, 246, 0.15) 0%, transparent 70%);
  animation: floatSlow 11s var(--ease) infinite reverse;
}

.auth-brand-content {
  position: relative;
  z-index: 1;
}

.auth-logo {
  width: 52px;
  height: 52px;
  background: linear-gradient(135deg, var(--brand), #0FA989);
  color: #04241C;
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: 'Sora', sans-serif;
  font-weight: 800;
  font-size: 16px;
  margin-bottom: 32px;
  box-shadow: 0 8px 18px -6px rgba(34, 211, 168, 0.45);
}

.auth-brand-title {
  font-family: 'Sora', sans-serif;
  font-size: 42px;
  font-weight: 800;
  color: #FBFAFF;
  line-height: 1.1;
  margin-bottom: 16px;
  letter-spacing: -0.01em;
}
.auth-brand-title em {
  background: linear-gradient(90deg, var(--purple-soft), var(--brand-soft));
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
  font-style: normal;
}

.auth-brand-sub {
  font-size: 15px;
  color: #C7CDE0;
  margin-bottom: 40px;
  line-height: 1.5;
}

.hero-pills {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}
.hero-pill {
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: var(--radius-pill);
  padding: 6px 14px;
  font-size: 12px;
  font-weight: 500;
  color: var(--text-dim);
  backdrop-filter: blur(4px);
}

/* ── Panel de formulario (Derecha) ── */
.auth-form-panel {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 48px;
  background: var(--bg-elev);
}

.auth-form-inner {
  width: 100%;
  max-width: 320px;
  animation: fadeInUp var(--tsl) var(--ease-out) both 100ms;
}

.auth-title {
  font-family: 'Sora', sans-serif;
  font-size: 24px;
  font-weight: 700;
  color: var(--text);
  margin-bottom: 8px;
}

.auth-subtitle {
  font-size: 13.5px;
  color: var(--text-faint);
  margin-bottom: 32px;
  line-height: 1.5;
}

/* Estilos locales de campos */
.field {
  margin-bottom: 20px;
}
.fl {
  display: block;
  font-size: 12.5px;
  font-weight: 600;
  color: var(--text-dim);
  margin-bottom: 8px;
}
.req {
  color: var(--rose);
  margin-left: 4px;
}

.auth-submit {
  width: 100%;
  justify-content: center;
  margin-top: 16px;
  padding: 12px;
  font-size: 14.5px;
}

.auth-links {
  text-align: center;
  margin-top: 28px;
  font-size: 13.5px;
}
.auth-links a {
  color: var(--brand);
  text-decoration: none;
  font-weight: 500;
  transition: color 0.2s ease;
}
.auth-links a:hover {
  color: var(--brand-soft);
  text-decoration: underline;
}

/* ── Responsivo ── */
@media (max-width: 768px) {
  .auth-shell {
    grid-template-columns: 1fr;
    min-height: auto;
  }
  .auth-brand-panel {
    padding: 32px;
    border-right: none;
    border-bottom: 1px solid var(--border-soft);
  }
  .auth-form-panel {
    padding: 32px 24px;
  }
  .auth-brand-pills {
    display: none;
  }
}
</style>