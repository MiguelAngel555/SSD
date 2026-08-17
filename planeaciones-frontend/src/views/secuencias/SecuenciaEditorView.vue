<template>
  <div v-if="cargando" class="editor-loading">Cargando secuencia…</div>

  <div v-else-if="secuencia" class="wrap">
    <!-- ═══ SIDEBAR (mismas clases que el resto de la app) ═══ -->
    <aside class="sidebar">
      <div class="sb-brand">
        <div class="logo-cube">UTH</div>
        <div>
          <h1>Planeaciones</h1>
          <p>{{ secuencia.asignatura?.nombre }}</p>
        </div>
      </div>

      <div class="nav-sec">
        <button class="nav-a" style="width:100%;border:none;background:none;cursor:pointer;text-align:left"
          @click="router.back()">
          <ArrowLeft :size="15" /> <span>Volver</span>
        </button>
      </div>

      <div class="nav-sec">
        <div class="nav-lbl">Documento</div>
        <button class="nav-a" style="width:100%;border:none;background:none;cursor:pointer;text-align:left"
          :class="{ active: seccion === 'caratula' }" @click="seccion = 'caratula'">
          <FileText :size="15" /> <span>Carátula</span>
        </button>
      </div>

      <div v-if="secuencia.unidades.length" class="nav-sec">
        <div class="nav-lbl">Unidades de Aprendizaje</div>
        <div v-for="(u, i) in secuencia.unidades" :key="u.id">
          <button class="nav-a" style="width:100%;border:none;background:none;cursor:pointer;text-align:left"
            @click="toggleGrupo(u.id)">
            <span class="num-badge">{{ i + 1 }}</span>
            <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;flex:1">{{ u.nombre || `Unidad ${i +
              1}` }}</span>
            <ChevronRight :size="13" class="nav-chevron" :class="{ open: gruposAbiertos[u.id] }" />
          </button>
          <template v-if="gruposAbiertos[u.id]">
            <button class="nav-a nav-a-child"
              style="width:100%;border:none;background:none;cursor:pointer;text-align:left"
              :class="{ active: seccion === `unidad-${u.id}` }" @click="seccion = `unidad-${u.id}`">
              <Info :size="12" /> B. Info unidad
            </button>
            <button class="nav-a nav-a-child"
              style="width:100%;border:none;background:none;cursor:pointer;text-align:left"
              :class="{ active: seccion === `unidad-${u.id}-evaluacion` }"
              @click="seccion = `unidad-${u.id}-evaluacion`">
              <ClipboardList :size="12" /> C. Evaluación
            </button>
            <button class="nav-a nav-a-child"
              style="width:100%;border:none;background:none;cursor:pointer;text-align:left"
              :class="{ active: seccion === `unidad-${u.id}-secuencia` }" @click="seccion = `unidad-${u.id}-secuencia`">
              <Layers :size="12" /> D. Secuencia
            </button>
          </template>
        </div>

      </div>

      <div class="nav-sec">
        <div class="nav-lbl">Referencias</div>
        <button class="nav-a" style="width:100%;border:none;background:none;cursor:pointer;text-align:left"
          :class="{ active: seccion === 'bibliografia' }" @click="seccion = 'bibliografia'">
          <Library :size="15" /> <span>Bibliografía</span>
        </button>
        <button class="nav-a" style="width:100%;border:none;background:none;cursor:pointer;text-align:left"
          :class="{ active: seccion === 'finalizar' }" @click="seccion = 'finalizar'">
          <CheckCheck :size="15" /> <span>Finalizar</span>
        </button>
      </div>
    </aside>

    <!-- ═══ CONTENIDO ═══ -->
    <div class="sd-content">

      <!-- ═══ Indicador flotante de autoguardado ═══ -->
      <transition name="fade-guardado">
        <div v-if="estadoGuardado" class="badge-autoguardado" :class="`ag-${estadoGuardado}`">
          <Loader2 v-if="estadoGuardado === 'guardando'" :size="13" class="spin" />
          <CheckCircle2 v-else-if="estadoGuardado === 'guardado'" :size="13" />
          <XCircle v-else :size="13" />
          <span v-if="estadoGuardado === 'guardando'">Guardando…</span>
          <span v-else-if="estadoGuardado === 'guardado'">Guardado</span>
          <span v-else>Error al guardar</span>
        </div>
      </transition>

      <!-- ═══ A. CARÁTULA ═══ -->
      <div v-show="seccion === 'caratula'" class="doc-wrap fade-in">
        <DocHeader :subtitulo="secuencia.asignatura?.nombre || ''" />
        <div class="doc-section-title">
          <span>A.— Carátula</span>
          <div class="flex ic g2u">
            <span :class="['estado-badge', badgeEstadoDoc(secuencia.estado)]">{{ etiquetaEstado(secuencia.estado)
              }}</span>
            <button v-if="esAutor" class="btn btn-outline btn-sm" @click="modalGruposAbierto = true">
              <Pencil :size="13" style="margin-right:4px" /> Editar grupos y coautores
            </button>
          </div>
        </div>
        <p class="hint-autoguardado">Los cambios en los campos se guardan automáticamente al salir del campo (no es
          necesario un botón de guardar).</p>

        <table class="doc-table" style="margin: 1rem 1.2rem; width: calc(100% - 2.4rem);">
          <tr>
            <td class="lbl">Programa educativo
              <InfoTooltip :texto="INSTRUCCIONES.programaEducativo" />
            </td>
            <td class="val"><input class="eval-input" v-model="caratula.programa_educativo" :disabled="!editable"
                @blur="guardarCaratula('programa_educativo')" /></td>
            <td class="lbl">Docente(s)
              <InfoTooltip :texto="INSTRUCCIONES.docentes" />
            </td>
            <td class="val">{{secuencia.autores.map(a => a.nombre_completo).join(', ') || '—'}}</td>
          </tr>
          <tr>
            <td class="lbl">Cuatrimestre</td>
            <td class="val">{{ secuencia.asignatura?.cuatrimestre?.numero ?? '—' }}</td>
            <td class="lbl">Periodo escolar</td>
            <td class="val">{{ secuencia.periodo }}</td>
          </tr>
          <tr>
            <td class="lbl">Nombre de la asignatura</td>
            <td class="val">{{ secuencia.asignatura?.nombre }}</td>
            <td class="lbl">Grupo(s)
              <InfoTooltip :texto="INSTRUCCIONES.grupos" />
            </td>
            <td class="val">{{secuencia.grupos.map(g => g.grupo).join(', ') || '—'}}</td>
          </tr>
        </table>

        <table class="doc-table" style="margin: 0 1.2rem 1rem; width: calc(100% - 2.4rem);">
          <tr>
            <td class="lbl" style="width:28%">Propósito de la asignatura
              <InfoTooltip :texto="INSTRUCCIONES.propositoAsignatura" />
            </td>
            <td class="val tall"><textarea class="eval-input" rows="3" v-model="caratula.proposito_aprendizaje"
                :disabled="!editable" @blur="guardarCaratula('proposito_aprendizaje')"></textarea></td>
          </tr>
        </table>

        <table class="doc-table" style="margin: 0 1.2rem 1rem; width: calc(100% - 2.4rem);">
          <tr>
            <td class="lbl" style="width:28%">Competencia a la que contribuye
              <InfoTooltip :texto="INSTRUCCIONES.competencia" />
            </td>
            <td class="val tall"><textarea class="eval-input" rows="3" v-model="caratula.competencia"
                :disabled="!editable" @blur="guardarCaratula('competencia')"></textarea></td>
          </tr>
        </table>

        <table class="doc-table" style="margin: 0 1.2rem 1rem; width: calc(100% - 2.4rem);">
          <tr>
            <td class="lbl">Tipo de competencia
              <InfoTooltip :texto="INSTRUCCIONES.tipoCompetencia" />
            </td>
            <td class="val"><input class="eval-input" v-model="caratula.tipo_competencia" :disabled="!editable"
                @blur="guardarCaratula('tipo_competencia')" /></td>
            <td class="lbl">Créditos
              <InfoTooltip :texto="INSTRUCCIONES.creditos" />
            </td>
            <td class="val"><input class="eval-input" type="number" v-model.number="caratula.creditos"
                :disabled="!editable" @blur="guardarCaratula('creditos')" /></td>
            <td class="lbl">Modalidad
              <InfoTooltip :texto="INSTRUCCIONES.modalidad" />
            </td>
            <td class="val"><input class="eval-input" v-model="caratula.modalidad" :disabled="!editable"
                @blur="guardarCaratula('modalidad')" /></td>
          </tr>
        </table>

        <table class="doc-table" style="margin: 0 1.2rem 1rem; width: calc(100% - 2.4rem); text-align:center">
          <tr>
            <td class="lbl lbl-hrs">Horas del saber</td>
            <td class="lbl lbl-hrs">Horas del saber hacer</td>
            <td class="lbl lbl-hrs">Horas totales</td>
            <td class="lbl lbl-hrs">Horas por semana
              <InfoTooltip :texto="INSTRUCCIONES.horas" />
            </td>
          </tr>
          <tr>
            <td class="val val-hrs"><input class="eval-input" style="text-align:center" type="number"
                v-model.number="caratula.horas_saber" :disabled="!editable" @blur="guardarCaratula('horas_saber')" />
            </td>
            <td class="val val-hrs"><input class="eval-input" style="text-align:center" type="number"
                v-model.number="caratula.horas_saber_hacer" :disabled="!editable"
                @blur="guardarCaratula('horas_saber_hacer')" /></td>
            <td class="val val-hrs"><input class="eval-input" style="text-align:center" type="number"
                v-model.number="caratula.horas_totales" :disabled="!editable"
                @blur="guardarCaratula('horas_totales')" />
            </td>
            <td class="val val-hrs"><input class="eval-input" style="text-align:center" type="number"
                v-model.number="caratula.horas_semana" :disabled="!editable" @blur="guardarCaratula('horas_semana')" />
            </td>
          </tr>
        </table>
        <DocFooter :pagina="paginaDe('caratula')" :total-paginas="totalPaginasDoc" />
      </div>

      <!-- ═══ POR UNIDAD ═══ -->
      <template v-for="(unidad, i) in secuencia.unidades" :key="unidad.id">

        <!-- B. Info -->
        <div v-show="seccion === `unidad-${unidad.id}`" class="doc-wrap fade-in">
          <DocHeader :subtitulo="`B.— Información de la Unidad ${i + 1}`" />

          <div class="doc-section-title"><span>B.— Información de la Unidad de Aprendizaje {{ i + 1 }}</span></div>
          <ValidacionElemento v-if="unidad.revision || puedeValidarElementos" variante="barra" tipo="unidad"
            :elemento-id="unidad.id" :revision="unidad.revision" :puede-validar="puedeValidarElementos"
            @actualizado="(r) => unidad.revision = r" />

          <table class="doc-table" style="margin: 1rem 1.2rem 0; width: calc(100% - 2.4rem);">
            <tr>
              <td class="lbl" style="width:28%">Nombre de la unidad
                <InfoTooltip :texto="INSTRUCCIONES.nombreUnidad" />
              </td>
              <td class="val"><input class="eval-input" v-model="unidad.nombre" :disabled="!puedeEditarUnidad(unidad)"
                  @blur="guardarUnidad(unidad, 'nombre')" /></td>
            </tr>
            <tr>
              <td class="lbl">Propósito esperado
                <InfoTooltip :texto="INSTRUCCIONES.propositoEsperado" />
              </td>
              <td class="val tall"><textarea class="eval-input" rows="3" v-model="unidad.proposito_esperado"
                  :disabled="!puedeEditarUnidad(unidad)" @blur="guardarUnidad(unidad, 'proposito_esperado')"></textarea>
              </td>
            </tr>
          </table>

          <table class="doc-table" style="margin: 0 1.2rem 1rem; width: calc(100% - 2.4rem); text-align:center">
            <tr>
              <td class="lbl">Horas saber</td>
              <td class="lbl">Horas saber hacer</td>
              <td class="lbl">Horas totales</td>
              <td class="lbl">% de la unidad
                <InfoTooltip :texto="INSTRUCCIONES.porcentajeUnidad" />
              </td>
            </tr>
            <tr>
              <td class="val"><input class="eval-input" style="text-align:center" type="number"
                  v-model.number="unidad.horas_saber" :disabled="!puedeEditarUnidad(unidad)"
                  @blur="guardarUnidad(unidad, 'horas_saber')" /></td>
              <td class="val"><input class="eval-input" style="text-align:center" type="number"
                  v-model.number="unidad.horas_saber_hacer" :disabled="!puedeEditarUnidad(unidad)"
                  @blur="guardarUnidad(unidad, 'horas_saber_hacer')" /></td>
              <td class="val"><input class="eval-input" style="text-align:center" type="number"
                  v-model.number="unidad.horas_totales" :disabled="!puedeEditarUnidad(unidad)"
                  @blur="guardarUnidad(unidad, 'horas_totales')" /></td>
              <td class="val"><input class="eval-input" style="text-align:center" type="number"
                  v-model.number="unidad.porcentaje_unidad" :disabled="!puedeEditarUnidad(unidad)"
                  @blur="guardarUnidad(unidad, 'porcentaje_unidad')" /></td>
            </tr>
          </table>

          <div class="doc-section-title" style="font-size:11.5px">
            <span>Temas de la Unidad
              <InfoTooltip :texto="INSTRUCCIONES.tema" />
            </span>
            <button v-if="puedeEditarUnidad(unidad)" class="btn btn-primary btn-sm"
              :disabled="!!agregandoTema[unidad.id]" @click="agregarTema(unidad)">
              <Loader2 v-if="agregandoTema[unidad.id]" :size="13" class="spin" style="margin-right:4px" />
              <Plus v-else :size="13" style="margin-right:4px" />
              {{ agregandoTema[unidad.id] ? 'Agregando…' : 'Añadir tema' }}
            </button>
          </div>

          <div style="overflow-x:auto; margin: 0 1.2rem 1.5rem">
            <table class="doc-table" style="width:100%; margin-bottom:0">
              <thead>
                <tr>
                  <td class="lbl" style="text-align:center">Temas</td>
                  <td class="lbl" style="text-align:center">Saber
                    <InfoTooltip :texto="INSTRUCCIONES.saberConceptual" />
                  </td>
                  <td class="lbl" style="text-align:center">Saber Hacer
                    <InfoTooltip :texto="INSTRUCCIONES.saberHacer" />
                  </td>
                  <td class="lbl" style="text-align:center">Saber Ser-convivir
                    <InfoTooltip :texto="INSTRUCCIONES.saberSerConvivir" />
                  </td>
                  <td class="lbl" style="width:110px;text-align:center">Estado</td>
                  <td class="lbl" style="width:36px"></td>
                </tr>
              </thead>
              <tbody>
                <tr v-if="unidad.temas.length === 0">
                  <td colspan="6" class="val" style="text-align:center;color:#bbb;font-style:italic">Sin temas
                    registrados.</td>
                </tr>
                <tr v-for="t in unidad.temas" :key="t.id">
                  <td class="val"><textarea class="tema-cell" v-model="t.tema" :disabled="!puedeEditarUnidad(unidad)"
                      @blur="guardarTema(t, 'tema')"></textarea></td>
                  <td class="val"><textarea class="tema-cell" v-model="t.saber" :disabled="!puedeEditarUnidad(unidad)"
                      @blur="guardarTema(t, 'saber')"></textarea></td>
                  <td class="val"><textarea class="tema-cell" v-model="t.saber_hacer"
                      :disabled="!puedeEditarUnidad(unidad)" @blur="guardarTema(t, 'saber_hacer')"></textarea></td>
                  <td class="val"><textarea class="tema-cell" v-model="t.ser_convivir"
                      :disabled="!puedeEditarUnidad(unidad)" @blur="guardarTema(t, 'ser_convivir')"></textarea></td>
                  <ValidacionElemento variante="fila" tipo="tema" :elemento-id="t.id" :revision="t.revision"
                    :puede-validar="puedeValidarElementos" @actualizado="(r) => t.revision = r" />
                  <td style="text-align:center;vertical-align:middle;border:1.5px solid #555;background:#fff8f8">
                    <IconButton v-if="puedeEditarUnidad(unidad)" title="Eliminar" variant="danger"
                      @click="eliminarTema(unidad, t)">
                      <Trash2 :size="13" />
                    </IconButton>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <DocFooter :pagina="paginaDe('info', i)" :total-paginas="totalPaginasDoc" />
        </div>

        <!-- C. Evaluación -->
        <div v-show="seccion === `unidad-${unidad.id}-evaluacion`" class="doc-wrap fade-in">
          <DocHeader :subtitulo="`C.— Sistema de Evaluación — Unidad ${i + 1}`" />

          <div class="doc-section-title">
            <span>C.— Sistema de Evaluación por Unidad de Aprendizaje</span>
            <span :class="['pond-badge', sumaPonderacion(unidad) === 100 ? 'pond-ok' : 'pond-warn']">Σ {{
              sumaPonderacion(unidad) }}%</span>
          </div>
          <ValidacionElemento v-if="unidad.evaluacion.revision || puedeValidarElementos" variante="barra"
            tipo="evaluacion" :elemento-id="unidad.evaluacion.id" :revision="unidad.evaluacion.revision"
            :puede-validar="puedeValidarElementos" @actualizado="(r) => unidad.evaluacion.revision = r" />

          <table class="doc-table" style="margin: 1rem 1.2rem 0; width: calc(100% - 2.4rem);">
            <tr>
              <td class="lbl" style="width:28%">Periodo en semanas
                <InfoTooltip :texto="INSTRUCCIONES.periodoSemanas" />
              </td>
              <td class="val"><input class="eval-input" style="width:80px" type="number" min="1" max="15"
                  v-model.number="unidad.evaluacion.periodo_semanas" :disabled="!puedeEditarUnidad(unidad)"
                  @blur="guardarEvaluacion(unidad)" /></td>
            </tr>
            <tr>
              <td class="lbl">Resultado de aprendizaje
                <InfoTooltip :texto="INSTRUCCIONES.resultadoAprendizaje" />
              </td>
              <td class="val tall"><textarea class="eval-input" rows="3"
                  v-model="unidad.evaluacion.resultado_aprendizaje" :disabled="!puedeEditarUnidad(unidad)"
                  @blur="guardarEvaluacion(unidad)"></textarea></td>
            </tr>
          </table>

          <div class="doc-section-title" style="font-size:11.5px">
            <span>Evidencias de aprendizaje</span>
            <button v-if="puedeEditarUnidad(unidad)" class="btn btn-primary btn-sm"
              :disabled="!!agregandoEvidencia[unidad.id]" @click="agregarEvidencia(unidad)">
              <Loader2 v-if="agregandoEvidencia[unidad.id]" :size="13" class="spin" style="margin-right:4px" />
              <Plus v-else :size="13" style="margin-right:4px" />
              {{ agregandoEvidencia[unidad.id] ? 'Agregando…' : 'Añadir evidencia' }}
            </button>
          </div>

          <div style="overflow-x:auto; margin: 0 1.2rem 1.5rem">
            <table class="doc-table" style="width:100%; margin-bottom:0">
              <thead>
                <tr>
                  <td class="lbl" style="text-align:center">Evidencia
                    <InfoTooltip :texto="INSTRUCCIONES.evidenciaAprendizaje" />
                  </td>
                  <td class="lbl" style="text-align:center">Tipo de evaluación
                    <InfoTooltip :texto="INSTRUCCIONES.tipoEvaluacion" />
                  </td>
                  <td class="lbl" style="width:90px;text-align:center">Ponderación %
                    <InfoTooltip :texto="INSTRUCCIONES.ponderacion" />
                  </td>
                  <td class="lbl" style="text-align:center">Instrumento
                    <InfoTooltip :texto="INSTRUCCIONES.instrumentoEvaluacion" />
                  </td>
                  <td class="lbl" style="width:110px;text-align:center">Estado</td>
                  <td class="lbl" style="width:36px"></td>
                </tr>
              </thead>
              <tbody>
                <tr v-if="unidad.evidencias.length === 0">
                  <td colspan="6" class="val" style="text-align:center;color:#bbb;font-style:italic">Sin evidencias
                    registradas.</td>
                </tr>
                <tr v-for="ev in unidad.evidencias" :key="ev.id">
                  <td class="val"><textarea class="ev-cell" v-model="ev.evidencia_aprendizaje"
                      :disabled="!puedeEditarUnidad(unidad)"
                      @blur="guardarEvidencia(ev, 'evidencia_aprendizaje')"></textarea></td>
                  <td class="val">
                    <select class="eval-select" v-model="ev.tipo_evaluacion" :disabled="!puedeEditarUnidad(unidad)"
                      @change="guardarEvidencia(ev, 'tipo_evaluacion')">
                      <option :value="null">— Seleccionar —</option>
                      <option v-for="t in TIPOS_EVALUACION" :key="t" :value="t">{{ t }}</option>
                    </select>
                  </td>
                  <td class="val"><input class="eval-input" style="text-align:center" type="number" min="0" max="100"
                      v-model.number="ev.ponderacion" :disabled="!puedeEditarUnidad(unidad)"
                      @blur="guardarEvidencia(ev, 'ponderacion')" /></td>
                  <td class="val">
                    <select class="eval-select" v-model="ev.instrumento_evaluacion"
                      :disabled="!puedeEditarUnidad(unidad)" @change="guardarEvidencia(ev, 'instrumento_evaluacion')">
                      <option :value="null">— Seleccionar —</option>
                      <option v-for="ins in INSTRUMENTOS" :key="ins" :value="ins">{{ ins }}</option>
                    </select>
                  </td>
                  <ValidacionElemento variante="fila" tipo="evidencia" :elemento-id="ev.id" :revision="ev.revision"
                    :puede-validar="puedeValidarElementos" @actualizado="(r) => ev.revision = r" />
                  <td style="text-align:center;vertical-align:middle;border:1.5px solid #555;background:#fff8f8">
                    <IconButton v-if="puedeEditarUnidad(unidad)" title="Eliminar" variant="danger"
                      @click="eliminarEvidencia(unidad, ev)">
                      <Trash2 :size="13" />
                    </IconButton>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div style="padding:.5rem 1.2rem 1rem;font-size:10.5px;color:#856404">
            Cada unidad debe tener al menos dos tipos distintos de evaluación y la suma de ponderaciones debe ser
            exactamente 100%.
          </div>
          <DocFooter :pagina="paginaDe('evaluacion', i)" :total-paginas="totalPaginasDoc" />
        </div>

        <!-- D. Secuencia (fases) -->
        <div v-show="seccion === `unidad-${unidad.id}-secuencia`" class="doc-wrap fade-in">
          <DocHeader :subtitulo="`D.— Secuencia Didáctica — Unidad ${i + 1}`" />
          <div class="doc-section-title"><span>D.— Secuencia Didáctica por Unidad de Aprendizaje</span></div>

          <div v-for="tipoFase in ['apertura', 'desarrollo', 'cierre']" :key="tipoFase">
            <div class="fase-header">
              <span class="fase-header-title">{{ tipoFase }}
                <InfoTooltip :texto="INSTRUCCIONES['fase' + capitalizar(tipoFase)]" />
              </span>
              <button v-if="puedeEditarUnidad(unidad)" class="btn btn-primary btn-sm"
                :disabled="!!agregandoActividad[claveFase(unidad.id, tipoFase)]"
                @click="agregarActividad(unidad, tipoFase)">
                <Loader2 v-if="agregandoActividad[claveFase(unidad.id, tipoFase)]" :size="13" class="spin"
                  style="margin-right:4px" />
                <Plus v-else :size="13" style="margin-right:4px" />
                {{ agregandoActividad[claveFase(unidad.id, tipoFase)] ? 'Agregando…' : 'Añadir estrategia' }}
              </button>
            </div>
            <div class="fase-objectives">Desarrollo de la estrategia enseñanza-aprendizaje para esta fase.</div>

            <div style="overflow-x:auto; margin: 0 1.2rem 1.5rem">
              <table class="doc-table" style="width:100%; margin-bottom:0">
                <thead>
                  <tr>
                    <td class="lbl" style="font-size:10.5px;text-align:center">Métodos y técnicas
                      <InfoTooltip :texto="INSTRUCCIONES.metodosTecnicas" />
                    </td>
                    <td class="lbl" style="font-size:10.5px;text-align:center">Actividades docente
                      <InfoTooltip :texto="INSTRUCCIONES.actividadesDocente" />
                    </td>
                    <td class="lbl" style="font-size:10.5px;text-align:center">Actividades estudiante
                      <InfoTooltip :texto="INSTRUCCIONES.actividadesEstudiante" />
                    </td>
                    <td class="lbl" style="font-size:10.5px;text-align:center">Evidencia
                      <InfoTooltip :texto="INSTRUCCIONES.evidenciaFase" />
                    </td>
                    <td class="lbl" style="font-size:10.5px;text-align:center">Medios y materiales
                      <InfoTooltip :texto="INSTRUCCIONES.mediosMateriales" />
                    </td>
                    <td class="lbl" style="width:110px;text-align:center">Estado</td>
                    <td class="lbl" style="width:36px"></td>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="actividadesDeFase(unidad, tipoFase).length === 0">
                    <td colspan="7" class="val" style="text-align:center;color:#bbb;font-style:italic">Sin estrategias
                      registradas.</td>
                  </tr>
                  <tr v-for="act in actividadesDeFase(unidad, tipoFase)" :key="act.id">
                    <td class="val" style="padding:5px">
                      <select class="eval-select" v-model="act.metodos_tecnicas" :disabled="!puedeEditarUnidad(unidad)"
                        @change="guardarActividad(act, 'metodos_tecnicas')">
                        <option :value="null">— Seleccionar —</option>
                        <option v-for="(estrategia, idx) in ESTRATEGIAS_POR_FASE[tipoFase]" :key="estrategia"
                          :value="estrategia">
                          {{ idx + 1 }}) {{ estrategia }}
                        </option>
                      </select>
                    </td>
                    <td class="val"><textarea class="fase-cell" v-model="act.actividades_docente"
                        :disabled="!puedeEditarUnidad(unidad)"
                        @blur="guardarActividad(act, 'actividades_docente')"></textarea></td>
                    <td class="val"><textarea class="fase-cell" v-model="act.actividades_estudiante"
                        :disabled="!puedeEditarUnidad(unidad)"
                        @blur="guardarActividad(act, 'actividades_estudiante')"></textarea></td>
                    <td class="val"><textarea class="fase-cell" v-model="act.evidencia_aprendizaje"
                        :disabled="!puedeEditarUnidad(unidad)"
                        @blur="guardarActividad(act, 'evidencia_aprendizaje')"></textarea></td>
                    <td class="val"><textarea class="fase-cell" v-model="act.medios_materiales"
                        :disabled="!puedeEditarUnidad(unidad)"
                        @blur="guardarActividad(act, 'medios_materiales')"></textarea></td>
                    <ValidacionElemento variante="fila" tipo="fase" :elemento-id="act.id" :revision="act.revision"
                      :puede-validar="puedeValidarElementos" @actualizado="(r) => act.revision = r" />
                    <td style="text-align:center;vertical-align:middle;border:1.5px solid #555;background:#fff8f8">
                      <IconButton v-if="puedeEditarUnidad(unidad)" title="Eliminar" variant="danger"
                        @click="eliminarActividad(unidad, tipoFase, act)">
                        <Trash2 :size="13" />
                      </IconButton>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <DocFooter :pagina="paginaDe('secuencia', i)" :total-paginas="totalPaginasDoc" />
        </div>
      </template>

      <!-- ═══ BIBLIOGRAFÍA ═══ -->
      <div v-show="seccion === 'bibliografia'" class="doc-wrap fade-in">
        <DocHeader subtitulo="Referencias Bibliográficas y Digitales" />
        <div class="doc-section-title">
          <span>Referencias
            <InfoTooltip :texto="INSTRUCCIONES.referencias" />
          </span>
          <button v-if="editable" class="btn btn-primary btn-sm" :disabled="agregandoReferencia"
            @click="agregarReferencia">
            <Loader2 v-if="agregandoReferencia" :size="13" class="spin" style="margin-right:4px" />
            <Plus v-else :size="13" style="margin-right:4px" />
            {{ agregandoReferencia ? 'Agregando…' : 'Añadir referencia' }}
          </button>
        </div>
        <div style="overflow-x:auto; margin: 1rem 1.2rem 1.5rem">
          <table class="doc-table" style="width:100%; margin-bottom:0">
            <thead>
              <tr>
                <td class="lbl" style="width:40px;text-align:center">#</td>
                <td class="lbl" style="text-align:center">Autor</td>
                <td class="lbl" style="text-align:center">Título</td>
                <td class="lbl" style="text-align:center">Referencia / vínculo</td>
                <td class="lbl" style="width:110px;text-align:center">Estado</td>
                <td class="lbl" style="width:36px"></td>
              </tr>
            </thead>
            <tbody>
              <tr v-if="secuencia.referencias.length === 0">
                <td colspan="6" class="val" style="text-align:center;color:#bbb;font-style:italic">Sin referencias
                  registradas.</td>
              </tr>
              <tr v-for="(r, idx) in secuencia.referencias" :key="r.id">
                <td class="val" style="text-align:center">{{ idx + 1 }}</td>
                <td class="val"><input class="eval-input" v-model="r.autor" :disabled="!editable"
                    @blur="guardarReferencia(r, 'autor')" /></td>
                <td class="val"><input class="eval-input" v-model="r.titulo" :disabled="!editable"
                    @blur="guardarReferencia(r, 'titulo')" /></td>
                <td class="val"><textarea class="ev-cell" v-model="r.referencia" :disabled="!editable"
                    @blur="guardarReferencia(r, 'referencia')"></textarea></td>
                <ValidacionElemento variante="fila" tipo="referencia" :elemento-id="r.id" :revision="r.revision"
                  :puede-validar="puedeValidarElementos" @actualizado="(rv) => r.revision = rv" />
                <td style="text-align:center;vertical-align:middle;border:1.5px solid #555;background:#fff8f8">
                  <IconButton v-if="editable" title="Eliminar" variant="danger" @click="eliminarReferencia(r)">
                    <Trash2 :size="13" />
                  </IconButton>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <DocFooter :pagina="paginaDe('bibliografia')" :total-paginas="totalPaginasDoc" />
      </div>

      <!-- ═══ FINALIZAR ═══ -->
      <div v-show="seccion === 'finalizar'" class="doc-wrap fade-in">
        <DocHeader subtitulo="Estado y Validación de la Secuencia" />
        <div class="doc-section-title">
          <span>Estado actual</span>
          <span :class="['estado-badge', badgeEstadoDoc(secuencia.estado)]">{{ etiquetaEstado(secuencia.estado)
            }}</span>
        </div>

        <div style="padding: 1.2rem">
          <div v-if="mensajeAccion"
            :class="['alert', mensajeAccion.tipo === 'error' ? 'a-danger' : 'a-success', 'mb4']">
            {{ mensajeAccion.texto }}
          </div>

          <template v-if="secuencia.estado === 'borrador' && esAutor">
            <h3 class="ht-sm mb2">Lista de verificación ({{ itemsOk }}/{{ completitud.length }})</h3>
            <div class="progreso-barra mb3">
              <div class="progreso-relleno" :style="{ width: porcentajeOk + '%' }"></div>
            </div>
            <div class="checklist-completitud mb4">
              <div v-for="(item, idx) in completitud" :key="idx" class="check-item" :class="item.ok ? 'ok' : 'falta'"
                @click="!item.ok && (seccion = item.seccion)">
                <CheckCircle2 v-if="item.ok" :size="14" />
                <XCircle v-else :size="14" />
                <span>{{ item.label }}</span>
              </div>
            </div>
            <div class="flex g2u">
              <button class="btn btn-primary" :disabled="itemsOk < completitud.length || enviando"
                @click="enviarRevision">
                <Send :size="14" style="margin-right:4px" /> Enviar a revisión
              </button>
              <button class="btn btn-danger" :disabled="eliminando" @click="eliminarSecuencia">
                <Trash2 :size="14" style="margin-right:4px" /> Eliminar secuencia
              </button>
            </div>
          </template>

          <template v-else-if="secuencia.estado === 'en_revision'">
            <div class="alert a-info mb4">Esta secuencia está en revisión.</div>
            <div class="flex g2u fw">
              <button v-if="esAutor" class="btn btn-ghost" @click="cancelarEnvio">
                <Undo2 :size="14" style="margin-right:4px" /> Cancelar envío
              </button>
              <button v-if="puedeValidarElementos" class="btn btn-primary" @click="enviarValidacion">
                <ShieldCheck :size="14" style="margin-right:4px" /> Enviar a validación
              </button>
              <button v-if="puedeValidarElementos" class="btn btn-danger" @click="rechazarComoRevisor">
                <XCircle :size="14" style="margin-right:4px" /> Rechazar (devolver a borrador)
              </button>
            </div>
          </template>

          <template v-else-if="secuencia.estado === 'en_proceso_validacion'">
            <div class="alert a-info">Esta secuencia está en proceso de validación por el director.</div>
          </template>
          <template v-else-if="secuencia.estado === 'validada'">
            <div class="alert a-success">Esta secuencia fue validada. Ya no puede modificarse.</div>
            <a v-if="secuencia.documento_validacion_url" class="btn btn-outline mt3" :href="secuencia.documento_validacion_url"
              target="_blank" rel="noopener">
              <FileText :size="14" style="margin-right:4px" /> Ver documento de validación firmado
            </a>
          </template>
          <template v-else-if="secuencia.estado === 'rechazada'">
            <div class="alert a-danger">Esta secuencia fue rechazada por el director.</div>
          </template>
        </div>
        <DocFooter :pagina="paginaDe('finalizar')" :total-paginas="totalPaginasDoc" />
      </div>
    </div>
  </div>

  <EditarGruposAutoresModal v-if="modalGruposAbierto" :secuencia="secuencia" @close="modalGruposAbierto = false"
    @actualizado="onGruposActualizados" />
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import {
  ArrowLeft, FileText, ChevronRight, Info, ClipboardList, Layers, Library, CheckCheck,
  Pencil, Plus, Trash2, CheckCircle2, XCircle, Send, Undo2, ShieldCheck, Loader2,
} from 'lucide-vue-next'
import '@/assets/secuencia-documento.css'
import { INSTRUCCIONES } from '@/config/instrucciones'
import { ESTRATEGIAS_POR_FASE } from '@/config/estrategias'
import InfoTooltip from '@/components/InfoTooltip.vue'
import DocHeader from '@/components/DocHeader.vue'
import DocFooter from '@/components/DocFooter.vue'
import ValidacionElemento from '@/components/ValidacionElemento.vue'
import IconButton from '@/components/IconButton.vue'
import EditarGruposAutoresModal from './EditarGruposAutoresModal.vue'
import api from '@/services/api'
import router from '@/router'

const route = useRoute()
const secuenciaId = route.params.id

const cargando = ref(true)
const secuencia = ref(null)
const esAutor = ref(false)
const puedeValidarElementos = ref(false)
const editable = ref(false)
const completitud = ref([])
const seccion = ref('caratula')
const gruposAbiertos = reactive({})
const modalGruposAbierto = ref(false)
const enviando = ref(false)
const eliminando = ref(false)
const mensajeAccion = ref(null)

// ── Estados de "agregando…" para bloquear botones y evitar duplicados
// por doble clic mientras se espera la respuesta del servidor.
const agregandoTema = reactive({})       // keyed por unidad.id
const agregandoEvidencia = reactive({})  // keyed por unidad.id
const agregandoActividad = reactive({})  // keyed por `${unidad.id}-${fase}`
const agregandoReferencia = ref(false)
function claveFase(unidadId, fase) { return `${unidadId}-${fase}` }

// ── Indicador de autoguardado (badge flotante arriba a la derecha)
// null | 'guardando' | 'guardado' | 'error'
const estadoGuardado = ref(null)
let timeoutOcultarGuardado = null
function marcarGuardando() {
  clearTimeout(timeoutOcultarGuardado)
  estadoGuardado.value = 'guardando'
}
function marcarGuardado() {
  estadoGuardado.value = 'guardado'
  clearTimeout(timeoutOcultarGuardado)
  timeoutOcultarGuardado = setTimeout(() => { estadoGuardado.value = null }, 1800)
}
function marcarErrorGuardado() {
  estadoGuardado.value = 'error'
  clearTimeout(timeoutOcultarGuardado)
  timeoutOcultarGuardado = setTimeout(() => { estadoGuardado.value = null }, 2500)
}

const caratula = computed(() => secuencia.value.caratula)

// ── Numeración de "página" del pie de página (réplica del formato oficial:
// Carátula = pág. 1, luego 3 páginas por cada unidad (B, C, D), luego
// Bibliografía y Finalizar). No es una paginación real de impresión, solo
// la numeración que muestra el documento oficial en cada sección. ──
const totalPaginasDoc = computed(() => 1 + (secuencia.value.unidades.length * 3) + 2)
function paginaDe(tipo, indiceUnidad = 0) {
  if (tipo === 'caratula') return 1
  if (tipo === 'info') return 2 + indiceUnidad * 3
  if (tipo === 'evaluacion') return 3 + indiceUnidad * 3
  if (tipo === 'secuencia') return 4 + indiceUnidad * 3
  if (tipo === 'bibliografia') return 2 + secuencia.value.unidades.length * 3
  if (tipo === 'finalizar') return 3 + secuencia.value.unidades.length * 3
  return 1
}

const TIPOS_EVALUACION = [
  'Autoevaluación', 'Coevaluación', 'Heteroevaluación',
  'Autoevaluación + Coevaluación', 'Autoevaluación + Heteroevaluación',
  'Coevaluación + Heteroevaluación', 'Autoevaluación + Coevaluación + Heteroevaluación',
]
const INSTRUMENTOS = [
  'Cuestionario de preguntas abiertas', 'Prueba objetiva', 'Prueba por competencias',
  'Lista de cotejo', 'Guía de observación', 'Escala estimativa', 'Rúbrica',
]

const itemsOk = computed(() => completitud.value.filter((i) => i.ok).length)
const porcentajeOk = computed(() => completitud.value.length ? Math.round((itemsOk.value / completitud.value.length) * 100) : 0)

onMounted(cargar)

// Solo el docente autor necesita ver el checklist actualizado (es lo único
// que lo usa, en la sección Finalizar). Se refresca solo, sin recargar toda
// la secuencia, cada vez que cambia algo que pueda afectarlo.
async function refrescarCompletitud() {
  if (!esAutor.value) return
  try {
    const { data } = await api.get(`/secuencias/${secuencia.value.id}/completitud`)
    completitud.value = data
  } catch (e) {
    console.error(e)
  }
}

// Además, se refresca siempre que el docente entra a la sección Finalizar,
// por si acaso algún cambio no disparó el refresco puntual.
watch(seccion, (nueva) => {
  if (nueva === 'finalizar') refrescarCompletitud()
})

async function cargar() {
  cargando.value = true
  try {
    const { data } = await api.get(`/secuencias/${secuenciaId}`)
    secuencia.value = data.secuencia
    esAutor.value = data.es_autor
    puedeValidarElementos.value = data.puede_validar_elementos
    editable.value = data.editable
    completitud.value = data.completitud
    secuencia.value.unidades.forEach((u) => { gruposAbiertos[u.id] = true })
  } catch (e) {
    alert(e.response?.data?.message || 'No se pudo cargar la secuencia.')
    router.back()
  } finally {
    cargando.value = false
  }
}

function toggleGrupo(id) { gruposAbiertos[id] = !gruposAbiertos[id] }
function puedeEditarUnidad(unidad) { return editable.value }
function actividadesDeFase(unidad, tipoFase) { return unidad.fases.find((f) => f.fase === tipoFase)?.actividades ?? [] }
function sumaPonderacion(unidad) {
  return Math.round((unidad.evidencias?.reduce((s, e) => s + (Number(e.ponderacion) || 0), 0) ?? 0) * 100) / 100
}
function capitalizar(s) { return s.charAt(0).toUpperCase() + s.slice(1) }

async function guardarCaratula(campo) {
  marcarGuardando()
  try {
    await api.patch(`/docente/secuencias/${secuencia.value.id}/caratula`, { [campo]: caratula.value[campo] })
    marcarGuardado()
    if (campo === 'proposito_aprendizaje' || campo === 'competencia') refrescarCompletitud()
  } catch (e) { console.error(e); marcarErrorGuardado() }
}

async function guardarUnidad(unidad, campo) {
  marcarGuardando()
  try {
    await api.patch(`/docente/unidades/${unidad.id}`, { [campo]: unidad[campo] })
    marcarGuardado()
  } catch (e) { console.error(e); marcarErrorGuardado() }
}

async function agregarTema(unidad) {
  if (agregandoTema[unidad.id]) return
  agregandoTema[unidad.id] = true
  try {
    const { data } = await api.post(`/docente/unidades/${unidad.id}/temas`, {})
    unidad.temas.push(data)
    refrescarCompletitud()
  } finally {
    agregandoTema[unidad.id] = false
  }
}
async function guardarTema(tema, campo) {
  marcarGuardando()
  try {
    await api.patch(`/docente/temas/${tema.id}`, { [campo]: tema[campo] })
    marcarGuardado()
  } catch (e) { console.error(e); marcarErrorGuardado() }
}
async function eliminarTema(unidad, tema) {
  if (!confirm('¿Eliminar este tema?')) return
  await api.delete(`/docente/temas/${tema.id}`)
  unidad.temas = unidad.temas.filter((t) => t.id !== tema.id)
  refrescarCompletitud()
}

async function guardarEvaluacion(unidad) {
  marcarGuardando()
  try {
    await api.patch(`/docente/unidades/${unidad.id}/evaluacion`, {
      periodo_semanas: unidad.evaluacion.periodo_semanas,
      resultado_aprendizaje: unidad.evaluacion.resultado_aprendizaje,
    })
    marcarGuardado()
    refrescarCompletitud()
  } catch (e) { console.error(e); marcarErrorGuardado() }
}

async function agregarEvidencia(unidad) {
  if (agregandoEvidencia[unidad.id]) return
  agregandoEvidencia[unidad.id] = true
  try {
    const { data } = await api.post(`/docente/unidades/${unidad.id}/evidencias`, {})
    unidad.evidencias.push(data)
    refrescarCompletitud()
  } finally {
    agregandoEvidencia[unidad.id] = false
  }
}
async function guardarEvidencia(evidencia, campo) {
  marcarGuardando()
  try {
    await api.patch(`/docente/evidencias/${evidencia.id}`, { [campo]: evidencia[campo] })
    marcarGuardado()
    // ponderacion/tipo_evaluacion son justo lo que evalúa el checklist
    if (campo === 'ponderacion' || campo === 'tipo_evaluacion') refrescarCompletitud()
  } catch (e) { console.error(e); marcarErrorGuardado() }
}
async function eliminarEvidencia(unidad, evidencia) {
  if (!confirm('¿Eliminar esta evidencia?')) return
  await api.delete(`/docente/evidencias/${evidencia.id}`)
  unidad.evidencias = unidad.evidencias.filter((e) => e.id !== evidencia.id)
  refrescarCompletitud()
}

async function agregarActividad(unidad, tipoFase) {
  const clave = claveFase(unidad.id, tipoFase)
  if (agregandoActividad[clave]) return
  agregandoActividad[clave] = true
  try {
    const { data } = await api.post(`/docente/unidades/${unidad.id}/fases/${tipoFase}/actividades`, {})
    let fase = unidad.fases.find((f) => f.fase === tipoFase)
    if (!fase) { fase = { fase: tipoFase, actividades: [] }; unidad.fases.push(fase) }
    fase.actividades.push(data)
    refrescarCompletitud()
  } finally {
    agregandoActividad[clave] = false
  }
}
async function guardarActividad(actividad, campo) {
  marcarGuardando()
  try {
    await api.patch(`/docente/fase-actividades/${actividad.id}`, { [campo]: actividad[campo] })
    marcarGuardado()
  } catch (e) { console.error(e); marcarErrorGuardado() }
}
async function eliminarActividad(unidad, tipoFase, actividad) {
  if (!confirm('¿Eliminar esta estrategia?')) return
  await api.delete(`/docente/fase-actividades/${actividad.id}`)
  const fase = unidad.fases.find((f) => f.fase === tipoFase)
  fase.actividades = fase.actividades.filter((a) => a.id !== actividad.id)
  refrescarCompletitud()
}

async function agregarReferencia() {
  if (agregandoReferencia.value) return
  agregandoReferencia.value = true
  try {
    const { data } = await api.post(`/docente/secuencias/${secuencia.value.id}/referencias`, {})
    secuencia.value.referencias.push(data)
    refrescarCompletitud()
  } finally {
    agregandoReferencia.value = false
  }
}
async function guardarReferencia(referencia, campo) {
  marcarGuardando()
  try {
    await api.patch(`/docente/referencias/${referencia.id}`, { [campo]: referencia[campo] })
    marcarGuardado()
  } catch (e) { console.error(e); marcarErrorGuardado() }
}
async function eliminarReferencia(referencia) {
  if (!confirm('¿Eliminar esta referencia?')) return
  await api.delete(`/docente/referencias/${referencia.id}`)
  secuencia.value.referencias = secuencia.value.referencias.filter((r) => r.id !== referencia.id)
  refrescarCompletitud()
}

function onGruposActualizados(data) {
  secuencia.value.autores = data.autores
  secuencia.value.grupos = data.grupos
  modalGruposAbierto.value = false
  refrescarCompletitud()
}

async function enviarRevision() {
  enviando.value = true
  mensajeAccion.value = null
  try {
    const { data } = await api.post(`/docente/secuencias/${secuencia.value.id}/enviar-revision`)
    secuencia.value.estado = data.estado
    mensajeAccion.value = { tipo: 'ok', texto: 'Secuencia enviada a revisión.' }
  } catch (e) {
    const errores = e.response?.data?.errors?.completitud
    mensajeAccion.value = { tipo: 'error', texto: errores ? errores.join(' ') : (e.response?.data?.message || 'No se pudo enviar.') }
  } finally {
    enviando.value = false
  }
}
async function eliminarSecuencia() {
  if (!confirm('¿Eliminar esta secuencia? Esta acción no se puede deshacer.')) return
  eliminando.value = true
  try {
    await api.delete(`/docente/secuencias/${secuencia.value.id}`)
    router.push({ name: 'secuencias-docente' })
  } catch (e) {
    mensajeAccion.value = { tipo: 'error', texto: e.response?.data?.message || 'No se pudo eliminar la secuencia.' }
  } finally {
    eliminando.value = false
  }
}

async function cancelarEnvio() {
  if (!confirm('¿Cancelar el envío y volver a borrador?')) return
  const { data } = await api.post(`/docente/secuencias/${secuencia.value.id}/cancelar-envio`)
  secuencia.value.estado = data.estado
  await cargar()
}
async function enviarValidacion() {
  const { data } = await api.post(`/revisor/secuencias/${secuencia.value.id}/enviar-validacion`)
  secuencia.value.estado = data.estado
}
async function rechazarComoRevisor() {
  if (!confirm('¿Rechazar y devolver esta secuencia a borrador?')) return
  const { data } = await api.post(`/revisor/secuencias/${secuencia.value.id}/rechazar`)
  secuencia.value.estado = data.estado
}

function badgeEstadoDoc(estado) {
  return { borrador: 'estado-En_desarrollo', en_revision: 'estado-En_revision', en_proceso_validacion: 'estado-En_proceso_validacion', validada: 'estado-Validada', rechazada: 'estado-Rechazada' }[estado] ?? 'estado-En_desarrollo'
}
function etiquetaEstado(estado) {
  return { borrador: 'Borrador', en_revision: 'En revisión', en_proceso_validacion: 'En validación', validada: 'Validada', rechazada: 'Rechazada' }[estado] ?? estado
}
</script>

<style scoped>
.editor-loading {
  padding: 3rem;
  text-align: center;
  color: var(--text-300);
}

.progreso-barra {
  background: #e8e8e8;
  border-radius: 99px;
  height: 6px;
}

.progreso-relleno {
  background: var(--uth-verde);
  height: 6px;
  border-radius: 99px;
  transition: width .3s;
}

.checklist-completitud {
  border: 1px solid var(--border);
  border-radius: var(--r-sm);
  font-size: var(--p-xs);
}

.check-item {
  display: flex;
  align-items: center;
  gap: var(--s2);
  padding: var(--s2) var(--s3);
  border-bottom: 1px solid var(--border);
}

.check-item:last-child {
  border-bottom: none;
}

.check-item.ok {
  background: #f8fff7;
  color: #2e6b24;
}

.check-item.falta {
  background: #fff8f8;
  color: #721c24;
  cursor: pointer;
}

.mb2 {
  margin-bottom: var(--s2);
}

.mb3 {
  margin-bottom: var(--s3);
}

.mb4 {
  margin-bottom: var(--s4);
}

/* ── Spinner en botones "agregar" ── */
.spin {
  animation: girar 0.8s linear infinite;
}

@keyframes girar {
  from {
    transform: rotate(0deg);
  }

  to {
    transform: rotate(360deg);
  }
}

/* ── Aviso de autoguardado en Carátula ── */
.hint-autoguardado {
  margin: 0 1.2rem .8rem;
  font-size: 11px;
  color: var(--text-300, #888);
  font-style: italic;
}

/* ── Badge flotante de estado de guardado ──
   position: fixed lo saca completamente del flujo del documento, para que
   aparecer/desaparecer NO empuje ni recorra el resto del formulario. */
.badge-autoguardado {
  position: fixed;
  top: 16px;
  right: 24px;
  z-index: 50;
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 5px 10px;
  border-radius: 99px;
  font-size: 11.5px;
  font-weight: 600;
  box-shadow: 0 2px 8px rgba(0, 0, 0, .15);
}

.badge-autoguardado.ag-guardando {
  background: #eef4ff;
  color: #2c5cc5;
}

.badge-autoguardado.ag-guardado {
  background: #f0faf0;
  color: #2e6b24;
}

.badge-autoguardado.ag-error {
  background: #fff0f0;
  color: #a12a2a;
}

.fade-guardado-enter-active,
.fade-guardado-leave-active {
  transition: opacity .25s ease;
}

.fade-guardado-enter-from,
.fade-guardado-leave-to {
  opacity: 0;
}
</style>