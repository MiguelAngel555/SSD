<template>
  <Modal titulo="Enviar a validación" @close="$emit('close')">
    <div v-if="mensajeError" class="alert a-danger mb4">{{ mensajeError }}</div>

    <p class="sz-sm mb3">
      Esta secuencia pasará a la bandeja del Director Académico para su validación final. Si firmas digitalmente
      ahora, esa firma quedará como la <strong>firma del PTC que valida</strong> en el documento oficial, y el
      Director solo tendrá que confirmar (ya no podrá firmar en tu lugar).
    </p>

    <div class="field mb3">
      <label class="fl">¿Deseas firmar digitalmente ahora? (opcional)</label>
      <FirmaDigitalPad ref="firmaPadRef" v-model="firmaDigital" />
    </div>

    <p class="sz-xs" style="color:var(--text-300)">
      Si prefieres no firmar aquí, el Director deberá subir el documento ya firmado a mano/escaneado.
    </p>

    <template #footer>
      <button class="btn btn-ghost" @click="$emit('close')">Cancelar</button>
      <button class="btn btn-primary" :disabled="enviando" @click="enviar">
        {{ enviando ? 'Enviando…' : 'Enviar al Director' }}
      </button>
    </template>
  </Modal>
</template>

<script setup>
import { ref } from 'vue'
import Modal from '@/components/Modal.vue'
import FirmaDigitalPad from '@/components/FirmaDigitalPad.vue'
import api from '@/services/api'

const props = defineProps({ secuenciaId: { type: [Number, String], required: true } })
const emit = defineEmits(['close', 'enviada'])

const firmaDigital = ref(null)
const firmaPadRef = ref(null)
const enviando = ref(false)
const mensajeError = ref('')

async function enviar() {
  enviando.value = true
  mensajeError.value = ''
  try {
    const { data } = await api.post(`/revisor/secuencias/${props.secuenciaId}/enviar-validacion`, {
      firma_digital: firmaDigital.value || undefined,
    })
    emit('enviada', data)
  } catch (e) {
    mensajeError.value = e.response?.data?.message || 'No se pudo enviar la secuencia a validación.'
  } finally {
    enviando.value = false
  }
}
</script>

<style scoped>
.mb3 { margin-bottom: var(--s3); }
</style>
