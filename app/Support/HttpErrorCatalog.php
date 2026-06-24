<?php

namespace App\Support;

final class HttpErrorCatalog
{
    /** @return array<int, array{title: string, headline: string, description: string, hint: string, category: string, tone: string, icon: string}> */
    public static function all(): array
    {
        return [
            400 => self::entry('Solicitud inválida', 'Datos mal formulados', 'El servidor no pudo interpretar la petición por sintaxis incorrecta o parámetros inválidos.', 'Revise el formulario y vuelva a intentar.', 'client', 'warning', 'clipboard-x'),
            401 => self::entry('No autenticado', 'Credenciales requeridas', 'Debe iniciar sesión para acceder a este recurso institucional.', 'Inicie sesión con su cuenta corporativa.', 'auth', 'warning', 'lock'),
            402 => self::entry('Pago requerido', 'Acceso con costo', 'Este recurso requiere un pago o licencia activa antes de continuar.', 'Contacte a administración si cree que es un error.', 'client', 'warning', 'credit-card'),
            403 => self::entry('Acceso denegado', 'Permisos insuficientes', 'Su rol no autoriza esta acción en el sistema farmacéutico.', 'Solicite permisos al administrador o regrese al panel.', 'auth', 'error', 'shield-off'),
            404 => self::entry('No encontrado', 'Recurso inexistente', 'La ruta, fármaco o registro solicitado no existe o fue movido.', 'Verifique la URL o use el menú de navegación.', 'navigation', 'info', 'search-x'),
            405 => self::entry('Método no permitido', 'Verbo HTTP incorrecto', 'La operación solicitada no está habilitada para esta ruta.', 'Use el enlace o botón previsto en la interfaz.', 'client', 'warning', 'ban'),
            406 => self::entry('No aceptable', 'Formato incompatible', 'El servidor no puede entregar el contenido en el formato que solicitó el navegador.', 'Actualice la página o cambie de navegador.', 'client', 'warning', 'file-x'),
            407 => self::entry('Autenticación proxy', 'Proxy requiere credenciales', 'Se necesita autenticación en el proxy de red antes de acceder al sistema.', 'Contacte al área de TI de su institución.', 'auth', 'warning', 'network'),
            408 => self::entry('Tiempo agotado', 'La petición expiró', 'El servidor cerró la conexión por demora excesiva en la respuesta.', 'Intente de nuevo; si persiste, revise su conexión.', 'network', 'warning', 'clock'),
            409 => self::entry('Conflicto', 'Estado inconsistente', 'La operación choca con el estado actual del recurso (duplicado o versión desactualizada).', 'Actualice la página y repita la acción.', 'client', 'warning', 'git-merge'),
            410 => self::entry('Ya no existe', 'Recurso eliminado', 'El contenido existió pero fue retirado de forma permanente.', 'Busque una alternativa en el menú principal.', 'navigation', 'info', 'trash'),
            411 => self::entry('Longitud requerida', 'Falta Content-Length', 'La petición no incluye el encabezado de longitud obligatorio.', 'Reintente desde la aplicación; no modifique la petición manualmente.', 'client', 'warning', 'ruler'),
            412 => self::entry('Precondición fallida', 'Condición no cumplida', 'Una regla previa (cabecera If-Match) no se cumplió para ejecutar la acción.', 'Recargue el registro y vuelva a intentar.', 'client', 'warning', 'alert-triangle'),
            413 => self::entry('Carga demasiado grande', 'Archivo excede límite', 'El archivo o payload supera el tamaño máximo permitido por el servidor.', 'Reduzca el tamaño del adjunto o divida la carga.', 'client', 'warning', 'upload'),
            414 => self::entry('URI demasiado larga', 'URL excesiva', 'La dirección solicitada supera el límite que el servidor puede procesar.', 'Use enlaces más cortos o navegue desde el menú.', 'client', 'warning', 'link'),
            415 => self::entry('Tipo de medio no soportado', 'Formato no admitido', 'El tipo de archivo enviado no es compatible con este endpoint.', 'Use PDF, Excel o los formatos indicados en pantalla.', 'client', 'warning', 'file-warning'),
            416 => self::entry('Rango no satisfacible', 'Rango inválido', 'El fragmento solicitado del archivo está fuera de los límites disponibles.', 'Descargue el archivo completo en lugar de un rango parcial.', 'client', 'warning', 'scissors'),
            417 => self::entry('Expectativa fallida', 'Cabecera Expect inválida', 'El servidor no puede cumplir las expectativas indicadas en la petición.', 'Reintente sin extensiones de cliente personalizadas.', 'client', 'warning', 'alert-circle'),
            418 => self::entry('Soy una tetera', 'RFC 2324', 'Este código es una broma del estándar HTTP — pero aquí indica una petición poco convencional.', 'Vuelva al flujo normal de la aplicación.', 'easter', 'accent', 'coffee'),
            421 => self::entry('Petición mal dirigida', 'Servidor incorrecto', 'La solicitud llegó a un nodo que no puede producir respuesta para este recurso.', 'Intente de nuevo; el balanceador redirigirá correctamente.', 'server', 'warning', 'shuffle'),
            422 => self::entry('Entidad no procesable', 'Validación fallida', 'Los datos enviados no cumplen las reglas de negocio o validación del sistema.', 'Corrija los campos marcados en rojo y reenvíe.', 'client', 'warning', 'form'),
            423 => self::entry('Recurso bloqueado', 'Registro en uso', 'El recurso está bloqueado por otra operación concurrente.', 'Espere unos segundos o cierre la otra sesión de edición.', 'client', 'warning', 'lock-keyhole'),
            424 => self::entry('Dependencia fallida', 'Operación encadenada', 'La acción dependía de otra que no se completó correctamente.', 'Revise el paso anterior antes de continuar.', 'client', 'warning', 'link-2-off'),
            425 => self::entry('Demasiado temprano', 'Reintento prematuro', 'El servidor rechaza procesar la petición en este momento del protocolo.', 'Espere e intente nuevamente en unos instantes.', 'client', 'warning', 'timer'),
            426 => self::entry('Actualización requerida', 'Protocolo obsoleto', 'Debe usar una versión más reciente del protocolo o cliente.', 'Actualice su navegador o la aplicación cliente.', 'client', 'warning', 'arrow-up-circle'),
            428 => self::entry('Precondición requerida', 'Falta cabecera obligatoria', 'Se requiere una condición previa (If-Match) para ejecutar la operación.', 'Recargue el recurso y repita la acción.', 'client', 'warning', 'list-checks'),
            429 => self::entry('Demasiadas peticiones', 'Límite de velocidad', 'Superó el número de intentos permitidos en un intervalo corto.', 'Espere un minuto antes de volver a intentar.', 'rate', 'error', 'gauge'),
            431 => self::entry('Cabeceras demasiado grandes', 'Headers excesivos', 'Los encabezados HTTP de la petición superan el límite del servidor.', 'Limpie cookies del sitio o use una ventana privada.', 'client', 'warning', 'headers'),
            451 => self::entry('No disponible por legal', 'Restricción legal', 'El acceso está bloqueado por razones legales o de cumplimiento normativo.', 'Contacte al área legal o administración.', 'legal', 'error', 'scale'),
            500 => self::entry('Error interno', 'Fallo del servidor', 'Ocurrió una condición inesperada al procesar su solicitud.', 'Intente más tarde o reporte el incidente a soporte.', 'server', 'error', 'server-crash'),
            501 => self::entry('No implementado', 'Función no disponible', 'El servidor no soporta la funcionalidad requerida para completar la petición.', 'Esta capacidad aún no está habilitada en el entorno.', 'server', 'error', 'puzzle'),
            502 => self::entry('Puerta de enlace inválida', 'Bad Gateway', 'Un servidor intermedio recibió una respuesta inválida del upstream.', 'El equipo técnico fue notificado; reintente en breve.', 'network', 'error', 'router'),
            503 => self::entry('Servicio no disponible', 'Mantenimiento o sobrecarga', 'El sistema está temporalmente fuera de servicio o en mantenimiento programado.', 'Espere unos minutos y actualice la página.', 'server', 'error', 'construction'),
            504 => self::entry('Tiempo de gateway agotado', 'Gateway Timeout', 'Un servidor intermedio no respondió a tiempo al reenviar la petición.', 'Verifique su conexión y vuelva a intentar.', 'network', 'error', 'hourglass'),
            505 => self::entry('Versión HTTP no soportada', 'Protocolo incompatible', 'El servidor no soporta la versión HTTP usada en la petición.', 'Actualice su navegador o proxy de red.', 'server', 'error', 'code'),
            507 => self::entry('Almacenamiento insuficiente', 'Sin espacio en servidor', 'El servidor no puede completar la operación por falta de espacio.', 'Contacte soporte; el almacenamiento requiere atención.', 'server', 'error', 'hard-drive'),
            508 => self::entry('Bucle detectado', 'Loop infinito', 'El servidor detectó un bucle al procesar la petición.', 'Reporte la URL a soporte técnico.', 'server', 'error', 'repeat'),
            511 => self::entry('Autenticación de red', 'Red requiere login', 'Debe autenticarse en la red antes de acceder a internet o al sistema.', 'Conéctese a la VPN o portal cautivo institucional.', 'auth', 'warning', 'wifi'),
            419 => self::entry('Sesión expirada', 'Token CSRF inválido', 'Su sesión expiró o el formulario quedó abierto demasiado tiempo.', 'Recargue la página e inicie sesión nuevamente si es necesario.', 'auth', 'warning', 'refresh'),
        ];
    }

