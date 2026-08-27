<template>
  <Modal titulo="Secuencia didáctica" @close="$emit('close')">
    <div v-if="cargando" class="sz-sm" style="text-align:center;color:var(--text-300)">Cargando…</div>

    <template v-else-if="secuencia">
      <div v-if="mensajeError" class="alert a-danger mb4">{{ mensajeError }}</div>

      <div class="resumen-header mb3">
        <div>
          <h3 class="ht-sm">{{ secuencia.asignatura?.nombre }}</h3>
          <p class="sz-xs" style="color:var(--text-300)">{{ secuencia.especialidad?.nombre }} · {{ secuencia.periodo }}</p>
        </div>
        <span :class="['estado-badge', badgeEstado(secuencia.estado)]">{{ etiquetaEstado(secuencia.estado) }}</span>
      </div>

      <!-- ═══ PDF real de la secuencia (reemplaza al resumen textual) ═══ -->
      <div class="pdf-preview mb4">
        <div v-if="cargandoPdf" class="sz-sm" style="text-align:center;padding:40px 0;color:var(--text-300)">
          Cargando documento…
        </div>
        <iframe v-else-if="pdfUrl" :src="pdfUrl" class="pdf-frame" title="PDF de la secuencia"></iframe>
        <div v-else class="sz-sm" style="text-align:center;padding:40px 0;color:var(--text-300)">
          No se pudo cargar el PDF de la secuencia.
        </div>
      </div>

      <!-- ═══ Ya validada: solo mostrar el documento firmado ═══ -->
      <div v-if="secuencia.estado === 'validada'" class="alert a-success">
        Esta secuencia ya fue validada.
        <a v-if="secuencia.documento_validacion_url" :href="secuencia.documento_validacion_url" target="_blank"
          rel="noopener" style="font-weight:600;margin-left:4px">Ver documento firmado</a>
      </div>

      <!-- ═══ En proceso de validación ═══ -->
      <template v-else-if="secuencia.estado === 'en_proceso_validacion'">
        <div class="field mb3">
          <label class="fl">1. Descarga el formato de validación</label>
          <p class="sz-xs mb2" style="color:var(--text-300)">
            Es el PDF oficial (UTH-ACA-DC-F-PVSD/14) con los datos de esta secuencia ya prellenados
            <span v-if="secuencia.revisor_firma_digital">y la firma digital del revisor ya estampada.</span>
          </p>
          <button class="btn btn-outline btn-sm" :disabled="descargando" @click="descargarFormato">
            <Download :size="14" style="margin-right:4px" /> {{ descargando ? 'Descargando…' : 'Descargar formato' }}
          </button>
        </div>

        <div v-if="secuencia.revisor_firma_digital" class="alert" style="margin-bottom:var(--s3)">
          <strong>{{ secuencia.revisor_validacion?.nombre_completo || 'El revisor' }}</strong> ya firmó
          digitalmente este documento como PTC al enviarlo a validación.
        </div>

        <!-- El Director firma digitalmente su propia sección del PDF,
             independiente de la firma del PTC/revisor. -->
        <div class="field mb3">
          <label class="fl">2. Firma digitalmente como Director (opcional)</label>
          <p class="sz-xs mb2" style="color:var(--text-300)">
            Dibuja tu firma para estamparla en la sección "Firma del director de carrera" del documento final.
          </p>
          <FirmaDigitalPad ref="firmaPadRef" v-model="firmaDigitalDirector" :disabled="!!archivoFirmado" />
        </div>

        <!-- Alternativa: subir el documento ya firmado a mano/escaneado.
             Si se sube un archivo, gana sobre cualquier firma digital. -->
        <div class="field mb3">
          <label class="fl">O sube el documento ya firmado</label>
          <p class="sz-xs mb2" style="color:var(--text-300)">
            Imprime, firma y escanea (o firma en PDF) el formato descargado, y súbelo aquí en vez de firmar arriba.
          </p>

          <div class="file-upload-3d" :class="{ 'has-file': archivoFirmado }">
            <input type="file" id="doc-validacion-upload" accept=".pdf,.jpg,.jpeg,.png" class="hidden-input"
              @change="onArchivoSeleccionado" />
            <label for="doc-validacion-upload" class="file-label">
              <div class="file-icon-wrap">
                <UploadCloud v-if="!archivoFirmado" :size="24" />
                <FileCheck v-else :size="24" color="#00B64F" />
              </div>
              <div class="file-text">
                <span class="file-title">{{ archivoFirmado ? archivoFirmado.name : 'Subir documento firmado' }}</span>
                <span class="file-desc">{{ archivoFirmado ? 'Clic para reemplazar' : 'PDF, JPG o PNG · máx. 10MB' }}</span>
              </div>
            </label>
          </div>
        </div>
      </template>

      <div v-if="secuencia.estado === 'en_proceso_validacion'" class="field mt3">
        <label class="fl">Comentario (opcional, para el docente)</label>
        <textarea v-model="comentario" class="input" rows="2" placeholder="Motivo de rechazo o notas…"></textarea>
      </div>
    </template>

    <template #footer>
      <button class="btn btn-ghost" @click="$emit('close')">Cerrar</button>
      <template v-if="secuencia?.estado === 'en_proceso_validacion'">
        <button class="btn btn-danger" :disabled="procesando" @click="rechazar">Rechazar</button>
        <button class="btn btn-primary" :disabled="procesando || !puedeValidar" @click="validar">
          {{ procesando ? 'Guardando…' : 'Validar' }}
        </button>
      </template>
    </template>
  </Modal>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { Download, UploadCloud, FileCheck } from 'lucide-vue-next'
