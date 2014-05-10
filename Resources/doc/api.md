## /powerdns/api/domains ##

### `GET` /powerdns/api/domains.{_format} ###

_Returns a list of domains_

#### Requirements ####

**_format**

  - Description: Output Format

#### Parameters ####

search:

  * type: string
  * required: false

name:

  * type: string
  * required: false

account:

  * type: string
  * required: false

type:

  * type: string
  * required: true

master:

  * type: string
  * required: false

#### Response ####

name:

  * type: string

type:

  * type: string

id:

  * type: integer

master:

  * type: string

notified_serial:

  * type: integer

account:

  * type: string

user:

  * type: string


### `POST` /powerdns/api/domains.{_format} ###

_Creates a new domain_

#### Requirements ####

**_format**

  - Type: string
  - Description: Output Format

#### Parameters ####

name:

  * type: string
  * required: true

type:

  * type: string
  * required: true

master:

  * type: string
  * required: false

account:

  * type: string
  * required: false

#### Response ####

name:

  * type: string

type:

  * type: string

id:

  * type: integer

master:

  * type: string

notified_serial:

  * type: integer

account:

  * type: string

user:

  * type: string

records[]:

  * type: array of objects (Records)

records[][id]:

  * type: integer

records[][name]:

  * type: string

records[][type]:

  * type: string

records[][ttl]:

  * type: integer

records[][prio]:

  * type: integer

records[][change_date]:

  * type: integer

records[][managed]:

  * type: integer


### `GET` /powerdns/api/domains/{id}.{_format} ###

_Shows a single domain_

#### Requirements ####

**id**

  - Requirement: \d+
**_format**

  - Type: string
  - Description: Output Format

#### Response ####

name:

  * type: string

type:

  * type: string

id:

  * type: integer

master:

  * type: string

notified_serial:

  * type: integer

account:

  * type: string

user:

  * type: string

records[]:

  * type: array of objects (Records)

records[][id]:

  * type: integer

records[][name]:

  * type: string

records[][type]:

  * type: string

records[][ttl]:

  * type: integer

records[][prio]:

  * type: integer

records[][change_date]:

  * type: integer

records[][managed]:

  * type: integer


### `PUT` /powerdns/api/domains/{id}.{_format} ###

_Updates the given domain object_

#### Requirements ####

**id**

  - Requirement: \d+
**_format**

  - Type: string
  - Description: Output Format

#### Parameters ####

name:

  * type: string
  * required: true

type:

  * type: string
  * required: true

master:

  * type: string
  * required: false

account:

  * type: string
  * required: false

#### Response ####

name:

  * type: string

type:

  * type: string

id:

  * type: integer

master:

  * type: string

notified_serial:

  * type: integer

account:

  * type: string

user:

  * type: string

records[]:

  * type: array of objects (Records)

records[][id]:

  * type: integer

records[][name]:

  * type: string

records[][type]:

  * type: string

records[][ttl]:

  * type: integer

records[][prio]:

  * type: integer

records[][change_date]:

  * type: integer

records[][managed]:

  * type: integer


### `DELETE` /powerdns/api/domains/{id}.{_format} ###

_deletes the given domain_

#### Requirements ####

**id**

  - Requirement: \d+
**_format**

  - Type: string
  - Description: Output Format


### `GET` /powerdns/api/domains/{id}/history.{_format} ###

_displays the history of the domain records_

#### Requirements ####

**id**

  - Requirement: \d+
**_format**

  - Type: string
  - Description: Output Format

#### Parameters ####

search:

  * type: string
  * required: false

domain_id:

  * type: string
  * required: false

user:

  * type: string
  * required: false

record_type:

  * type: string
  * required: true

from:

  * type: datetime
  * required: false

to:

  * type: datetime
  * required: false

action:

  * type: string
  * required: false

#### Response ####

id:

  * type: integer

action:

  * type: string

record_id:

  * type: integer

domain_id:

  * type: integer

domain_name:

  * type: integer

record_type:

  * type: string

changes:

  * type: array

user:

  * type: string

created:

  * type: DateTime
