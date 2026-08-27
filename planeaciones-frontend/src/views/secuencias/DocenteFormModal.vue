<template>
  <Modal :titulo="docente ? 'Editar docente' : 'Nuevo docente'" @close="$emit('close')">
    <div v-if="erroresForm.length" class="alert a-danger mb4">
      <div v-for="(e, i) in erroresForm" :key="i">{{ e }}</div>
    </div>

    <div v-if="!docente" class="alert a-info mb4">
      Se generará una contraseña temporal y se enviará por correo junto con un enlace de confirmación.
    </div>

    <div class="field-row">
      <div class="field">
        <label class="fl">Nombre(s)<span class="req">*</span></label>
        <input v-model.trim="form.nombre" type="text" class="input" placeholder="Ej. María" />
      </div>
      <div class="field">
        <label class="fl">Apellido paterno<span class="req">*</span></label>
        <input v-model.trim="form.apellido_paterno" type="text" class="input" placeholder="Ej. López" />
      </div>
    </div>

    <div class="field">
      <label class="fl">Apellido materno</label>
      <input v-model.trim="form.apellido_materno" type="text" class="input" placeholder="Ej. García" />
    </div>

    <div class="field">
      <label class="fl">Correo<span class="req">*</span></label>
      <input v-model.trim="form.email" type="email" class="input" placeholder="usuario@uth.edu.mx" />
    </div>

    <div class="field">
      <label class="fl">Materias que puede impartir</label>
      <div class="checklist">
        <label v-for="a in catalogos.asignaturas" :key="a.id" class="checklist-item">
          <input type="checkbox" :value="a.id" v-model="form.asignatura_ids" />
          <span>{{ a.nombre }} <span class="sz-xs" style="color:var(--text-300)">({{ a.clave }})</span></span>
        </label>
        <p v-if="catalogos.asignaturas.length === 0" class="sz-sm" style="color:var(--text-300)">
          Tu carrera aún no tiene asignaturas registradas.
        </p>
      </div>
    </div>

    <template #footer>
      <button class="btn btn-ghost" @click="$emit('close')">Cancelar</button>
      <button class="btn btn-primary" :disabled="guardando" @click="guardar">
        {{ guardando ? 'Guardando…' : 'Guardar' }}
      </button>
    </template>
  </Modal>
</template>

<script setup>
import { reactive, ref } from 'vue'
import Modal from '@/components/Modal.vue'
import api from '@/services/api'

const props = defineProps({
  docente: { type: Object, default: null },
  catalogos: { type: Object, required: true },
})
const emit = defineEmits(['close', 'guardado'])

const form = reactive({
  nombre: props.docente?.nombre ?? '',
  apellido_paterno: props.docente?.apellido_paterno ?? '',
  apellido_materno: props.docente?.apellido_materno ?? '',
  email: props.docente?.email ?? '',
  asignatura_ids: props.docente?.asignaturas?.map((a) => a.id) ?? [],
})

const guardando = ref(false)
const erroresForm = ref([])

async function guardar() {
  guardando.value = true
  erroresForm.value = []
  try {
    const payload = {
      nombre: form.nombre,
      apellido_paterno: form.apellido_paterno,
      apellido_materno: form.apellido_materno || null,
      email: form.email,
      asignatura_ids: form.asignatura_ids,
    }

    if (props.docente) {
      await api.put(`/carrera/docentes/${props.docente.id}`, payload)
      emit('guardado', 'Docente actualizado correctamente.')
    } else {
      await api.post('/carrera/docentes', payload)
      emit('guardado', 'Se creó el docente y se envió un correo con sus credenciales.')
    }
  } catch (e) {
    const errores = e.response?.data?.errors
    erroresForm.value = errores ? Object.values(errores).flat() : [e.response?.data?.message || 'No se pudo guardar.']
  } finally {
    guardando.value = false
  }
}
</script>

<style scoped>
.field-row { display: grid; grid-template-columns: 1fr 1fr; gap: var(--s4); }
.checklist { max-height: 180px; overflow-y: auto; border: 1px solid var(--border); border-radius: var(--r-sm); padding: var(--s3); }
.checklist-item { display: flex; align-items: center; gap: var(--s2); padding: var(--s2) 0; font-size: var(--p-sm); cursor: pointer; }
</style>