import Modal from '@/components/Modal.vue'
import FirmaDigitalPad from '@/components/FirmaDigitalPad.vue'
import api from '@/services/api'

const props = defineProps({ secuenciaId: { type: [Number, String], required: true } })
const emit = defineEmits(['close', 'resuelta'])

const cargando = ref(true)
const secuencia = ref(null)
const comentario = ref('')
const procesando = ref(false)
const descargando = ref(false)
const mensajeError = ref('')

const cargandoPdf = ref(true)
const pdfUrl = ref(null)
const archivoFirmado = ref(null)
const firmaDigitalDirector = ref(null)
const firmaPadRef = ref(null)

// Se puede validar si: el revisor ya firmó digital como PTC (el Director
// solo confirma), o el Director firma digitalmente ahora, o se sube un
// documento ya firmado.
const puedeValidar = computed(() => {
  if (archivoFirmado.value) return true
  if (firmaDigitalDirector.value) return true
  return !!secuencia.value?.revisor_firma_digital
})

onMounted(async () => {
  try {
    const { data } = await api.get(`/director/secuencias/${props.secuenciaId}/resumen`)
    secuencia.value = data
    cargarPdf()
  } catch (e) {
    mensajeError.value = e.response?.data?.message || 'No se pudo cargar la secuencia.'
  } finally {
    cargando.value = false
  }
})

onBeforeUnmount(() => {
  if (pdfUrl.value) window.URL.revokeObjectURL(pdfUrl.value)
})

async function cargarPdf() {
  cargandoPdf.value = true
  try {
    const { data } = await api.get(`/secuencias/${props.secuenciaId}/documento-planeacion`, {
      responseType: 'blob',
    })
    pdfUrl.value = window.URL.createObjectURL(new Blob([data], { type: 'application/pdf' }))
  } catch (e) {
    pdfUrl.value = null
  } finally {
    cargandoPdf.value = false
  }
}

function onArchivoSeleccionado(evento) {
  archivoFirmado.value = evento.target.files[0] || null
  // Un archivo subido siempre gana sobre la firma digital dibujada: no
  // tiene sentido combinarlos.
  if (archivoFirmado.value) {
    firmaDigitalDirector.value = null
    firmaPadRef.value?.limpiar?.()
  }
}

