<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class ManualContent
{
    public static function seedDefaults(bool $overwrite = false): void
    {
        if (! Schema::hasTable('manual_sections') || ! Schema::hasTable('manual_articles')) {
            return;
        }

        $sectionIds = [];

        foreach (self::sections() as $section) {
            $sectionIds[$section['slug']] = self::insertOrUpdateSection($section, $overwrite);
        }

        foreach (self::articles() as $article) {
            $sectionId = $sectionIds[$article['section']] ?? null;

            if (! $sectionId) {
                continue;
            }

            $data = [
                'manual_section_id' => $sectionId,
                'title' => $article['title'],
                'summary' => $article['summary'],
                'content' => self::body(
                    $article['objective'],
                    $article['requirements'],
                    $article['steps'],
                    $article['result'],
                    $article['warnings'] ?? [],
                    $article['errors'] ?? [],
                    $article['recommendations'] ?? []
                ),
                'required_permission' => $article['permission'] ?? null,
                'related_route_name' => $article['route'] ?? null,
                'sort_order' => $article['sort_order'],
                'updated_at' => now(),
            ];

            $existingArticleId = DB::table('manual_articles')
                ->where('slug', $article['slug'])
                ->value('id');

            if ($existingArticleId) {
                if ($overwrite) {
                    DB::table('manual_articles')->where('id', $existingArticleId)->update($data);
                }

                continue;
            }

            DB::table('manual_articles')->insert($data + [
                'slug' => $article['slug'],
                'is_active' => true,
                'created_at' => now(),
            ]);
        }
    }

    private static function insertOrUpdateSection(array $section, bool $overwrite): int
    {
        $id = DB::table('manual_sections')->where('slug', $section['slug'])->value('id');

        if ($id) {
            if ($overwrite) {
                DB::table('manual_sections')->where('id', $id)->update([
                    'title' => $section['title'],
                    'description' => $section['description'],
                    'icon' => $section['icon'],
                    'required_permission' => $section['permission'] ?? null,
                    'sort_order' => $section['sort_order'],
                    'updated_at' => now(),
                ]);
            }

            return (int) $id;
        }

        return (int) DB::table('manual_sections')->insertGetId([
            'slug' => $section['slug'],
            'title' => $section['title'],
            'description' => $section['description'],
            'icon' => $section['icon'],
            'required_permission' => $section['permission'] ?? null,
            'sort_order' => $section['sort_order'],
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private static function body(
        string $objective,
        array $requirements,
        array $steps,
        string $result,
        array $warnings = [],
        array $errors = [],
        array $recommendations = []
    ): string {
        $html = '<h2>Objetivo</h2><p>'.e($objective).'</p>';
        $html .= self::list('Antes de comenzar', $requirements, 'ul');
        $html .= self::list('Pasos', $steps, 'ol');
        $html .= '<h2>Resultado esperado</h2><p>'.e($result).'</p>';

        if ($warnings !== []) {
            $html .= self::list('Advertencias importantes', $warnings, 'ul');
        }

        if ($errors !== []) {
            $html .= self::list('Errores frecuentes', $errors, 'ul');
        }

        if ($recommendations !== []) {
            $html .= self::list('Buenas practicas', $recommendations, 'ul');
        }

        return $html;
    }

    private static function list(string $title, array $items, string $tag): string
    {
        $html = '<h2>'.e($title).'</h2><'.$tag.'>';

        foreach ($items as $item) {
            $html .= '<li>'.e($item).'</li>';
        }

        return $html.'</'.$tag.'>';
    }

    private static function sections(): array
    {
        return [
            ['slug' => 'primeros-pasos', 'title' => 'Primeros pasos', 'description' => 'Conceptos basicos para comenzar a trabajar en el panel.', 'icon' => 'start', 'sort_order' => 10],
            ['slug' => 'guias-rapidas', 'title' => 'Guias rapidas', 'description' => 'Procesos frecuentes explicados de principio a fin.', 'icon' => 'bolt', 'sort_order' => 20],
            ['slug' => 'inmobiliaria', 'title' => 'Inmobiliaria', 'description' => 'Dashboard, inventario y zonas.', 'icon' => 'home', 'sort_order' => 30],
            ['slug' => 'crm', 'title' => 'CRM', 'description' => 'Leads, clientes, comentarios y agenda de visitas.', 'icon' => 'users', 'sort_order' => 40],
            ['slug' => 'administracion', 'title' => 'Administracion', 'description' => 'Usuarios, roles y permisos internos.', 'icon' => 'shield', 'sort_order' => 50],
            ['slug' => 'integraciones', 'title' => 'MLS', 'description' => 'Sincronizacion MLS, catalogos y exportacion hacia EasyBroker.', 'icon' => 'sync', 'sort_order' => 60],
            ['slug' => 'correos', 'title' => 'Correos', 'description' => 'Cuentas corporativas, bandejas y redaccion de mensajes.', 'icon' => 'mail', 'sort_order' => 70],
            ['slug' => 'cms', 'title' => 'CMS', 'description' => 'Edicion del contenido publico, menus y configuracion del sitio.', 'icon' => 'edit', 'sort_order' => 80],
            ['slug' => 'ajustes', 'title' => 'Ajustes', 'description' => 'Monedas, temas, colores frontend, EasyBroker y notificaciones.', 'icon' => 'settings', 'sort_order' => 90],
            ['slug' => 'ayuda-interna', 'title' => 'Ayuda interna', 'description' => 'Consulta y mantenimiento de este centro de conocimiento.', 'icon' => 'help', 'sort_order' => 100],
        ];
    }

    private static function articles(): array
    {
        return array_merge([
            self::article('conocer-el-panel', 'Conocer el panel', 'Ubica las areas principales del sistema antes de comenzar.', 'primeros-pasos', 10, null, 'dashboard',
                'Reconocer el menu, la barra superior y las acciones disponibles en el panel.',
                ['Inicia sesion con tu usuario asignado.', 'Ten presente que las opciones visibles dependen de tu rol.'],
                ['Usa el menu lateral para abrir un grupo de trabajo.', 'Observa la ruta superior para confirmar en que modulo te encuentras.', 'Utiliza los accesos rapidos de la barra superior cuando necesites crear una propiedad o agendar una visita.', 'Revisa la campana para consultar notificaciones pendientes.', 'Cierra sesion desde el boton Salir al finalizar tu jornada.'],
                'Puedes navegar con seguridad entre los modulos disponibles para tu usuario.',
                ['No compartas tu contrasena ni dejes una sesion abierta en equipos compartidos.']),
            self::article('entender-roles-y-permisos', 'Entender roles y permisos', 'Por que dos usuarios pueden ver opciones diferentes.', 'primeros-pasos', 20, null, null,
                'Comprender como los permisos controlan la informacion y las acciones disponibles.',
                ['Identifica si trabajas como agente, asistente, manager o administrador.'],
                ['Cada opcion del menu requiere un permiso.', 'Algunas pantallas permiten consultar datos, pero reservan la edicion para perfiles autorizados.', 'Los agentes suelen trabajar con leads, clientes y agenda relacionados con su operacion.', 'Los responsables administrativos pueden gestionar contenido, integraciones y configuraciones.', 'Si necesitas una opcion que no aparece, solicita al administrador que revise tu rol.'],
                'Sabes distinguir un problema de permisos de un error del sistema.',
                ['No solicites permisos amplios si solo necesitas una accion puntual.']),
            self::article('usar-notificaciones', 'Usar las notificaciones', 'Consulta eventos recientes y marca pendientes como leidos.', 'primeros-pasos', 30, 'menu.notifications.view', 'notifications',
                'Mantener al dia los eventos que requieren revision.',
                ['Debes tener acceso a notificaciones.'],
                ['Haz clic en la campana de la barra superior.', 'Lee el resumen y abre la notificacion que deseas revisar.', 'El sistema marcara el elemento como leido al abrirlo.', 'Usa Marcar leidas cuando hayas revisado todos los pendientes.', 'Entra al modulo Notificaciones para consultar el historial completo.'],
                'La campana refleja solamente los eventos que todavia requieren tu atencion.',
                [],
                ['Si el contador no cambia, actualiza la lista desde el panel de notificaciones.']),

            self::article('registrar-propiedad', 'Registrar una propiedad manualmente', 'Crea un inmueble nuevo y completa su informacion principal.', 'guias-rapidas', 10, 'menu.properties.view', 'properties',
                'Crear una propiedad local lista para ser revisada y publicada.',
                ['Confirma que tienes la informacion comercial, ubicacion, precio y material visual.', 'Verifica que la propiedad no exista previamente.'],
                ['Abre Propiedades y selecciona Nueva propiedad.', 'Completa los datos generales y el tipo de operacion.', 'Registra precio, moneda y ubicacion.', 'Agrega descripciones claras en los idiomas disponibles.', 'Selecciona imagenes y documentos desde el administrador multimedia.', 'Guarda el registro y revisa la ficha antes de publicarlo.'],
                'La propiedad aparece en el inventario interno con su informacion principal.',
                ['No publiques una propiedad sin revisar precio, moneda, ubicacion e imagen principal.'],
                ['Crear duplicados cuando la propiedad ya llego por MLS o EasyBroker.', 'Guardar una moneda incorrecta para el precio indicado.']),
            self::article('atender-lead', 'Atender un lead nuevo', 'Registra seguimiento desde el primer contacto.', 'guias-rapidas', 20, 'menu.property-contact-requests.view', 'property-contact-requests',
                'Dar seguimiento ordenado a una solicitud comercial recibida.',
                ['Revisa la notificacion o abre Leads.', 'Ten disponible el contexto de la propiedad consultada cuando exista.'],
                ['Localiza el lead nuevo en la lista.', 'Revisa nombre, telefono, correo, origen y mensaje.', 'Actualiza el estado segun el contacto realizado.', 'Asigna el responsable cuando tu perfil lo permita.', 'Agrega contexto util para el seguimiento.', 'Convierte el lead en cliente cuando ya deba gestionarse como contacto activo.'],
                'El equipo puede identificar el avance real del contacto y el siguiente paso.',
                ['No elimines un lead solo porque todavia no responde. Actualiza su estado.']),
            self::article('convertir-lead-en-cliente', 'Convertir un lead en cliente', 'Pasa una solicitud comercial al expediente de cliente.', 'guias-rapidas', 30, 'menu.property-contact-requests.view', 'property-contact-requests',
                'Crear un expediente de cliente a partir de un lead valido.',
                ['Abre el lead y valida sus datos de contacto.', 'Confirma que no exista ya como cliente.'],
                ['Entra al modulo Leads.', 'Busca y abre el registro.', 'Actualiza la informacion faltante si es necesario.', 'Selecciona la opcion para convertirlo en cliente.', 'Abre el nuevo expediente y agrega contexto, comentarios o una visita.'],
                'Existe un expediente reutilizable para el seguimiento comercial.',
                ['Evita crear expedientes duplicados para la misma persona.']),
            self::article('agendar-visita', 'Agendar una visita', 'Registra una cita comercial desde la agenda o desde un cliente.', 'guias-rapidas', 40, 'menu.calendar.view', 'calendar',
                'Reservar una visita con fecha, hora y contexto suficiente.',
                ['El cliente debe existir en el CRM.', 'Ten clara la propiedad o el objetivo de la visita.'],
                ['Abre Agenda de visitas o utiliza Agendar visita en la barra superior.', 'Selecciona el cliente.', 'Indica fecha, hora y propiedad cuando corresponda.', 'Agrega notas utiles para quien atendera la cita.', 'Guarda la visita.', 'Despues de la cita, registra el resultado y cualquier siguiente paso.'],
                'La visita aparece en el calendario mensual y en el expediente del cliente.',
                ['Actualiza o cancela la visita si cambia el horario para evitar confusiones.']),
            self::article('actualizar-sitio-web', 'Actualizar textos e imagenes del sitio web', 'Edita contenido publico desde el CMS.', 'guias-rapidas', 50, 'menu.cms.pages.view', 'cms.pages',
                'Modificar una seccion publica sin intervenir el codigo del sitio.',
                ['Define el texto final y prepara imagenes optimizadas.', 'Identifica la pagina publica que necesitas modificar.'],
                ['Abre CMS y entra a Paginas.', 'Selecciona la pagina correspondiente.', 'Ubica el grupo y el campo que deseas modificar.', 'Actualiza textos e imagenes.', 'Guarda los cambios.', 'Abre la pagina publica y valida el resultado en computadora y celular.'],
                'El sitio publico muestra el contenido actualizado.',
                ['Revisa el resultado visual antes de dar por terminada la tarea.']),

            self::article('administrar-propiedades', 'Administrar propiedades', 'Busqueda, edicion, publicacion y mantenimiento del inventario.', 'inmobiliaria', 10, 'menu.properties.view', 'properties',
                'Mantener el inventario confiable y actualizado.',
                ['Distingue si la propiedad es manual, MLS o EasyBroker antes de editar.'],
                ['Usa los filtros para localizar una propiedad.', 'Abre la ficha y revisa la fuente de datos.', 'Edita solamente los campos que correspondan al proceso interno.', 'Actualiza estado, informacion comercial y material visual cuando sea necesario.', 'Revisa el resultado antes de publicar.', 'Utiliza eliminar o restaurar solamente cuando el proceso lo requiera.'],
                'El inventario interno refleja informacion vigente y consistente.',
                ['Una sincronizacion puede volver a actualizar campos provenientes de una integracion.'],
                ['Editar manualmente datos sincronizados sin comprobar si la integracion los reemplazara.']),
            self::article('gestionar-multimedia', 'Gestionar imagenes, documentos y videos', 'Selecciona recursos visuales reutilizables desde el administrador multimedia.', 'inmobiliaria', 20, 'menu.properties.view', 'properties',
                'Adjuntar material visual correcto a las propiedades y al contenido del sitio.',
                ['Prepara archivos con nombres descriptivos y peso razonable.'],
                ['Abre el selector multimedia desde el formulario que estas editando.', 'Busca un archivo existente antes de subir uno nuevo.', 'Filtra por tipo cuando la biblioteca sea extensa.', 'Selecciona la imagen o documento adecuado.', 'Para videos embebidos, usa una URL compatible.', 'Guarda el formulario principal y valida la vista final.'],
                'El recurso seleccionado aparece correctamente en su contexto.',
                ['Evita subir varias copias del mismo archivo.']),
            self::article('administrar-zonas-seo', 'Administrar zonas SEO', 'Mantiene paginas publicas por ubicacion.', 'inmobiliaria', 30, 'menu.zones.view', 'zones',
                'Controlar el contenido publico asociado a las zonas inmobiliarias.',
                ['Confirma que tienes acceso a Zonas SEO.'],
                ['Abre Zonas SEO.', 'Sincroniza las zonas cuando necesites incorporar ubicaciones disponibles.', 'Busca la zona que deseas trabajar.', 'Edita titulos, descripciones y metadatos.', 'Configura su visibilidad y orden cuando corresponda.', 'Guarda y revisa la pagina publica asociada.'],
                'La pagina de zona muestra informacion adecuada para usuarios y buscadores.',
                ['No uses textos duplicados en todas las zonas; cada ubicacion debe aportar contexto propio.']),
            self::article('gestionar-leads', 'Gestionar leads', 'Consulta, clasifica y da seguimiento a solicitudes comerciales.', 'crm', 10, 'menu.property-contact-requests.view', 'property-contact-requests',
                'Evitar que una solicitud comercial quede sin seguimiento.',
                ['Revisa diariamente Leads y notificaciones.'],
                ['Filtra la lista segun estado, responsable u origen.', 'Abre cada solicitud nueva.', 'Valida los datos de contacto y el mensaje.', 'Registra el estado real del seguimiento.', 'Agrega manualmente leads recibidos por telefono, redes o recomendacion.', 'Convierte el lead en cliente cuando avance el proceso.'],
                'Cada solicitud tiene estado y contexto comercial verificables.',
                ['No registres informacion sensible innecesaria en notas abiertas.']),
            self::article('gestionar-clientes', 'Gestionar clientes', 'Actualiza el expediente comercial de cada contacto.', 'crm', 20, 'menu.clients.view', 'clients',
                'Centralizar el historial util para atender correctamente a cada cliente.',
                ['Abre Clientes y localiza el expediente existente.'],
                ['Revisa datos de contacto y contexto.', 'Actualiza informacion desactualizada.', 'Agrega comentarios con decisiones, acuerdos y siguientes pasos.', 'Consulta las visitas registradas.', 'Programa nuevas visitas desde el expediente cuando corresponda.'],
                'El expediente permite retomar una conversacion sin perder contexto.',
                ['Escribe comentarios concretos y profesionales.']),
            self::article('registrar-comentarios', 'Registrar comentarios de cliente', 'Documenta acuerdos y siguientes pasos.', 'crm', 30, 'menu.clients.view', 'clients',
                'Conservar un historial breve y util del seguimiento.',
                ['Abre el expediente del cliente correcto.'],
                ['Entra a la seccion Comentarios.', 'Escribe la fecha o contexto cuando sea importante.', 'Resume el acuerdo, necesidad o siguiente accion.', 'Guarda el comentario.', 'Edita o elimina un comentario solo si realmente contiene un error.'],
                'El equipo puede consultar el historial comercial del cliente.',
                ['No incluyas contrasenas, datos bancarios ni documentos confidenciales.']),
            self::article('gestionar-agenda', 'Gestionar la agenda de visitas', 'Consulta el calendario y actualiza citas.', 'crm', 40, 'menu.calendar.view', 'calendar',
                'Coordinar visitas sin perder cambios ni resultados.',
                ['Consulta el calendario al inicio de la jornada.'],
                ['Abre Agenda de visitas.', 'Navega al mes correspondiente.', 'Selecciona una visita para revisar sus detalles.', 'Actualiza horario, notas o estado cuando cambie la cita.', 'Registra el resultado despues de realizarla.', 'Consulta tambien el expediente del cliente para mantener el contexto completo.'],
                'El calendario refleja compromisos y resultados vigentes.',
                ['No dejes visitas pasadas sin resultado si hubo informacion relevante.']),

            self::article('usar-bandeja-entrada', 'Usar la bandeja de entrada', 'Consulta y responde correos corporativos.', 'correos', 10, 'menu.corporate-email.inbox.view', 'corporate-email.inbox',
                'Procesar mensajes recibidos desde la cuenta asignada.',
                ['Tu usuario debe tener una cuenta corporativa configurada.'],
                ['Abre Correos y entra a Bandeja.', 'Actualiza la bandeja cuando necesites traer mensajes recientes.', 'Abre un mensaje para revisar remitente, asunto y contenido.', 'Marca como leido al procesarlo.', 'Usa Redactar para preparar una respuesta cuando corresponda.'],
                'Los mensajes atendidos quedan identificados y disponibles para consulta.',
                ['Confirma destinatarios antes de enviar informacion comercial.']),
            self::article('redactar-correo', 'Redactar y enviar un correo', 'Envia mensajes desde una cuenta corporativa.', 'correos', 20, 'menu.corporate-email.compose.view', 'corporate-email.compose',
                'Enviar un mensaje profesional desde el panel.',
                ['Verifica el destinatario y define el objetivo del mensaje.'],
                ['Abre Redactar.', 'Selecciona la cuenta remitente cuando exista mas de una.', 'Completa destinatario y asunto.', 'Escribe un mensaje claro y revisa su contenido.', 'Envia el correo.', 'Consulta Salida para confirmar el registro del envio.'],
                'El correo queda registrado en la bandeja de salida.',
                ['Revisa cuidadosamente direcciones y archivos antes de enviar.']),
            self::article('configurar-correo', 'Configurar cuentas de correo', 'Registra parametros de conexion para cuentas corporativas.', 'correos', 30, 'menu.corporate-email.configuration.view', 'corporate-email.configuration',
                'Mantener operativas las cuentas corporativas conectadas al sistema.',
                ['Solicita al proveedor los parametros IMAP y SMTP correctos.', 'Realiza esta tarea solamente si administras cuentas de correo.'],
                ['Abre Configuracion dentro de Correos.', 'Crea o edita la cuenta.', 'Completa servidor, puertos, usuario y seguridad.', 'Guarda los datos.', 'Ejecuta la prueba de conexion.', 'Sincroniza la bandeja para confirmar el funcionamiento.'],
                'La cuenta puede recibir y enviar mensajes desde el panel.',
                ['No compartas contrasenas por canales inseguros.'],
                ['Usar puertos o tipos de seguridad que no corresponden al proveedor.']),

            self::article('editar-paginas-cms', 'Editar paginas del sitio', 'Modifica textos e imagenes del contenido publico.', 'cms', 10, 'menu.cms.pages.view', 'cms.pages',
                'Actualizar paginas publicas mediante campos administrables.',
                ['Identifica la pagina y prepara el contenido final.'],
                ['Abre CMS y entra a Paginas.', 'Selecciona la pagina que deseas editar.', 'Busca el grupo de contenido correspondiente.', 'Actualiza los campos necesarios.', 'Guarda los cambios.', 'Verifica visualmente la pagina publica.'],
                'La pagina publica refleja los cambios guardados.',
                ['No modifiques campos que no correspondan a la solicitud recibida.']),
            self::article('administrar-blog', 'Administrar blog y publicaciones', 'Crea y mantiene entradas de contenido.', 'cms', 20, 'menu.cms.posts.view', 'cms.posts',
                'Gestionar publicaciones del sitio desde el CMS.',
                ['Prepara titulo, texto, imagen y estado de publicacion.'],
                ['Abre Blog / Posts.', 'Crea una publicacion o edita una existente.', 'Completa titulo, slug, resumen y contenido.', 'Selecciona imagen y categorias cuando corresponda.', 'Define el estado y la fecha de publicacion.', 'Guarda y revisa el resultado publico.'],
                'La publicacion queda guardada con el estado definido.',
                ['No publiques borradores sin revision editorial.']),
            self::article('administrar-menus-publicos', 'Administrar menus publicos', 'Controla enlaces visibles en la navegacion del sitio.', 'cms', 30, 'menu.cms.menus.view', 'cms.menus',
                'Mantener ordenados los enlaces de navegacion publica.',
                ['Confirma la URL o pagina de destino de cada enlace.'],
                ['Abre Menus de navegacion.', 'Selecciona el menu correspondiente.', 'Agrega, edita, elimina u ordena elementos.', 'Revisa etiquetas y destinos.', 'Guarda los cambios.', 'Prueba la navegacion publica en computadora y celular.'],
                'El menu publico dirige a los destinos correctos.',
                ['Un enlace incorrecto puede dejar contenido inaccesible para visitantes.']),
            self::article('configurar-sitio', 'Configurar datos generales del sitio', 'Actualiza ajustes globales del portal publico.', 'cms', 40, 'menu.cms.settings.view', 'cms.settings',
                'Gestionar informacion reutilizada en varias paginas.',
                ['Identifica claramente el ajuste solicitado.'],
                ['Abre Configuracion dentro de CMS.', 'Busca el grupo adecuado.', 'Modifica solamente los valores necesarios.', 'Guarda los cambios.', 'Revisa las paginas publicas donde se reutiliza el dato.'],
                'El ajuste global se refleja correctamente donde corresponde.',
                ['Un cambio global puede afectar varias paginas al mismo tiempo.']),

            self::article('sincronizar-mls', 'Sincronizar MLS AMPI', 'Actualiza inventario proveniente de MLS y revisa el resultado.', 'integraciones', 10, 'menu.mls.view', 'mls',
                'Traer informacion actualizada desde MLS AMPI de forma controlada.',
                ['Realiza esta tarea solamente si tienes responsabilidad sobre integraciones.', 'Verifica que la configuracion y la conexion esten operativas.'],
                ['Abre MLS AMPI Sync.', 'Revisa estado de configuracion y ultima sincronizacion.', 'Prueba la conexion cuando exista alguna duda.', 'Selecciona el tipo de sincronizacion necesario.', 'Inicia el proceso y espera su finalizacion.', 'Revisa resumen, progreso y errores.', 'Corrige problemas antes de repetir una sincronizacion fallida.'],
                'El inventario MLS local queda actualizado y los errores quedan identificados.',
                ['No elimines todas las propiedades MLS ni liberes bloqueos sin confirmar el impacto.', 'Evita iniciar procesos repetidos mientras otro proceso sigue activo.'],
                ['Repetir la sincronizacion sin leer el detalle del error.']),
            self::article('exportar-easybroker', 'Exportar MLS hacia EasyBroker', 'Envia propiedades MLS seleccionadas a EasyBroker.', 'integraciones', 20, 'menu.easybroker.mls-export.view', 'easybroker.mls-export',
                'Publicar en EasyBroker propiedades MLS previamente verificadas.',
                ['Verifica la conexion de EasyBroker.', 'Confirma que la propiedad cumple las condiciones comerciales antes de enviarla.'],
                ['Abre MLS -> EasyBroker.', 'Filtra y localiza las propiedades que deseas enviar.', 'Revisa el detalle y selecciona solamente registros validos.', 'Ejecuta el envio.', 'Lee el resultado de cada propiedad.', 'Corrige cualquier error antes de volver a intentar.'],
                'Las propiedades aceptadas quedan registradas como enviadas a EasyBroker.',
                ['No ejecutes envios masivos sin revisar previamente los filtros.']),
            self::article('sincronizar-easybroker', 'Sincronizar EasyBroker', 'Consulta configuracion y ejecuta actualizaciones de inventario.', 'ajustes', 40, 'menu.easybroker.view', 'easybroker',
                'Mantener actualizados los datos conectados con EasyBroker.',
                ['Confirma que tienes autorizacion para ejecutar integraciones.'],
                ['Abre EasyBroker Sync.', 'Revisa estado, configuracion y ultima ejecucion.', 'Prueba la conexion si es necesario.', 'Inicia la sincronizacion adecuada.', 'Espera el resultado y revisa los mensajes del proceso.', 'Documenta o corrige cualquier error antes de repetir.'],
                'El sistema muestra el resultado verificable de la sincronizacion.',
                ['No cambies claves de integracion sin tener un respaldo valido.']),
            self::article('administrar-catalogos-mls', 'Administrar agentes y agencias MLS', 'Consulta catalogos sincronizados y campos internos.', 'integraciones', 40, 'menu.mls-agents.view', 'mls-agents',
                'Mantener consistentes los catalogos relacionados con MLS.',
                ['Comprende que parte de la informacion proviene de la integracion.'],
                ['Abre Agentes MLS o Agencias MLS.', 'Busca el registro que necesitas revisar.', 'Actualiza solamente campos permitidos para gestion manual.', 'Usa las acciones de sincronizacion cuando corresponda.', 'Comprueba relaciones con propiedades antes de finalizar.'],
                'Los catalogos reflejan datos sincronizados y ajustes internos validos.',
                ['No reemplaces manualmente informacion que debe provenir de MLS.']),

            self::article('administrar-usuarios', 'Administrar usuarios', 'Crea, actualiza y desactiva accesos internos.', 'administracion', 10, 'menu.users.view', 'users',
                'Controlar quienes pueden ingresar al panel.',
                ['Realiza esta tarea solamente con autorizacion administrativa.'],
                ['Abre Usuarios.', 'Busca un registro antes de crear uno nuevo.', 'Completa nombre, correo y datos requeridos.', 'Asigna roles adecuados a las responsabilidades reales.', 'Desactiva accesos que ya no deban utilizarse.', 'Verifica el resultado.'],
                'Cada integrante dispone unicamente del acceso necesario.',
                ['No compartas cuentas entre varias personas.', 'Desactiva accesos cuando alguien deje de colaborar.']),
            self::article('administrar-rbac', 'Administrar roles y permisos', 'Configura capacidades por perfil.', 'administracion', 20, 'menu.rbac.view', 'rbac',
                'Aplicar el principio de acceso minimo necesario.',
                ['Comprende el impacto de cada permiso antes de modificar un rol.'],
                ['Abre Roles y permisos.', 'Selecciona el rol que deseas revisar.', 'Consulta los permisos actuales.', 'Agrega o elimina solamente los permisos necesarios.', 'Guarda los cambios.', 'Valida con un usuario de prueba cuando el cambio sea importante.'],
                'Los usuarios del rol pueden realizar exactamente las acciones autorizadas.',
                ['Cambios amplios pueden exponer informacion o habilitar operaciones delicadas.']),
            self::article('administrar-colores', 'Administrar colores y temas', 'Ajusta apariencia interna y colores del sitio publico.', 'ajustes', 20, 'menu.frontend-colors.view', 'frontend-colors',
                'Modificar colores de forma controlada y verificable.',
                ['Define la paleta aprobada y las vistas afectadas.'],
                ['Abre Temas de color para revisar apariencia interna o Colores Frontend para el sitio publico.', 'Selecciona la configuracion correcta.', 'Modifica los valores necesarios.', 'Guarda y activa la configuracion cuando corresponda.', 'Revisa contraste, legibilidad y resultado visual en varias pantallas.'],
                'Los colores activos respetan la identidad visual sin afectar la lectura.',
                ['Mantener contraste suficiente es mas importante que reproducir un color de forma aislada.']),
            self::article('administrar-monedas', 'Administrar monedas', 'Mantiene el catalogo utilizado en precios.', 'ajustes', 10, 'menu.currencies.view', 'currencies',
                'Conservar disponibles las monedas correctas para el inventario.',
                ['Confirma el codigo y simbolo antes de guardar.'],
                ['Abre Monedas.', 'Busca la moneda existente.', 'Crea o actualiza el registro necesario.', 'Guarda los cambios.', 'Verifica como se muestran precios relacionados.'],
                'Los formularios y precios utilizan monedas consistentes.',
                ['No cambies el significado de una moneda existente para representar otra diferente.']),

            self::article('consultar-manual', 'Consultar el manual de uso', 'Busca temas y abre articulos relacionados con tu trabajo.', 'ayuda-interna', 10, null, 'manual',
                'Resolver dudas operativas desde el centro de conocimiento.',
                ['Abre Manual de uso desde Ayuda interna.'],
                ['Escribe una palabra en el buscador o recorre los capitulos.', 'Selecciona el articulo relacionado con tu tarea.', 'Sigue los pasos numerados.', 'Usa Ir al modulo cuando el articulo tenga un acceso directo.', 'Reproduce los videos relacionados cuando necesites apoyo visual.', 'Consulta la fecha de actualizacion para identificar contenido reciente.'],
                'Puedes resolver el proceso sin depender exclusivamente de un video.',
                [],
                ['Buscar solamente el nombre exacto del modulo; tambien puedes buscar una accion como publicar, convertir o agendar.']),
            self::article('administrar-manual', 'Administrar el manual de uso', 'Mantiene capitulos, articulos y videos relacionados.', 'ayuda-interna', 20, 'menu.manual-articles.view', 'manual-articles',
                'Mantener vigente la documentacion interna a medida que cambia el sistema.',
                ['Debes tener permiso para administrar el manual.', 'Prepara el texto revisado antes de reemplazar contenido existente.'],
                ['Abre Administrar manual.', 'Crea o edita capitulos para mantener una navegacion clara.', 'Selecciona un video tutorial opcional para el capitulo.', 'Crea o abre un articulo.', 'Completa titulo, resumen, contenido, orden y estado.', 'Asocia el permiso del modulo para ocultar instrucciones que el usuario no puede ejecutar.', 'Agrega la ruta relacionada.', 'Guarda y valida el articulo desde Manual de uso.'],
                'El equipo dispone de instrucciones actualizadas y adecuadas para cada perfil.',
                ['No elimines un capitulo que todavia contiene articulos.', 'Desactiva temporalmente un articulo antes de eliminarlo si necesitas conservar referencia.']),
            self::article('usar-videotutoriales', 'Usar y administrar videotutoriales', 'Consulta videos de apoyo y mantenlos relacionados con el manual.', 'ayuda-interna', 30, 'menu.tutorials.view', 'tutorials',
                'Usar videos como complemento visual de la documentacion escrita.',
                ['Abre Tutoriales desde Ayuda interna.'],
                ['Busca un video por titulo o descripcion.', 'Selecciona el tutorial y reproducelo desde el panel.', 'Consulta primero el articulo escrito cuando necesites una lista precisa de pasos.', 'Si administras contenido, entra a Administrar videos para crear, editar, ordenar o desactivar enlaces de YouTube.', 'Desde Administrar manual, asigna un video opcional a cada capitulo donde aporte valor.'],
                'Los videos complementan el manual sin reemplazar la informacion principal.',
                ['Mantener descripcion y titulo claros facilita encontrar el video correcto.']),
        ], ExpandedManualContent::articles());
    }

    private static function article(
        string $slug,
        string $title,
        string $summary,
        string $section,
        int $sortOrder,
        ?string $permission,
        ?string $route,
        string $objective,
        array $requirements,
        array $steps,
        string $result,
        array $warnings = [],
        array $errors = [],
        array $recommendations = []
    ): array {
        return compact(
            'slug',
            'title',
            'summary',
            'section',
            'sortOrder',
            'permission',
            'route',
            'objective',
            'requirements',
            'steps',
            'result',
            'warnings',
            'errors',
            'recommendations'
        ) + ['sort_order' => $sortOrder];
    }
}
