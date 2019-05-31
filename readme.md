# Cash Machine

## Instalación

### Requisitos

- php >= 7.3
- composer
- docker

### Levantamiento en un paso

`composer run install`

Este comando hará la instalción de las dependencias que usa el proyecto, posterior a eso ejecutara el `script` llamando `post-install-cmd`

### Levantamiento alternativo sin composer

En caso que no se haya podido ejecutar el script `post-install-cmd` se tendrá que hacer un levantamiento manual de la siguiente forma:

1. `docker-compose build`
2. `docker-compose up -d` El flag `-d` es opcional si no lo quieres levantar como demonio
3. `docker-compose exec cash-machine php artisan migrate --seed`
4. `docker-compose exec cash-machine php artisan passport:install --force`

### Detener ejecución

`composer run stop`

### Limpiar contenedores

`composer run prune-docker`

## Descripción de los endpoints expuestos

La colección de postman esta disponible en el archivo `cash_machine.postman_collection.json`

🛡 = Se require el header `Authorization: Bearer fancy.JWT`

### [POST] api/user/register
Permite el registro de un usuario

#### Petición:

```js
{
	"email": "random3@mail.com", // Valor requerido
	"name": "Random User", // Valor requerido
	"password": "123porMi" // Valor requerido
}
```

#### Respuesta
```js
{
    "token": "fancy.JWT"
}
```

### [POST] api/user/login
Permite el login del usuario vía password

#### Petición:

```js
{
	"email": "random3@mail.com", // Valor requerido
	"password": "123porMi" // Valor requerido
}
```

#### Respuesta
```js
{
    "token": "fancy.JWT"
}
```

### [GET] /api/me 🛡
Regresa la información del actual usuario


#### Respuesta:

```js
{
    "user": {
        "id": 5,
        "name": "Random User",
        "email": "random4@mail.com",
        "email_verified_at": null,
        "created_at": "2019-05-31 02:37:05",
        "updated_at": "2019-05-31 02:37:05"
    }
}
```

### [POST] /api/me/accounts 🛡
Crea una cuenta virtual al usuario

#### Petición:

```js
{
	"account_type": "credit", // Valor requerido, "credit" || "debit"
	"alias": "Fancy Bank", // Valor opcional
	"credit_line": 15000.45 // Valor requerido si el account_type es "credit"
}
```

#### Respuesta:

```js
{
    "id": 3,
    "account_type": "credit",
    "alias": "Fancy Bank",
    "created_date": "2019-05-31T02:46:50.000000Z"
}
```

### [GET] /api/me/accounts 🛡
Devuelve un arreglo con todas las cuentas asociadas al actual usuario y su actual saldo.

Si la cuenta es de tipo **"credit"** el campo **balance** es el monto que se debe, si es negativo representa un saldo positivo

#### Respuesta:
```js
[
    {
        "id": 1,
        "user_id": 1,
        "account_type": "debit",
        "alias": "Fancy Bank",
        "status": 1,
        "created_date": "2019-05-30 04:14:18",
        "balance": 145.3699999999999
    },
    {
        "id": 2,
        "user_id": 1,
        "account_type": "credit",
        "alias": "Fancy Bank",
        "status": 1,
        "created_date": "2019-05-30 05:40:42",
        "balance": 0
    },
    ...
]
```

### [PUT] /api/me/accounts/{accountId} 🛡
Permite la edición de la cuenta asociada al parámetro `accountId`.

El `{accountId}` debe estar asociada al usuario de la petición

#### Petición:

```js
{
	"account_type": "credit", // Valor requerido, "credit" || "debit"
	"alias": "Fancy Bank", // Valor opcional
	"credit_line": 15000.45 // Valor requerido si el account_type es "credit"
}
```

#### Respuesta:

```js
{
    "id": 3,
    "account_type": "credit",
    "alias": "Fancy Bank",
    "created_date": "2019-05-31T02:46:50.000000Z"
}
```