async function descargarFormato() {
  descargando.value = true
  mensajeError.value = ''
  try {
    const { data } = await api.get(`/director/secuencias/${props.secuenciaId}/formato-validacion`, {
      responseType: 'blob',
    })
    const url = window.URL.createObjectURL(new Blob([data], { type: 'application/pdf' }))
    const enlace = document.createElement('a')
    enlace.href = url
    enlace.download = `validacion-${secuencia.value.asignatura?.nombre || 'secuencia'}.pdf`
    document.body.appendChild(enlace)
    enlace.click()
    enlace.remove()
    window.URL.revokeObjectURL(url)
  } catch (e) {
    mensajeError.value = 'No se pudo descargar el formato de validación.'
  } finally {
    descargando.value = false
  }
}

async function validar() {
  procesando.value = true
  mensajeError.value = ''
  try {
    const fd = new FormData()
    if (archivoFirmado.value) {
      // El archivo subido gana sobre cualquier firma digital.
      fd.append('documento', archivoFirmado.value)
    } else if (firmaDigitalDirector.value) {
      fd.append('firma_digital', firmaDigitalDirector.value)
    }
    if (comentario.value) fd.append('comentario', comentario.value)

    await api.post(`/director/secuencias/${props.secuenciaId}/validar`, fd, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    emit('resuelta', 'La secuencia fue validada.')
  } catch (e) {
    mensajeError.value = e.response?.data?.message || 'No se pudo validar.'
  } finally {
    procesando.value = false
  }
}

async function rechazar() {
  procesando.value = true
  mensajeError.value = ''
  try {
    await api.post(`/director/secuencias/${props.secuenciaId}/rechazar`, { comentario: comentario.value || null })
    emit('resuelta', 'La secuencia fue rechazada.')
  } catch (e) {
    mensajeError.value = e.response?.data?.message || 'No se pudo rechazar.'
  } finally {
    procesando.value = false
  }
}

function badgeEstado(estado) {
  return { borrador: 'estado-En_desarrollo', en_revision: 'estado-En_revision', en_proceso_validacion: 'estado-En_proceso_validacion', validada: 'estado-Validada', rechazada: 'estado-Rechazada' }[estado] ?? 'estado-En_desarrollo'
}

function etiquetaEstado(estado) {
  return {
    borrador: 'Borrador', en_revision: 'En revisión', en_proceso_validacion: 'En validación',
    validada: 'Validada', rechazada: 'Rechazada',
  }[estado] || estado
}
</script>

<style scoped>
.resumen-header { display: flex; justify-content: space-between; align-items: center; }
.mb3 { margin-bottom: var(--s3); }
.mt3 { margin-top: var(--s3); }
.mb2 { margin-bottom: var(--s2, 8px); }
.pdf-frame { width: 100%; height: 420px; border: 1px solid var(--border); border-radius: var(--r-md); }

.file-upload-3d {
  position: relative;
  border: 2px dashed var(--border);
  border-radius: var(--r-md);
  background: #FFFFFF;
  transition: all 0.2s;
}
.file-upload-3d:hover { border-color: var(--uth-verde-claro); background: rgba(0, 182, 79, 0.02); }
.file-upload-3d.has-file { border-style: solid; border-color: rgba(0, 182, 79, 0.3); background: rgba(0, 182, 79, 0.05); }
.hidden-input { position: absolute; width: 0; height: 0; opacity: 0; }
.file-label { display: flex; align-items: center; gap: 16px; padding: 16px; cursor: pointer; width: 100%; }
.file-icon-wrap {
  width: 40px; height: 40px; background: var(--bg-soft); border-radius: 10px;
  display: flex; align-items: center; justify-content: center; color: var(--text-500);
}
.has-file .file-icon-wrap { background: #FFFFFF; box-shadow: 0 4px 10px rgba(0, 182, 79, 0.15); }
.file-text { display: flex; flex-direction: column; }
.file-title { font-weight: 800; font-size: 14px; color: var(--text-900); }
.file-desc { font-size: 12px; color: var(--text-400); }
.has-file .file-title { color: var(--uth-verde); }
</style>
