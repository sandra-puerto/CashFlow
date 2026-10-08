# CashFlow

**Sistema contable colombiano con motor API, basado en el Plan Único de Cuentas (PUC) y las Normas Internacionales de Información Financiera (NIIF).**

[![Arquitectura](https://img.shields.io/badge/Arquitectura-API--first-4f46e5?style=for-the-badge)](#2-arquitectura)
[![Laravel](https://img.shields.io/badge/Laravel-Core-ff2d20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![Filament](https://img.shields.io/badge/Filament-Panel%20Administrativo-f59e0b?style=for-the-badge)](https://filamentphp.com)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-Datos%20Relacionales-336791?style=for-the-badge&logo=postgresql&logoColor=white)](https://www.postgresql.org)
[![Contabilidad](https://img.shields.io/badge/Contabilidad-PUC%20%7C%20NIIF-0284c7?style=for-the-badge)](#5-modelo-contable)
[![Despliegue](https://img.shields.io/badge/Despliegue-Mono--empresa-10b981?style=for-the-badge)](#23-modelo-de-despliegue-mono-empresa)
[![Licencia](https://img.shields.io/badge/Licencia-Basada%20en%20MIT-yellow.svg?style=for-the-badge)](LICENSE)

CashFlow es un sistema contable construido con Laravel y Laravel Filament. Implementa el catálogo de cuentas del PUC, registra los movimientos por partida doble y expone su lógica mediante una API, de modo que el panel administrativo, las aplicaciones móviles y las integraciones externas operen sobre las mismas reglas y los mismos datos.

Fue diseñado e implementado por Sandra Gabriela Puerto Torres a partir de una necesidad práctica: disponer de un sistema contable abierto y adaptable, formulado con la terminología y los documentos propios de la contabilidad colombiana. Su documentación busca, además, que sirva de referencia a las organizaciones que deseen construir una solución propia.

---

## 1. Descripción general

Los sistemas contables desarrollados a medida suelen presentar tres dificultades recurrentes. La primera es que las reglas contables quedan incorporadas en las pantallas y los controladores, lo que impide reutilizarlas desde otra plataforma. La segunda es que muchos modelos provienen de software extranjero y no reflejan la estructura del PUC, la naturaleza de las cuentas ni los comprobantes de uso local. La tercera es la dificultad para rastrear un movimiento contable hasta el documento que lo respalda.

CashFlow responde a estas dificultades con una capa de dominio independiente de la interfaz y una API que actúa como contrato de acceso. Las reglas de partida doble, la naturaleza de las cuentas y la validación de comprobantes se concentran en el dominio; la trazabilidad se garantiza desde el documento soporte hasta el movimiento contable.

Capacidades principales:

* Registro contable por partida doble, con la naturaleza de cada cuenta como regla de comportamiento.
* Gestión de cuentas por cobrar y cuentas por pagar por tercero, con saldos y vencimientos.
* Gestión de documentos soporte y comprobantes de contabilidad asociados a cada movimiento.
* API para consumo desde múltiples plataformas.
* Despliegue mono-empresa, con una instalación por organización.

CashFlow es una herramienta de software. La interpretación de las normas contables y tributarias, y su aplicación a cada organización, corresponde a un contador público.

---

## 2. Arquitectura

### 2.1 Topología de la plataforma

```mermaid
graph TB
    subgraph Clients ["Clientes"]
        Web["Aplicación web"]
        Mobile["Aplicación móvil"]
        Ext["Integraciones de terceros"]
    end

    subgraph CashFlow ["CashFlow (Laravel)"]
        subgraph Access ["Capa de acceso"]
            API["API REST"]
            Filament["Panel Filament<br/>(administración)"]
        end

        subgraph Domain ["Capa de dominio"]
            Rules["Reglas contables<br/>(partida doble, naturaleza)"]
            Docs["Documentos y comprobantes"]
            Third["Cuentas por cobrar<br/>y por pagar"]
        end

        subgraph Data ["Capa de datos"]
            ORM["Eloquent ORM"]
            DB[("PostgreSQL")]
        end
    end

    Web --> API
    Mobile --> API
    Ext --> API

    API --> Rules
    Filament --> Rules
    Rules --> Docs
    Rules --> Third
    Docs --> ORM
    Third --> ORM
    ORM --> DB

    classDef client fill:#f59e0b,stroke:#b45309,stroke-width:2px,color:#000;
    classDef access fill:#1e293b,stroke:#38bdf8,stroke-width:2px,color:#fff;
    classDef domain fill:#1e293b,stroke:#10b981,stroke-width:2px,color:#fff;
    classDef data fill:#1e293b,stroke:#ec4899,stroke-width:2px,color:#fff;

    class Web,Mobile,Ext client;
    class API,Filament access;
    class Rules,Docs,Third domain;
    class ORM,DB data;
```

### 2.2 Principios de diseño

1. Toda funcionalidad debe poder utilizarse sin el panel administrativo.
2. Las reglas contables residen en la capa de dominio, y tanto la API como Filament acceden a ella directamente.
3. Un movimiento solo es válido cuando la suma de los débitos es igual a la suma de los créditos.
4. Los movimientos contabilizados no se sobrescriben; las correcciones se realizan mediante nuevos movimientos.

### 2.3 Modelo de despliegue: mono-empresa

Cada instalación de CashFlow atiende a una única organización. Cuando se requiere servir a varias, se despliega una instancia independiente para cada una.

```mermaid
graph LR
    subgraph OrgA ["Organización A"]
        AppA["Instancia CashFlow"] --> DBA[("Base de datos propia")]
    end

    subgraph OrgB ["Organización B"]
        AppB["Instancia CashFlow"] --> DBB[("Base de datos propia")]
    end

    classDef app fill:#1e293b,stroke:#10b981,stroke-width:2px,color:#fff;
    classDef db fill:#1e293b,stroke:#ec4899,stroke-width:2px,color:#fff;

    class AppA,AppB app;
    class DBA,DBB db;
```

Este modelo mantiene los datos de cada organización en una base de datos propia, sin filtros por empresa dentro de las consultas, y reduce la complejidad del sistema, puesto que el catálogo de cuentas, los terceros y los comprobantes pertenecen a una sola entidad contable. La multitenencia, entendida como varias empresas dentro de una misma instalación, queda fuera del alcance del diseño.

---

## 3. Módulos

| Módulo | Responsabilidad | Referencia contable |
| :--- | :--- | :--- |
| Registro contable | Movimientos por partida doble sobre el catálogo de cuentas | Débito (Debe), crédito (Haber) y naturaleza de la cuenta |
| Cuentas por cobrar | Valores pendientes de recibir de clientes, con saldo y vencimiento | Terceros y abonos |
| Cuentas por pagar | Obligaciones pendientes con proveedores y acreedores, con saldo y vencimiento | Terceros y pagos |
| Documentos y comprobantes | Evidencia que respalda cada movimiento contable | Documentos soporte y comprobantes de ingreso, egreso y general |

El alcance funcional se amplía de forma continua. El estado de cada módulo puede consultarse en los Issues y Projects del repositorio.

---

## 4. Flujo de una transacción

El siguiente diagrama describe el ciclo de un comprobante, desde su envío por parte del cliente hasta su registro contable.

```mermaid
sequenceDiagram
    autonumber
    actor Client as Cliente (web, móvil o tercero)
    participant API as API REST
    participant Domain as Capa de dominio
    participant DB as PostgreSQL

    Client->>API: Enviar comprobante con líneas y soportes
    API->>Domain: Solicitud validada
    Domain->>DB: Consultar cuentas y su naturaleza
    DB-->>Domain: Cuentas del catálogo (PUC)
    Domain->>Domain: Verificar que débitos y créditos cuadran

    alt Los valores cuadran
        Domain->>DB: Registrar comprobante y movimientos
        Domain->>DB: Asociar documentos soporte
        opt El comprobante afecta a un tercero
            Domain->>DB: Actualizar saldo por cobrar o por pagar
        end
        DB-->>Domain: Confirmación
        Domain-->>API: Comprobante contabilizado
        API-->>Client: Comprobante registrado
    else Los valores no cuadran
        Domain-->>API: Error de partida doble
        API-->>Client: Rechazo con detalle del descuadre
    end
```

---

## 5. Modelo contable

### 5.1 Reglas de integridad

El registro de cada comprobante es atómico: o se contabiliza completo, o no se contabiliza.

```
                  ┌─────────────────────────────────────────┐
                  │     REGLAS DE INTEGRIDAD CONTABLE       │
                  ├─────────────────────────────────────────┤
                  │ - Partida doble: suma Debe = suma Haber │
                  │ - Cada línea afecta una cuenta del PUC  │
                  │ - La naturaleza define el saldo normal  │
                  │ - Sin sobrescritura: las correcciones   │
                  │   se hacen con un nuevo movimiento      │
                  │ - Todo comprobante enlaza sus soportes  │
                  │ - Registro atómico                      │
                  └─────────────────────────────────────────┘
```

### 5.2 Naturaleza de las cuentas

Cada transacción afecta al menos dos cuentas, una por el Debe (débito) y otra por el Haber (crédito). La naturaleza de la cuenta indica si su saldo normal aumenta por el débito o por el crédito.

| Clase del PUC | Naturaleza | Aumenta con | Disminuye con |
| :--- | :---: | :---: | :---: |
| 1. Activo | Débito | Débito | Crédito |
| 2. Pasivo | Crédito | Crédito | Débito |
| 3. Patrimonio | Crédito | Crédito | Débito |
| 4. Ingresos | Crédito | Crédito | Débito |
| 5. Gastos | Débito | Débito | Crédito |
| 6. Costos de ventas | Débito | Débito | Crédito |
| 7. Costos de producción o de operación | Débito | Débito | Crédito |
| 8. Cuentas de orden deudoras | Débito | Débito | Crédito |
| 9. Cuentas de orden acreedoras | Crédito | Crédito | Débito |

Las cuentas de naturaleza contraria a la de su grupo, como la depreciación acumulada dentro del activo, se tratan según su propia naturaleza.

### 5.3 Documentos soporte y comprobantes de contabilidad

Los documentos soporte evidencian el hecho económico: facturas, recibos de caja, cuentas de cobro, notas débito y crédito, consignaciones y contratos, entre otros. Los comprobantes de contabilidad, que pueden ser de ingreso, de egreso o generales, sirven de fuente para registrar los movimientos. La relación entre ambos se conserva en los dos sentidos.

```mermaid
graph LR
    Soporte["Documento soporte<br/>(factura, recibo de caja,<br/>cuenta de cobro, nota débito<br/>o crédito, consignación)"]
    Comprobante["Comprobante de contabilidad<br/>(ingreso, egreso o general)"]
    Movimiento["Movimiento contable<br/>(Debe y Haber)"]

    Soporte -->|respalda| Comprobante
    Comprobante -->|origina| Movimiento
    Movimiento -.->|trazabilidad inversa| Soporte

    classDef support fill:#1e293b,stroke:#f59e0b,stroke-width:2px,color:#fff;
    classDef voucher fill:#1e293b,stroke:#38bdf8,stroke-width:2px,color:#fff;
    classDef entry fill:#1e293b,stroke:#10b981,stroke-width:2px,color:#fff;

    class Soporte support;
    class Comprobante voucher;
    class Movimiento entry;
```

### 5.4 Cuentas por cobrar y por pagar

Cada tercero mantiene un saldo que se actualiza con los abonos, en el caso de los clientes, y con los pagos, en el caso de proveedores y acreedores. Ambos movimientos se registran mediante su comprobante de contabilidad correspondiente.

```mermaid
graph LR
    subgraph Cobrar ["Cuentas por cobrar"]
        Sale["Documento a cargo<br/>del cliente"] --> CxC["Saldo por cobrar<br/>del tercero"]
        CxC --> Abono["Abono<br/>(comprobante de ingreso)"]
        Abono --> CxCClosed["Saldo cancelado"]
    end

    subgraph Pagar ["Cuentas por pagar"]
        Bill["Obligación con<br/>el proveedor"] --> CxP["Saldo por pagar<br/>al tercero"]
        CxP --> Pago["Pago<br/>(comprobante de egreso)"]
        Pago --> CxPClosed["Saldo cancelado"]
    end

    classDef open fill:#1e293b,stroke:#f59e0b,stroke-width:2px,color:#fff;
    classDef move fill:#1e293b,stroke:#38bdf8,stroke-width:2px,color:#fff;
    classDef closed fill:#1e293b,stroke:#10b981,stroke-width:2px,color:#fff;

    class Sale,Bill,CxC,CxP open;
    class Abono,Pago move;
    class CxCClosed,CxPClosed closed;
```

---

## 6. Stack tecnológico

| Capa | Tecnología |
| :--- | :--- |
| Lenguaje y framework | PHP y Laravel |
| Panel administrativo | Laravel Filament |
| Interfaz de integración | API REST |
| Acceso a datos | Eloquent ORM |
| Base de datos | PostgreSQL |

---

## 7. Puesta en marcha

```mermaid
graph LR
    Step1["1. Clonar<br/>(repositorio)"] --> Step2["2. Dependencias<br/>(composer install)"]
    Step2 --> Step3["3. Entorno<br/>(.env y clave)"]
    Step3 --> Step4["4. Base de datos<br/>(migrate --seed)"]
    Step4 --> Step5["5. Acceso<br/>(usuario Filament)"]

    style Step1 fill:#0f172a,stroke:#f59e0b,stroke-width:2px,color:#fff
    style Step2 fill:#0f172a,stroke:#38bdf8,stroke-width:2px,color:#fff
    style Step3 fill:#0f172a,stroke:#a78bfa,stroke-width:2px,color:#fff
    style Step4 fill:#0f172a,stroke:#ec4899,stroke-width:2px,color:#fff
    style Step5 fill:#0f172a,stroke:#10b981,stroke-width:2px,color:#fff
```

Requisitos: PHP, Composer y PostgreSQL.

```bash
git clone https://github.com/sandra-puerto/cashflow.git
cd cashflow

composer install
cp .env.example .env
php artisan key:generate

# Configurar las credenciales de PostgreSQL en .env y luego:
php artisan migrate --seed

# Crear un usuario para el panel administrativo:
php artisan make:filament-user

php artisan serve
```

---

## 8. Guía para construir un ERP contable propio

Quienes deseen desarrollar una solución similar pueden tomar este proyecto como referencia. Las decisiones que conviene revisar con mayor atención son las siguientes:

1. Definir el grupo NIIF y el tipo de organización, pues de ello depende el catálogo de cuentas y los informes requeridos.
2. Separar la lógica contable en una capa de dominio, independiente del framework y de la interfaz.
3. Establecer desde el inicio el mecanismo de corrección de movimientos ya contabilizados.
4. Adaptar el catálogo de cuentas a la organización, tomando el PUC como base de referencia.
5. Validar el resultado con un contador público antes de utilizar las cifras para fines oficiales.

---

## 9. Contribuciones y mejoras de aplicación general

CashFlow evoluciona a partir de las mejoras de quienes lo utilizan. Las correcciones, funcionalidades y ajustes de aplicación general, es decir, aquellos que benefician a cualquier organización usuaria, deben notificarse a la autora conforme a lo establecido en la [licencia](LICENSE), con el fin de integrarlos en la rama principal y ponerlos a disposición de todas las organizaciones.

Las adaptaciones propias de una organización, como su configuración, su catálogo de cuentas personalizado o sus integraciones internas, quedan fuera de esta obligación.

Para proponer una mejora puede abrirse una solicitud de integración (pull request) contra la rama principal, o describirla en un Issue cuando se requiera discutir el enfoque previamente. Cada propuesta se revisa antes de integrarse, y su aceptación depende de que sea coherente con los principios de diseño y con el alcance del proyecto. Las contribuciones sobre normativa y práctica contable colombiana son especialmente valiosas.

---

## 10. Seguridad

Las vulnerabilidades deben comunicarse de forma privada a [contacto@sandrapuerto.com](mailto:contacto@sandrapuerto.com), sin abrir Issues públicos, e incluir una descripción del problema y los pasos para reproducirlo.

---

## 11. Organizaciones que confían en CashFlow

Se listan aquí las organizaciones que utilizan CashFlow y han sido informadas a la autora conforme a la [licencia](LICENSE).

---

## 12. Autora

CashFlow fue diseñado e implementado por **Sandra Gabriela Puerto Torres**, desarrolladora backend (PHP y Laravel) con experiencia en infraestructura TI.

* Stack: PHP, Laravel, Filament, PostgreSQL, Docker, Linux y Nginx
* Sitio web: [sandrapuerto.com](https://sandrapuerto.com)
* GitHub: [github.com/sandra-puerto](https://github.com/sandra-puerto)
* Contacto: [contacto@sandrapuerto.com](mailto:contacto@sandrapuerto.com)

---

## 13. Licencia

CashFlow se distribuye bajo una licencia personalizada basada en MIT, que permite el uso comercial, la modificación y la redistribución sin costo. Las organizaciones que lo utilicen en producción deben informarlo a la autora y autorizar la mención de su nombre en [sandrapuerto.com](https://sandrapuerto.com), y deben notificar las mejoras de aplicación general que desarrollen para su integración en la rama principal. Las pruebas, la evaluación, el desarrollo y el uso educativo no requieren notificación.

Las notificaciones se dirigen a [contacto@sandrapuerto.com](mailto:contacto@sandrapuerto.com). El texto completo se encuentra en [LICENSE](LICENSE).
