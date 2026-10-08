# Política de seguridad

Este documento describe cómo comunicar vulnerabilidades en CashFlow y qué puede esperar quien las reporte.

## Versiones con soporte

Las correcciones de seguridad se aplican sobre la rama principal del repositorio. Las organizaciones que mantengan copias o bifurcaciones propias deben incorporar esas correcciones a su instalación.

## Cómo reportar una vulnerabilidad

Las vulnerabilidades deben comunicarse de forma privada a [contacto@sandrapuerto.com](mailto:contacto@sandrapuerto.com), con el asunto "Seguridad CashFlow". No deben publicarse en Issues, Discussions ni pull requests, ya que esos espacios son públicos y la información podría ser aprovechada antes de que exista una corrección.

El reporte debe incluir, en la medida de lo posible:

* Una descripción del problema y del componente afectado (API, panel de Filament, capa de dominio o base de datos).
* Los pasos necesarios para reproducirlo.
* El impacto estimado, por ejemplo acceso no autorizado, alteración de movimientos contables o exposición de información financiera.
* La versión o el identificador del commit en que se observó.
* Una propuesta de mitigación, si existe.

## Proceso de atención

1. La autora acusará recibo del reporte en un plazo de cinco (5) días hábiles.
2. Se evaluará la vulnerabilidad y se informará al reportante si fue confirmada, así como su severidad estimada.
3. Se desarrollará y verificará una corrección en privado.
4. Una vez disponible la corrección, se publicará en la rama principal junto con una descripción del problema, y se reconocerá al reportante si así lo desea.

Los plazos de corrección dependen de la severidad y de la complejidad del problema. La autora mantendrá informado al reportante sobre el avance.

## Divulgación coordinada

Se solicita a quien reporte que no divulgue públicamente los detalles de la vulnerabilidad hasta que exista una corrección disponible o hasta acordar una fecha de divulgación conjunta.

## Alcance

Se consideran dentro del alcance los problemas que afecten al código de este repositorio, entre ellos:

* Fallos de autenticación o autorización en la API y en el panel administrativo.
* Alteración, omisión o eliminación indebida de comprobantes y movimientos contables.
* Elusión de las reglas de partida doble o de las validaciones de la capa de dominio.
* Exposición de información financiera o de terceros.
* Inyección de código o de consultas, y vulnerabilidades en la gestión de documentos soporte cargados.

Se consideran fuera del alcance:

* Vulnerabilidades en dependencias de terceros (Laravel, Filament, PostgreSQL u otras) que deban corregirse en su propio proyecto. Pueden reportarse a CashFlow si existe una forma de mitigarlas desde este repositorio.
* Problemas derivados de la configuración o la infraestructura de una instalación particular.
* Ataques que requieran acceso físico al servidor o credenciales administrativas ya comprometidas.
* Ingeniería social, denegación de servicio por saturación de recursos y hallazgos de escáneres automáticos sin una demostración de impacto.

## Recomendaciones para quienes despliegan CashFlow

Dado que cada instalación es mono-empresa y la operación del sistema corresponde a cada organización, se recomienda:

* Establecer `APP_DEBUG=false` y `APP_ENV=production` en entornos productivos.
* Mantener el archivo `.env` fuera del control de versiones y con permisos restringidos.
* Servir la aplicación únicamente sobre HTTPS.
* Utilizar credenciales propias y de privilegios mínimos para la base de datos.
* Habilitar autenticación multifactor para los usuarios administrativos, cuando sea posible.
* Mantener actualizados PHP, Laravel, Filament y las demás dependencias.
* Realizar copias de seguridad periódicas de la base de datos y de los documentos soporte, y verificar su restauración.

## Reconocimientos

Quienes reporten vulnerabilidades de forma responsable serán reconocidos en las notas de la corrección, salvo que prefieran permanecer en el anonimato.
