<template>
  <div class="auth-wrap">
    <div class="auth-shell">
      <!-- Panel de marca -->
      <div class="auth-brand-panel">
        <div class="auth-brand-blob auth-brand-blob-1"></div>
        <div class="auth-brand-blob auth-brand-blob-2"></div>
        <div class="auth-brand-content">
          <div class="logo-cube auth-logo">UTH</div>
          <h1 class="auth-brand-title">Planeaciones<br /><em>Didácticas</em></h1>
          <p class="auth-brand-sub">Universidad Tecnológica de Huejotzingo</p>
          <div class="hero-pills auth-brand-pills">
            <span class="hero-pill">Secuencias didácticas</span>
            <span class="hero-pill">Validación académica</span>
            <span class="hero-pill">Gestión de usuarios</span>
          </div>
        </div>
      </div>

      <!-- Panel de formulario -->
      <div class="auth-form-panel">
        <div class="auth-form-inner">
          <h2 class="ht-md mb4">Iniciar sesión</h2>
          <p class="sz-sm mb4" style="color: var(--text-300)">Ingresa con tu correo institucional para continuar.</p>

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

            <button type="submit" class="btn btn-primary btn-lg auth-submit" :disabled="loading">
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
.auth-wrap {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bg-page);
  padding: var(--s5);
}

.auth-shell {
  width: 100%;
  max-width: 900px;
  min-height: 560px;
  display: grid;
  grid-template-columns: 1.05fr 1fr;
  background: var(--bg-white);
  border-radius: var(--r-xl);
  overflow: hidden;
  box-shadow: var(--sh-lg);
  border: 1px solid var(--border);
  animation: scaleIn var(--tsl) var(--ease-spring) both;
}

/* ── Panel de marca ── */
.auth-brand-panel {
  position: relative;
  background: var(--grad-brand-deep);
  background-size: 220% 220%;
  animation: floatSlow 14s var(--ease) infinite;
  overflow: hidden;
  display: flex;
  align-items: center;
  padding: var(--s7);
}

.auth-brand-blob {
  position: absolute;
  border-radius: 50%;
  background: rgba(255, 255, 255, .06);
}
.auth-brand-blob-1 {
  width: 260px;
  height: 260px;
  top: -70px;
  right: -70px;
  animation: floatSlow 9s var(--ease) infinite;
}
.auth-brand-blob-2 {
  width: 220px;
  height: 220px;
  bottom: -80px;
  left: -40px;
  background: rgba(163, 230, 53, .08);
  animation: floatSlow 11s var(--ease) infinite reverse;
}

.auth-brand-content {
  position: relative;
  z-index: 1;
}

.auth-logo {
  width: 48px;
  height: 48px;
  font-size: 15px;
  margin-bottom: var(--s5);
}

.auth-brand-title {
  font-size: var(--h-xl);
  font-weight: 700;
  color: white;
  line-height: var(--lh-tight);
  margin-bottom: var(--s3);
}
.auth-brand-title em {
  color: #A8EBA0;
  font-style: normal;
}

.auth-brand-sub {
  font-size: var(--p-md);
  color: rgba(255, 255, 255, .68);
  margin-bottom: var(--s6);
}

.auth-brand-pills {
  margin-top: 0;
}

/* ── Panel de formulario ── */
.auth-form-panel {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: var(--s7);
}

.auth-form-inner {
  width: 100%;
  max-width: 320px;
  animation: fadeInUp var(--tsl) var(--ease-out) both 100ms;
}

.auth-submit {
  width: 100%;
  justify-content: center;
  margin-top: var(--s2);
}
.auth-links {
  text-align: center;
  margin-top: var(--s5);
  font-size: var(--p-sm);
}
.auth-links a {
  color: var(--uth-verde);
  text-decoration: none;
}
.auth-links a:hover {
  text-decoration: underline;
}

@media (max-width: 760px) {
  .auth-shell {
    grid-template-columns: 1fr;
    min-height: 0;
  }
  .auth-brand-panel {
    padding: var(--s6);
  }
  .auth-brand-pills {
    display: none;
  }
}
</style>
