<template>
    <AppShell>
        <div class="dashboard-layout">

            <!-- ENCABEZADO -->
            <header class="dash-header widget-contorno-verde">
                <div class="header-icon green-icon">
                    <UserCircle :size="32" color="#00B64F" stroke-width="2" />
                </div>
                <div class="header-info">
                    <h2>Mi perfil</h2>
                    <p>Tus datos, tu contraseña y la seguridad de tu cuenta, todo en un solo lugar.</p>
                </div>
                <div class="header-deco-dots"></div>
            </header>

            <div class="settings-container">

                <!-- =========================================
             TARJETA: MIS DATOS
        ========================================== -->
                <div class="widget-contorno-verde settings-card">
                    <h3 class="ht-sm mb4">Mis datos</h3>

                    <div v-if="errorDatos" class="alert a-danger alert-bounce">{{ errorDatos }}</div>
                    <div v-if="successDatos" class="alert a-success alert-bounce">{{ successDatos }}</div>

                    <div v-if="perfil" class="datos-grid">
                        <div class="field">
                            <label class="fl">Nombre<span class="req">*</span></label>
                            <input v-model.trim="formDatos.nombre" type="text" class="input input-3d-lit" />
                        </div>
                        <div class="field">
                            <label class="fl">Apellido paterno<span class="req">*</span></label>
                            <input v-model.trim="formDatos.apellido_paterno" type="text" class="input input-3d-lit" />
                        </div>
                        <div class="field">
                            <label class="fl">Apellido materno</label>
                            <input v-model.trim="formDatos.apellido_materno" type="text" class="input input-3d-lit" />
                        </div>
                        <div class="field">
                            <label class="fl">Correo institucional</label>
                            <input :value="perfil.email" type="text" class="input input-3d-lit" disabled />
                            <span class="hint">Tu correo no se puede cambiar aquí porque está ligado a tu inicio de
                                sesión y a la verificación en dos pasos.</span>
                        </div>
                    </div>

                    <div v-if="perfil" class="roles-wrap">
                        <span v-for="r in perfil.roles" :key="r" class="badge-3d badge-role">{{ r }}</span>
                        <span v-if="perfil.carrera_dirigida" class="badge-3d badge-role-alt">Dirige: {{
                            perfil.carrera_dirigida }}</span>
                        <span v-for="a in perfil.asignaturas" :key="a" class="badge-3d badge-role-alt">{{ a }}</span>
                    </div>

                    <button class="btn btn-primary btn-add-3d" :disabled="loadingDatos" @click="onGuardarDatos">
                        Guardar cambios
                    </button>
                </div>

                <!-- =========================================
             TARJETA: CAMBIAR CONTRASEÑA
        ========================================== -->
                <div class="widget-contorno-verde settings-card">
                    <h3 class="ht-sm mb4">Cambiar contraseña</h3>
                    <p class="sz-sm text-dim mb4">Al cambiarla, cerraremos tu sesión en otros dispositivos o pestañas
                        por seguridad; esta sesión seguirá abierta.</p>

                    <div v-if="errorPassword" class="alert a-danger alert-bounce">{{ errorPassword }}</div>
                    <div v-if="successPassword" class="alert a-success alert-bounce">{{ successPassword }}</div>

                    <div class="datos-grid">
                        <div class="field">
                            <label class="fl">Contraseña actual<span class="req">*</span></label>
                            <input v-model="formPassword.password_actual" type="password" class="input input-3d-lit"
                                placeholder="••••••••" />
                        </div>
                        <div class="field"></div>
                        <div class="field">
                            <label class="fl">Nueva contraseña<span class="req">*</span></label>
                            <input v-model="formPassword.password" type="password" class="input input-3d-lit"
                                placeholder="Mínimo 8 caracteres" />
                        </div>
                        <div class="field">
                            <label class="fl">Confirmar nueva contraseña<span class="req">*</span></label>
                            <input v-model="formPassword.password_confirmation" type="password"
                                class="input input-3d-lit" placeholder="Repite la nueva contraseña" />
                        </div>
                    </div>

                    <button class="btn btn-primary btn-add-3d" :disabled="loadingPassword" @click="onCambiarPassword">
                        Actualizar contraseña
                    </button>
                </div>

                <!-- =========================================
             AUTENTICACIÓN EN DOS PASOS
        ========================================== -->
                <h3 class="ht-sm section-title">Autenticación en dos pasos</h3>
                <TwoFactorSettingsCard />

            </div>
        </div>
    </AppShell>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { UserCircle } from 'lucide-vue-next'
import AppShell from '@/components/AppShell.vue'
import TwoFactorSettingsCard from '@/components/TwoFactorSettingsCard.vue'
import api from '@/services/api'

const perfil = ref(null)

const formDatos = reactive({ nombre: '', apellido_paterno: '', apellido_materno: '' })
const loadingDatos = ref(false)
const errorDatos = ref('')
const successDatos = ref('')

const formPassword = reactive({ password_actual: '', password: '', password_confirmation: '' })
const loadingPassword = ref(false)
const errorPassword = ref('')
const successPassword = ref('')

onMounted(cargarPerfil)

async function cargarPerfil() {
    const { data } = await api.get('/perfil')
    perfil.value = data
    formDatos.nombre = data.nombre
    formDatos.apellido_paterno = data.apellido_paterno
    formDatos.apellido_materno = data.apellido_materno
}

