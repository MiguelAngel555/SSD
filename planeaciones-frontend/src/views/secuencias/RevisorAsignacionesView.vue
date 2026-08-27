<template>
  <AppShell>
    <div class="sec">
      <div class="sec-hdr">
        <div class="sec-num"><UserCheck :size="20" /></div>
        <div>
          <h2>Asignación de revisores</h2>
          <p>Define qué cuatrimestre(s) de qué carrera debe revisar cada Revisor.</p>
        </div>
      </div>

      <div v-if="errorMsg" class="alert a-danger mb4">{{ errorMsg }}</div>
      <div v-if="successMsg" class="alert a-success mb4">{{ successMsg }}</div>

      <!-- Alta de nueva asignación -->
      <div class="card mb4">
        <div class="cp">
          <div class="field-row">
            <div class="field">
              <label class="fl">Revisor<span class="req">*</span></label>
              <select v-model="form.revisor_id" class="input">
                <option :value="null" disabled>Selecciona un revisor</option>
                <option v-for="r in catalogos.revisores" :key="r.id" :value="r.id">{{ nombreCompleto(r) }}</option>
              </select>
            </div>
            <div class="field">
              <label class="fl">Cuatrimestre<span class="req">*</span></label>
              <select v-model="form.cuatrimestre_id" class="input">
                <option :value="null" disabled>Selecciona un cuatrimestre</option>
                <option v-for="c in catalogos.cuatrimestres" :key="c.id" :value="c.id">
                  {{ c.nombre || `Cuatrimestre ${c.numero}` }}
                </option>
              </select>
            </div>
            <div v-if="catalogos.carreras.length" class="field">
              <label class="fl">Carrera<span class="req">*</span></label>
              <select v-model="form.carrera_id" class="input">
                <option :value="null" disabled>Selecciona una carrera</option>
                <option v-for="c in catalogos.carreras" :key="c.id" :value="c.id">{{ c.nombre }}</option>
              </select>
            </div>
          </div>
          <button class="btn btn-primary mt3" :disabled="guardando || !puedeGuardar" @click="crear">
            <Plus :size="16" style="margin-right:4px" /> {{ guardando ? 'Guardando…' : 'Asignar' }}
          </button>
        </div>
      </div>

      <!-- Tabla de asignaciones existentes -->
      <div class="card">
        <div class="cp" style="overflow-x:auto">
          <table class="tt">
            <thead>
              <tr>
                <th>Revisor</th>
                <th v-if="mostrarColumnaCarrera">Carrera</th>
                <th>Cuatrimestre</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="cargando">
                <td :colspan="mostrarColumnaCarrera ? 4 : 3" class="sz-sm" style="text-align:center;color:var(--text-300)">Cargando…</td>
              </tr>
              <tr v-else-if="asignaciones.length === 0">
                <td :colspan="mostrarColumnaCarrera ? 4 : 3" class="sz-sm" style="text-align:center;color:var(--text-300)">
                  Aún no hay asignaciones registradas.
                </td>
              </tr>
              <tr v-for="a in asignaciones" :key="a.id">
                <td>{{ nombreCompleto(a.revisor) }}</td>
                <td v-if="mostrarColumnaCarrera">{{ a.carrera?.nombre }}</td>
                <td>{{ a.cuatrimestre?.nombre || `Cuatrimestre ${a.cuatrimestre?.numero}` }}</td>
                <td><IconButton title="Eliminar" @click="eliminar(a)"><Trash2 :size="16" /></IconButton></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppShell>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { UserCheck, Plus, Trash2 } from 'lucide-vue-next'
import AppShell from '@/components/AppShell.vue'
import IconButton from '@/components/IconButton.vue'
import api from '@/services/api'

const cargando = ref(false)
const guardando = ref(false)
const errorMsg = ref('')
const successMsg = ref('')

const asignaciones = ref([])
const catalogos = reactive({ revisores: [], cuatrimestres: [], carreras: [] })

const form = reactive({ revisor_id: null, cuatrimestre_id: null, carrera_id: null })

// Admin ve el selector y la columna de carrera; Director/Secretario no
// (su carrera queda fija en el servidor).
const mostrarColumnaCarrera = computed(() => catalogos.carreras.length > 0)

const puedeGuardar = computed(() => {
  if (!form.revisor_id || !form.cuatrimestre_id) return false
  if (mostrarColumnaCarrera.value && !form.carrera_id) return false
  return true
})

onMounted(async () => {
  cargando.value = true
  try {
    const [{ data: cat }, { data: lista }] = await Promise.all([
      api.get('/revisor-asignaciones/catalogos'),
      api.get('/revisor-asignaciones'),
    ])
    Object.assign(catalogos, cat)
    asignaciones.value = lista
  } catch (e) {
    errorMsg.value = e.response?.data?.message || 'No se pudieron cargar las asignaciones.'
  } finally {
    cargando.value = false
  }
})

function nombreCompleto(u) {
  if (!u) return '—'
  return [u.nombre, u.apellido_paterno, u.apellido_materno].filter(Boolean).join(' ')
}

async function crear() {
  guardando.value = true
  errorMsg.value = ''
  successMsg.value = ''
  try {
    const { data } = await api.post('/revisor-asignaciones', {
      revisor_id: form.revisor_id,
      cuatrimestre_id: form.cuatrimestre_id,
      carrera_id: form.carrera_id,
    })
    asignaciones.value.push(data)
    successMsg.value = 'Asignación creada correctamente.'
    form.revisor_id = null
    form.cuatrimestre_id = null
    form.carrera_id = null
  } catch (e) {
    const errores = e.response?.data?.errors
    errorMsg.value = errores ? Object.values(errores).flat().join(' ') : (e.response?.data?.message || 'No se pudo crear la asignación.')
  } finally {
    guardando.value = false
  }
}

async function eliminar(asignacion) {
  if (!confirm('¿Eliminar esta asignación?')) return
  try {
    await api.delete(`/revisor-asignaciones/${asignacion.id}`)
    asignaciones.value = asignaciones.value.filter((a) => a.id !== asignacion.id)
  } catch (e) {
    errorMsg.value = e.response?.data?.message || 'No se pudo eliminar la asignación.'
  }
}
</script>

<style scoped>
.field-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--s4); }
.mt3 { margin-top: var(--s3); }
</style>