### [GET] /api/me/accounts/{accountId} 🛡
Regresa la información de la cuenta asociada al `accountId`

Si la cuenta es de tipo **"credit"** el campo **balance** es el monto que se debe, si es negativo representa un saldo positivo.

#### Respuesta:

```js
{
    "id": 2,
    "user_id": 1,
    "account_type": "credit",
    "alias": "Fancy Bank",
    "status": 1,
    "created_date": "2019-05-30 05:40:42",
    "balance": 0
}
```

### [POST] /api/account/withdraw/{accountId} 🛡
Permite realizar un retiro de la cuenta solicitada en `{accountId}`, se valida que la cuenta este asociada al actual usuario.

Si la cuenta es de débito debe de tener saldo disponible

Si la cuenta es de crédito se agrega un 10% de comisión, y se valida que el monto solicitado más la comisión este disponbile de su línea de crédito.

##### Nota:
Los cargos creados se crean con el estado de aprobados

#### Petición:

```js
{
	"amount": 5, // Valor requerido
	"description": "Prueba" // Valor opcional
}
```

#### Respuesta:

```js
{
    "operation_id": 7,
    "balance": 145.3699999999999,
    "operation_date": "2019-05-31"
}
```

### [POST] /api/account/deposit/{accountId} 🛡
Permite realizar un aboono a la cuenta virtual relacionado a `{accountId}`

No se requiere que la cuenta este asociada al usuario.

##### Nota:
El movimiento creado se crea con estado pendiente, por ende no se refleja al momento, se requiere una autorización posterior.

#### Petición:

```js
{
	"source": "spei", // Valor requerido, opciones: "spei" || "bank_deposit" || "store_deposit" || "credit_payment"
	"operation_date": "2019-05-29", // Valor requerido
	"liquidation_date": "2019-05-29", // Valor requerido
	"description": "Random description", // Valor opcional
	"amount": 160 // Valor requerido
}
```

#### Respuesta:

```js
{
    "success": true,
    "tracking_id": "c3ad9800-8347-11e9-a632-71992833edb6",
    "id": 19
}
```

### [POST] /api/cash-machine/pay

Este endpoint requiere el siguiente header:

`Authorization: Token ZAtBTRSkBsDtCNdyBrt7jDv684HNFm`

Crea un pago a una cuenta virtual de crédito.

El cargo creado se genera con el estado pendiente, falta aprovación posterior

#### Petición

```js
{
	"account_id": 2, // Valor requerido
	"amount": 10 // Valor requerido
}
```

#### Respuesta:

```js
{
    "status": "success",
    "tracking_id": 11
}
```

### [PUT] /api/cash-machine/charge/{state}

Este endpoint requiere el siguiente header:

`Authorization: Token ZAtBTRSkBsDtCNdyBrt7jDv684HNFm`

Modifica el estado de un cargo, los estado disponibles son los siguientes:

- `approved`: Aprueba el cargo y por ende ya se refleja en el saldo de la cuenta.
- `rejected`: Rechaza el cargo, no afecta el saldo de la cuenta.
- `cancel`: Cancela el crgo, no afecta el saldo de la cuenta

#### Petición

```js
{
	"id": 10 // Valor requerido
}
```

#### Respuesta:

```js
{
    "status": "success"
}
```

### [PUT] /api/cash-machine/deposit/{state}

Este endpoint requiere el siguiente header:

`Authorization: Token ZAtBTRSkBsDtCNdyBrt7jDv684HNFm`

Modifica el estado de un déposito, los estado disponibles son los siguientes:

- `approved`: Aprueba el déposito y por ende ya se refleja en el saldo de la cuenta.
- `rejected`: Rechaza el déposito, no afecta el saldo de la cuenta.

#### Petición

```js
{
	"id": 10 // Valor requerido
}
```

#### Respuesta:

```js
{
    "status": "success"
}
```
