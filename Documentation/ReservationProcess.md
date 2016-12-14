Reservation Process
===================
The reservation process was designed to be as robust and flexible as possible.  
For an overview of the default process and how to change it see [Routing](Routing.md).

### Outline
It starts with creating a new reservation and storing it into the database.  
Then the _edit_ form is presented. It allows to call other actions:
* new participant
* edit participant
* remove participant
* add billing address
* edit billing address
* add contact
* edit contact

Any of this actions do redirect to the edit action on success.

The initial status _draft_ will be kept until the user performs the _confirm_ action (which changes the status to _submitted_).

### Access control
A reference to the current reservation is kept in the front end user session. 
Before entering any action an access control check ensures that the user is allowed to edit the current reservation.  
If the session value does not match the controller clears the session throws an exception.  The handling of this exception can be configured by TypoScript (see [Error Handling](./ErrorHandling.md)).

The session entry is cleared after _confirm_ action or if the user cookie invalidates (This depends on the TYPO3 configuration and the browser settings).

**Please note**: The reservation process will not work when cookies are disabled!

### Notifications
After confirmation [notifications](./Notifications.md) can be send via email.

### Clean up
Incomplete reservations can be removed from system by a CleanUpCommand triggered via scheduler task or command line.

