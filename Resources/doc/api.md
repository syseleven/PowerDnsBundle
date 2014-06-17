## API Documentation ##

## Searching ##

### `GET` /api/search.json ###


#### Parameters ####

Name    | Type   | Description
------- |:-----: | ---
search  | string | wildcard search in name and the records entity
name    | string | search for a domain name (exact search)
account | string | search for an account name (exact match)
type    | string | search for records of the given type (MASTER, SLAVE, NATIVE, SUPERSLAVE)
master  | string | searches within the contents of the master field
offset  | integer | Offset to start
limit   | integer | Limit result

### `GET` /api/records.json ###

Searches within the domains and records entities

#### Parameters ####

Name    | Type   | Description
------- |:-----: | ---
id | integer | ID or list of ids of a record
search | string | Search string
name | string | searches in content of name field
content | string | searches in then content field
type | enum | record type
domain | integer | domain id or list of domain ids

### `GET` /api/history.json ###

Searches within the domains and records entities

#### Parameters ####

Name    | Type   | Description
------- |:-----: | ---
search  | string | Search string
domain_id | integer | ID of a domain
user | string | username
record_type | string | type of the record
from | date | Date to start iso date
to | date | Date to end
action | string | one of CREATE, UPDATE, DELETE


## Domains ##

### `GET` /api/domains.json ###

Returns a list of domains, the list can optionally be filtered by the following parameters

#### Parameters ####

Name    | Type   | Description
------- |:-----: | ---
search  | string | wildcard search in name and the records entity
name    | string | search for a domain name (exact search)
account | string | search for an account name (exact match)
type    | string | search for records of the given type (MASTER, SLAVE, NATIVE, SUPERSLAVE)
master  | string | searches within the contents of the master field

### `POST /api/domains.json ###

Creates a new domain with the given parameters. Note: when you create a domain a SOA record will be automatically created.

#### Parameters ####

Name    | Type   | Description
------- |:-----: | ---
name    | string | The name of the domain
type    | enum   | One of [MASTER, SLAVE, NATIVE, SUPERSLAVE]
master  | string | Name of the master server only applies to SLAVE or SUPERSLAVE
account | string | Name of the account to use to login to the master


### `GET` /api/domains/{id}.json ###

Retrieves the details of the given domain. {id} die must be a positive number.


### `PUT` /api/domains/{id}.json ###

Updates the domain given by {id} with the given parameters.

#### Parameters ####

Name    | Type   | Description
------- |:-----: | ---
name    | string | The name of the domain
type    | enum   | One of [MASTER, SLAVE, NATIVE, SUPERSLAVE]
master  | string | Name of the master server, applies only to SLAVE or SUPERSLAVE
account | string | Name of the account to use to login to the master


### `DELETE` /api/domains/{id}.{_format} ###

Deletes the domain given by {id} and all records of the domain.

### `GET` /api/domains/{id}/history.{_format} ###

Displays the history of the domain given by {id}.

## SOA Manipulation ##

### `GET` /api/domain/{domain}/soa.json ###

Retrieves the SOA record of the given domain.

### `POST` /api/domain/{domain}/soa.json ###

Creates a new SOA record for the given domain.

#### Parameters ####

Name    | Type   | Description
------- |:-----: | ---
primary | string | Name of the primary nameserver
hostmaster | string | email of the hostmaster
serial | string | serial number
refresh | integer | Refresh in seconds
expire | integer | expire in seconds
default_ttl | integer | default ttl in seconds

### `PUT` /api/domain/{domain}/soa.json ###

Updates the SOA record of the given domain.

#### Parameters ####

Name    | Type   | Description
------- |:-----: | ---
primary | string | Name of the primary nameserver
hostmaster | string | email of the hostmaster
serial | string | serial number
refresh | integer | Refresh in seconds
expire | integer | expire in seconds
default_ttl | integer | default ttl in seconds

### `DELETE` /api/domain/{domain}/soa.json ###

Deletes the SOA record of the given domain.

## Records ##

### `GET` /api/domain/{domain}/records.json ###

Retrieves a list of records for the given domain, the list can optionally filtered.

#### Parameters ####

Name    | Type   | Description
------- |:-----: | ---
id | integer | ID or list of ids of a record
search | string | Search string
name | string | searches in content of name field
content | string | searches in then content field
type | enum | record type
domain | integer | domain id or list of domain ids


### `POST` /api/domain/{domain}/records.json ###

Creates a new records within the domain given by {domain}.

#### Parameters ####

Name    | Type   | Description
------- |:-----: | ---
name | string | name
type | string | record type
prio | integer | priority
ttl | integer | ttl in seconds
content | string | content field
force | integer | if 1 validation is completely skipped
loose_check | integer | if 1 loose validation is enabled on record.


### `GET` /api/domain/{domain}/records/{record}.json ###

Retrieves the details of the record specified by {domain} and {record}

### `PUT` /api/domain/{domain}/records/{record}.json ###

Updates the record specified by {domain} and {record}. Note: you can only update records of domain with type [MASTER, NATIVE].

#### Parameters ####

Name    | Type   | Description
------- |:-----: | ---
name | string | name
type | string | record type
prio | integer | priority
ttl | integer | ttl in seconds
content | string | content field
force | integer | if 1 validation is completely skipped
loose_check | integer | if 1 loose validation is enabled on record.

### `DELETE` /api/domain/{domain}/records/{record}.json ###

Deletes the record specified by {domain} and {record}.

### `GET` /api/domain/{domain}/records/{record}/history.json ###

Shows the history of the record specified by {domain} and {record}.