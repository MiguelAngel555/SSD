<template>
  <AppShell>
    <div class="sec">
      <div class="sec-hdr">
        <div class="sec-num"><Users :size="20" /></div>
        <div>
          <h2>Docentes de mi carrera</h2>
          <p>Alta y edición de docentes. Solo puedes asignarles materias de tu propia carrera.</p>
        </div>
      </div>

      <div class="card mb4">
        <div class="cp flex jb ic fw g3u">
          <input v-model.trim="filtros.q" type="text" class="input" placeholder="Buscar por nombre o correo…"
            style="max-width: 260px" @input="buscarConDebounce" />
          <button class="btn btn-primary" @click="abrirCrear">
            <Plus :size="16" style="margin-right:4px" /> Nuevo docente
          </button>
        </div>
      </div>

      <div v-if="errorMsg" class="alert a-danger mb4">{{ errorMsg }}</div>
      <div v-if="successMsg" class="alert a-success mb4">{{ successMsg }}</div>

      <div class="card">
        <div class="cp" style="overflow-x:auto">
          <table class="tt">
            <thead>
              <tr><th>Nombre</th><th>Correo</th><th>Materias</th><th></th></tr>
            </thead>
            <tbody>
              <tr v-if="cargando"><td colspan="4" class="sz-sm" style="text-align:center;color:var(--text-300)">Cargando…</td></tr>
              <tr v-else-if="docentes.length === 0"><td colspan="4" class="sz-sm" style="text-align:center;color:var(--text-300)">No hay docentes registrados.</td></tr>
              <tr v-for="d in docentes" :key="d.id">
                <td>{{ [d.nombre, d.apellido_paterno, d.apellido_materno].filter(Boolean).join(' ') }}</td>
                <td>{{ d.email }}</td>
                <td>{{ d.asignaturas.map((a) => a.nombre).join(', ') || '—' }}</td>
                <td><IconButton title="Editar" @click="abrirEditar(d)"><Pencil :size="16" /></IconButton></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <DocenteFormModal v-if="modalAbierto" :docente="docenteEnEdicion" :catalogos="catalogos" @close="modalAbierto = false"
      @guardado="onGuardado" />
  </AppShell>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import { Users, Plus, Pencil } from 'lucide-vue-next'
import AppShell from '@/components/AppShell.vue'
import IconButton from '@/components/IconButton.vue'
import DocenteFormModal from './DocenteFormModal.vue'
import api from '@/services/api'

const docentes = ref([])
const catalogos = reactive({ asignaturas: [] })
const cargando = ref(false)
const errorMsg = ref('')
const successMsg = ref('')
const filtros = reactive({ q: '' })

const modalAbierto = ref(false)
const docenteEnEdicion = ref(null)

let debounce = null

onMounted(async () => {
  try {
    const { data } = await api.get('/carrera/docentes/catalogos')
    Object.assign(catalogos, data)
  } catch (e) {
    errorMsg.value = 'No se pudieron cargar los catálogos.'
  }
  cargar()
})

function buscarConDebounce() {
  clearTimeout(debounce)
  debounce = setTimeout(() => cargar(), 350)
}

async function cargar() {
  cargando.value = true
  try {
    const { data } = await api.get('/carrera/docentes', { params: { q: filtros.q || undefined } })
    docentes.value = data.data ?? data
  } catch (e) {
    errorMsg.value = e.response?.data?.message || 'No se pudieron cargar los docentes.'
  } finally {
    cargando.value = false
  }
}

function abrirCrear() {
  docenteEnEdicion.value = null
  modalAbierto.value = true
}

function abrirEditar(docente) {
  docenteEnEdicion.value = docente
  modalAbierto.value = true
}

function onGuardado(mensaje) {
  modalAbierto.value = false
  successMsg.value = mensaje
  cargar()
}
</script>