async function onGuardarDatos() {
    loadingDatos.value = true
    errorDatos.value = ''
    successDatos.value = ''
    try {
        const { data } = await api.put('/perfil', { ...formDatos })
        successDatos.value = data.message
        await cargarPerfil()
    } catch (e) {
        errorDatos.value = e.response?.data?.message || 'No se pudieron guardar tus datos.'
    } finally {
        loadingDatos.value = false
    }
}

async function onCambiarPassword() {
    loadingPassword.value = true
    errorPassword.value = ''
    successPassword.value = ''
    try {
        const { data } = await api.put('/perfil/password', { ...formPassword })
        successPassword.value = data.message
        formPassword.password_actual = ''
        formPassword.password = ''
        formPassword.password_confirmation = ''
    } catch (e) {
        errorPassword.value = e.response?.data?.message || 'No se pudo actualizar tu contraseña.'
    } finally {
        loadingPassword.value = false
    }
}
</script>

<style scoped>
.dashboard-layout {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

/* Reutilizamos el estilo "widget-contorno" pero en acento verde (marca UTH),
   para diferenciar visualmente esta tarjeta de la de seguridad (azul). */
.widget-contorno-verde {
    background: #FFFFFF;
    border: 3px solid rgba(0, 182, 79, 0.15);
    border-radius: var(--r-xl);
    box-shadow: 0 10px 30px -10px rgba(0, 182, 79, 0.15);
    position: relative;
    overflow: hidden;
    transition: transform 0.3s var(--ease-spring), box-shadow 0.3s ease, border-color 0.3s ease;
}

.widget-contorno-verde:hover {
    border-color: rgba(0, 182, 79, 0.3);
    box-shadow: 0 15px 35px -10px rgba(0, 182, 79, 0.2);
    transform: translateY(-2px);
}

.dash-header {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 32px 40px;
    background: linear-gradient(90deg, #FFFFFF 0%, #F0FDF4 100%);
}

.header-icon {
    width: 64px;
    height: 64px;
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    transform: rotate(-3deg);
}

.green-icon {
    background: rgba(0, 182, 79, 0.1);
    border: 2px solid rgba(0, 182, 79, 0.2);
    box-shadow: 0 6px 15px rgba(0, 182, 79, 0.15);
}

.header-info h2 {
    font-family: 'Sora', sans-serif;
    font-size: 28px;
    font-weight: 800;
    color: var(--text-900);
    margin-bottom: 4px;
}

.header-info p {
    color: var(--text-500);
    font-size: 15px;
    margin: 0;
}

.header-deco-dots {
    position: absolute;
    right: 20px;
    top: 20px;
    width: 60px;
    height: 60px;
    background-image: radial-gradient(rgba(0, 182, 79, 0.2) 2px, transparent 2px);
    background-size: 10px 10px;
    opacity: 0.5;
}

.settings-container {
    max-width: 700px;
    width: 100%;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.settings-card {
    padding: 32px;
}

.section-title {
    margin: 8px 0 -8px;
    color: var(--text-900);
}

.text-dim {
    color: var(--text-500);
}

.datos-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 16px;
}

@media (max-width: 600px) {
    .datos-grid {
        grid-template-columns: 1fr;
    }
}

.hint {
    display: block;
    font-size: 12px;
    color: var(--text-500);
    margin-top: 4px;
}

.roles-wrap {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 20px;
}

.badge-3d {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: var(--r-pill);
    font-size: 13px;
    font-weight: 700;
    box-shadow: 0 3px 0 rgba(0, 0, 0, 0.1);
}

.badge-role {
    background: #ECFDF5;
    color: #059669;
    border: 2px solid #10B981;
}

.badge-role-alt {
    background: #EFF6FF;
    color: #2563EB;
    border: 2px solid #93C5FD;
}

.input-3d-lit {
    padding: 14px 16px;
    border-radius: var(--r-md);
    border: 2px solid var(--uth-verde-claro) !important;
    box-shadow: 0 0 0 3px var(--uth-verde-bg), inset 0 3px 6px rgba(0, 0, 0, 0.04) !important;
    background: #FFFFFF !important;
    transition: all 0.2s ease;
    width: 100%;
}

.input-3d-lit:focus {
    border-color: var(--uth-verde) !important;
    box-shadow: 0 0 0 4px var(--uth-verde-ring), inset 0 2px 4px rgba(0, 0, 0, 0.02) !important;
    outline: none;
}

.input-3d-lit:disabled {
    background: var(--bg-page) !important;
    color: var(--text-500);
    cursor: not-allowed;
}

.btn-add-3d {
    background: var(--uth-verde);
    color: white;
    border: none;
    border-radius: var(--r-pill);
    padding: 14px 28px;
    font-weight: 800;
    box-shadow: 0 6px 0 #007734, 0 10px 15px rgba(0, 182, 79, 0.3);
    transform: translateY(-2px);
    transition: all 0.15s cubic-bezier(0.34, 1.56, 0.64, 1);
    cursor: pointer;
}

.btn-add-3d:hover:not(:disabled) {
    transform: translateY(-4px);
    box-shadow: 0 8px 0 #007734, 0 14px 20px rgba(0, 182, 79, 0.4);
}

.btn-add-3d:active:not(:disabled) {
    transform: translateY(2px);
    box-shadow: 0 0 0 #007734;
}

.alert-bounce {
    animation: scaleIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1) both;
}

@keyframes scaleIn {
    from {
        opacity: 0;
        transform: scale(0.95);
    }

    to {
        opacity: 1;
        transform: scale(1);
    }
}
</style>