    /** @return array{title: string, headline: string, description: string, hint: string, category: string, tone: string, icon: string} */
    public static function resolve(int $code): array
    {
        $catalog = self::all();

        if (isset($catalog[$code])) {
            return $catalog[$code];
        }

        if ($code >= 500) {
            return self::entry('Error de servidor', 'Código '.$code, 'El servidor encontró una condición que impide completar la solicitud.', 'Intente más tarde o contacte a soporte técnico.', 'server', 'error', 'server-crash');
        }

        if ($code >= 400) {
            return self::entry('Error del cliente', 'Código '.$code, 'La petición no pudo procesarse correctamente.', 'Verifique la URL y los datos enviados.', 'client', 'warning', 'alert-circle');
        }

        return self::entry('Respuesta HTTP', 'Código '.$code, 'Se produjo una respuesta HTTP inesperada.', 'Regrese al inicio o contacte soporte.', 'client', 'info', 'info');
    }

    /** @return list<int> */
    public static function codes(): array
    {
        $codes = array_keys(self::all());
        sort($codes);

        return $codes;
    }

    /** @return array{title: string, headline: string, description: string, hint: string, category: string, tone: string, icon: string} */
    private static function entry(
        string $title,
        string $headline,
        string $description,
        string $hint,
        string $category,
        string $tone,
        string $icon,
    ): array {
        return compact('title', 'headline', 'description', 'hint', 'category', 'tone', 'icon');
    }
}
