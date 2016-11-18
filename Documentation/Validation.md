Validation
==========

Any posted form is validated. 
There are validations for request arguments and object properties.
If any validation fails, the request is redirected to the previous action.

## Default validation
The following section describes the default validation. You may implement your own. See [Custom Validation](#custom-validation).

### Objects
Object validation rules apply when a form is posted which creates or alters an object.
Since objects can be properties of other objects they are validated recursively.

#### Reservation

| property | type | validation rules |
| --- | ---| --- |
| `billingAddress` | object | valid billing address |
| `company` | object |  valid company |
| `contact` | object |  valid contact |
| `lesson` | object |  not empty |
| `participants` | object storage |  contains at least one |
| `privacyStatementAccepted` | boolean |  is true |

#### Contact

| property | type | validation rules |
| --- | ---| --- |
| `reservation` | object | valid reservation |
| `email`| string | valid email address |

#### Billing Address

| property | type | validation rules |
| --- | ---| --- |
| `email`| string | valid email address |

#### Participant

| property | type | validation rules |
| --- | ---| --- |
| `reservation` | object | valid reservation |
| `email`| string | valid email address |

### Request arguments


### Custom Validation
